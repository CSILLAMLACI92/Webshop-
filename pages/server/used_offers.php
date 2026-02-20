<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "jwt_helper.php";
require_once "market_schema.php";

$schemaError = ensure_market_schema($conn);
if ($schemaError) {
    http_response_code(500);
    echo json_encode(["error" => "Schema error", "detail" => $schemaError]);
    exit;
}

function read_json() {
    $contentType = strtolower($_SERVER["CONTENT_TYPE"] ?? "");
    if (strpos($contentType, "application/json") !== false) {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

function get_current_user() {
    return verify_jwt();
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $user = get_current_user();
    $item_id = (int)($_GET["item_id"] ?? 0);
    $mine = trim($_GET["mine"] ?? "");

    if ($item_id <= 0 && $mine === "") {
        http_response_code(400);
        echo json_encode(["error" => "Missing query"]);
        exit;
    }

    if ($item_id > 0) {
        $stmt = $conn->prepare("SELECT user_id FROM used_items WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Item not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        $ownerId = (int)$row["user_id"];

        $params = [$item_id];
        $types = "i";
        $where = "o.item_id = ?";

        if (!$user) {
            echo json_encode([]);
            exit;
        }
        if ((int)$user->id !== $ownerId) {
            $where .= " AND o.buyer_id = ?";
            $params[] = (int)$user->id;
            $types .= "i";
        }

        $sql = "SELECT o.*, u.username AS buyer_name FROM used_offers o JOIN users u ON u.id = o.buyer_id WHERE $where ORDER BY o.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = [
                "id" => (int)$row["id"],
                "item_id" => (int)$row["item_id"],
                "buyer_id" => (int)$row["buyer_id"],
                "buyer_name" => $row["buyer_name"],
                "offer_price" => (float)$row["offer_price"],
                "status" => $row["status"],
                "created_at" => $row["created_at"]
            ];
        }
        echo json_encode($list);
        exit;
    }

    if ($mine !== "" && !$user) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    if ($mine === "buyer") {
        $stmt = $conn->prepare("SELECT o.*, i.title AS item_title FROM used_offers o JOIN used_items i ON i.id = o.item_id WHERE o.buyer_id = ? ORDER BY o.created_at DESC");
        $stmt->bind_param("i", $user->id);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = [
                "id" => (int)$row["id"],
                "item_id" => (int)$row["item_id"],
                "item_title" => $row["item_title"],
                "offer_price" => (float)$row["offer_price"],
                "status" => $row["status"],
                "created_at" => $row["created_at"]
            ];
        }
        echo json_encode($list);
        exit;
    }

    if ($mine === "seller") {
        $stmt = $conn->prepare("SELECT o.*, i.title AS item_title FROM used_offers o JOIN used_items i ON i.id = o.item_id WHERE i.user_id = ? ORDER BY o.created_at DESC");
        $stmt->bind_param("i", $user->id);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $list[] = [
                "id" => (int)$row["id"],
                "item_id" => (int)$row["item_id"],
                "item_title" => $row["item_title"],
                "offer_price" => (float)$row["offer_price"],
                "status" => $row["status"],
                "created_at" => $row["created_at"]
            ];
        }
        echo json_encode($list);
        exit;
    }
}

if ($method === "POST") {
    $user = verify_jwt();
    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
    $data = read_json();
    $action = $data["action"] ?? "";

    if ($action === "create") {
        $item_id = (int)($data["item_id"] ?? 0);
        $offer_price = (float)($data["offer_price"] ?? 0);
        if ($item_id <= 0 || $offer_price <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing item or price"]);
            exit;
        }
        $stmt = $conn->prepare("SELECT user_id, status FROM used_items WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Item not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        if ($row["status"] !== "active") {
            http_response_code(400);
            echo json_encode(["error" => "Item not active"]);
            exit;
        }
        if ((int)$row["user_id"] === (int)$user->id) {
            http_response_code(400);
            echo json_encode(["error" => "Cannot offer on own item"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO used_offers (item_id, buyer_id, offer_price) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $item_id, $user->id, $offer_price);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Insert failed"]);
            exit;
        }
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        exit;
    }

    if ($action === "accept") {
        $offer_id = (int)($data["offer_id"] ?? 0);
        if ($offer_id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing offer"]);
            exit;
        }
        $stmt = $conn->prepare("SELECT o.item_id, o.buyer_id, i.user_id AS owner_id FROM used_offers o JOIN used_items i ON i.id = o.item_id WHERE o.id = ? LIMIT 1");
        $stmt->bind_param("i", $offer_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Offer not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        if ((int)$row["owner_id"] !== (int)$user->id) {
            http_response_code(403);
            echo json_encode(["error" => "Not allowed"]);
            exit;
        }

        $conn->begin_transaction();
        try {
            $acc = $conn->prepare("UPDATE used_offers SET status = 'accepted' WHERE id = ?");
            $acc->bind_param("i", $offer_id);
            $acc->execute();

            $rej = $conn->prepare("UPDATE used_offers SET status = 'rejected' WHERE item_id = ? AND id != ?");
            $rej->bind_param("ii", $row["item_id"], $offer_id);
            $rej->execute();

            $upd = $conn->prepare("UPDATE used_items SET status = 'sold', buyer_id = ?, accepted_offer_id = ? WHERE id = ?");
            $upd->bind_param("iii", $row["buyer_id"], $offer_id, $row["item_id"]);
            $upd->execute();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(["error" => "Accept failed"]);
            exit;
        }

        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === "reject") {
        $offer_id = (int)($data["offer_id"] ?? 0);
        if ($offer_id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing offer"]);
            exit;
        }
        $stmt = $conn->prepare("SELECT o.item_id, i.user_id AS owner_id FROM used_offers o JOIN used_items i ON i.id = o.item_id WHERE o.id = ? LIMIT 1");
        $stmt->bind_param("i", $offer_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "Offer not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        if ((int)$row["owner_id"] !== (int)$user->id) {
            http_response_code(403);
            echo json_encode(["error" => "Not allowed"]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE used_offers SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $offer_id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Unknown action"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
