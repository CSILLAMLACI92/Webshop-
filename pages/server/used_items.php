<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "connect.php";
require_once "auth.php";
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

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    $user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
    $q = trim($_GET["q"] ?? "");
    $category = trim($_GET["category"] ?? "");
    $status = trim($_GET["status"] ?? "active");

    if ($id > 0) {
        $stmt = $conn->prepare("SELECT u.username, u.profile_pic, i.* FROM used_items i JOIN users u ON u.id = i.user_id WHERE i.id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if (!$row) {
            http_response_code(404);
            echo json_encode(["error" => "Not found"]);
            exit;
        }
        echo json_encode(["item" => $row]);
        exit;
    }

    $where = "1=1";
    $params = [];
    $types = "";

    if ($user_id > 0) {
        $where .= " AND i.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    if ($status !== "" && $status !== "all") {
        $where .= " AND i.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    if ($category !== "") {
        $where .= " AND i.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    if ($q !== "") {
        $where .= " AND (i.title LIKE ? OR i.description LIKE ?)";
        $like = "%" . $q . "%";
        $params[] = $like;
        $params[] = $like;
        $types .= "ss";
    }

    $sql = "SELECT u.username, u.profile_pic, i.* FROM used_items i JOIN users u ON u.id = i.user_id WHERE $where ORDER BY i.created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode($items);
    exit;
}

if ($method === "POST") {
    $data = read_json();
    $action = $data["action"] ?? "";

    if ($action === "create") {
        $user = require_user();
        $title = trim($data["title"] ?? "");
        $description = trim($data["description"] ?? "");
        $price = (float)($data["price"] ?? 0);
        $category = trim($data["category"] ?? "");
        $condition = trim($data["condition"] ?? "");
        $image_url = trim($data["image_url"] ?? "");

        if ($title === "" || $price <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing title or price"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO used_items (user_id, title, description, price, category, condition_label, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("issdsss", $user->id, $title, $description, $price, $category, $condition, $image_url);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Insert failed"]);
            exit;
        }
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        exit;
    }

    if ($action === "update") {
        $user = require_user();
        $id = (int)($data["id"] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT user_id FROM used_items WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if (!$row || (int)$row["user_id"] !== (int)$user->id) {
            http_response_code(403);
            echo json_encode(["error" => "Not allowed"]);
            exit;
        }

        $fields = [];
        $params = [];
        $types = "";

        foreach (["title","description","price","category","condition_label","image_url","status"] as $key) {
            if (!array_key_exists($key, $data)) continue;
            $fields[] = "$key = ?";
            $params[] = $data[$key];
            $types .= ($key === "price") ? "d" : "s";
        }

        if (!$fields) {
            echo json_encode(["success" => true, "updated" => 0]);
            exit;
        }

        $params[] = $id;
        $types .= "i";
        $sql = "UPDATE used_items SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode(["success" => true, "updated" => $stmt->affected_rows]);
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Unknown action"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
