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

function normalize_pic($rawPic) {
    $rawPic = trim((string)($rawPic ?? ""));
    $rawLower = strtolower(basename($rawPic));
    if (in_array($rawLower, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
        $rawPic = "Default.avatar.jpg";
    }
    if ($rawPic === "") return "/uploads/Default.avatar.jpg";
    if (preg_match("#^https?://#i", $rawPic)) return $rawPic;
    if (strpos($rawPic, "/uploads/") === 0) return $rawPic;
    if (strpos($rawPic, "../uploads/") === 0) return "/uploads/" . ltrim(substr($rawPic, strlen("../uploads/")), "/");
    return "/uploads/" . $rawPic;
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
    $username = trim($_GET["username"] ?? "");
    $target_id = (int)($_GET["user_id"] ?? 0);

    if ($username !== "") {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        $target_id = (int)$row["id"];
    }

    if ($target_id === 0) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user"]);
        exit;
    }

    $summaryStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM profile_ratings WHERE target_user_id = ?");
    $summaryStmt->bind_param("i", $target_id);
    $summaryStmt->execute();
    $summaryRow = $summaryStmt->get_result()->fetch_assoc();
    $avg = $summaryRow && $summaryRow["avg_rating"] ? round((float)$summaryRow["avg_rating"], 2) : 0;
    $count = $summaryRow ? (int)$summaryRow["cnt"] : 0;

    $stmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.username AS reviewer, u.profile_pic AS reviewer_pic FROM profile_ratings r JOIN users u ON u.id = r.reviewer_id WHERE r.target_user_id = ? ORDER BY r.created_at DESC");
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $list[] = [
            "rating" => (int)$row["rating"],
            "comment" => $row["comment"] ?? "",
            "created_at" => $row["created_at"],
            "reviewer" => $row["reviewer"] ?? "",
            "reviewer_pic" => normalize_pic($row["reviewer_pic"] ?? "")
        ];
    }

    echo json_encode([
        "summary" => ["avg" => $avg, "count" => $count],
        "ratings" => $list
    ]);
    exit;
}

if ($method === "POST") {
    $user = verify_jwt();
    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
    $data = read_json();
    $username = trim($data["username"] ?? "");
    $target_id = (int)($data["user_id"] ?? 0);
    $rating = (int)($data["rating"] ?? 0);
    $comment = trim((string)($data["comment"] ?? ""));

    if ($username !== "") {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            exit;
        }
        $row = $res->fetch_assoc();
        $target_id = (int)$row["id"];
    }

    if ($target_id === 0) {
        http_response_code(400);
        echo json_encode(["error" => "Missing target user"]);
        exit;
    }
    if ($target_id === (int)$user->id) {
        http_response_code(400);
        echo json_encode(["error" => "Cannot rate yourself"]);
        exit;
    }
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid rating"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO profile_ratings (target_user_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment), updated_at=NOW()");
    $stmt->bind_param("iiis", $target_id, $user->id, $rating, $comment);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Save failed"]);
        exit;
    }
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
