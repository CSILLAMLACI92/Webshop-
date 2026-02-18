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
    $q = trim($_GET["q"] ?? "");
    $genre = trim($_GET["genre"] ?? "");
    $city = trim($_GET["city"] ?? "");
    $looking = trim($_GET["looking_for"] ?? "");
    $owner_id = (int)($_GET["owner_id"] ?? 0);

    $where = "1=1";
    $params = [];
    $types = "";

    if ($owner_id > 0) {
        $where .= " AND b.owner_id = ?";
        $params[] = $owner_id;
        $types .= "i";
    }
    if ($genre !== "") {
        $where .= " AND b.genre LIKE ?";
        $params[] = "%" . $genre . "%";
        $types .= "s";
    }
    if ($city !== "") {
        $where .= " AND b.city LIKE ?";
        $params[] = "%" . $city . "%";
        $types .= "s";
    }
    if ($looking !== "") {
        $where .= " AND b.looking_for LIKE ?";
        $params[] = "%" . $looking . "%";
        $types .= "s";
    }
    if ($q !== "") {
        $where .= " AND (b.name LIKE ? OR b.description LIKE ?)";
        $params[] = "%" . $q . "%";
        $params[] = "%" . $q . "%";
        $types .= "ss";
    }

    $sql = "SELECT b.*, u.username, u.profile_pic FROM bands b JOIN users u ON u.id = b.owner_id WHERE $where ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $list[] = [
            "id" => (int)$row["id"],
            "owner_id" => (int)$row["owner_id"],
            "name" => $row["name"],
            "genre" => $row["genre"],
            "city" => $row["city"],
            "looking_for" => $row["looking_for"],
            "description" => $row["description"],
            "contact" => $row["contact"],
            "created_at" => $row["created_at"],
            "owner" => $row["username"],
            "owner_pic" => normalize_pic($row["profile_pic"] ?? "")
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
    $name = trim((string)($data["name"] ?? ""));
    $genre = trim((string)($data["genre"] ?? ""));
    $city = trim((string)($data["city"] ?? ""));
    $looking = trim((string)($data["looking_for"] ?? ""));
    $description = trim((string)($data["description"] ?? ""));
    $contact = trim((string)($data["contact"] ?? ""));

    if ($name === "") {
        http_response_code(400);
        echo json_encode(["error" => "Missing name"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO bands (owner_id, name, genre, city, looking_for, description, contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user->id, $name, $genre, $city, $looking, $description, $contact);
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
