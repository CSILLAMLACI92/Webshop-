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

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $username = trim($_GET["username"] ?? "");
    $user_id = (int)($_GET["user_id"] ?? 0);
    $limit = (int)($_GET["limit"] ?? 30);
    if ($limit <= 0 || $limit > 100) $limit = 30;

    if ($username === "" && $user_id === 0) {
        $user = verify_jwt();
        if ($user) $user_id = (int)$user->id;
    }

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
        $user_id = (int)$row["id"];
    }

    if ($user_id === 0) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user"]);
        exit;
    }

    $sql = "SELECT m.id, m.message, m.created_at, m.author_user_id, m.author_name, u.username AS author_username, u.profile_pic AS author_pic
            FROM profile_messages m
            LEFT JOIN users u ON u.id = m.author_user_id
            WHERE m.target_user_id = ?
            ORDER BY m.created_at DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $list[] = [
            "id" => (int)$row["id"],
            "message" => $row["message"],
            "created_at" => $row["created_at"],
            "author_id" => $row["author_user_id"] ? (int)$row["author_user_id"] : null,
            "author_name" => $row["author_username"] ?: ($row["author_name"] ?: "Vendeg"),
            "author_pic" => normalize_pic($row["author_pic"] ?? "")
        ];
    }
    echo json_encode($list);
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
    $message = trim((string)($data["message"] ?? ""));
    if ($message === "") {
        http_response_code(400);
        echo json_encode(["error" => "Missing message"]);
        exit;
    }

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

    $authorName = $user->username ?? "Felhasznalo";
    $stmt = $conn->prepare("INSERT INTO profile_messages (target_user_id, author_user_id, author_name, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $target_id, $user->id, $authorName, $message);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Insert failed"]);
        exit;
    }
    echo json_encode(["success" => true, "id" => $stmt->insert_id]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
