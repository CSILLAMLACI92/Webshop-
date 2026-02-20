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
    $rawPic = str_replace("\\", "/", $rawPic);
    $rawLower = strtolower(basename($rawPic));
    if (in_array($rawLower, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
        $rawPic = "Default.avatar.jpg";
    }
    if ($rawPic === "") return "/uploads/Default.avatar.jpg";
    if (preg_match("#^https?://#i", $rawPic)) return $rawPic;
    if (preg_match("#^/?images/#i", $rawPic)) {
        return "/uploads/" . ltrim(preg_replace("#^/?images/#i", "", $rawPic), "/");
    }
    if (strpos($rawPic, "/uploads/") === 0) return $rawPic;
    if (strpos($rawPic, "../uploads/") === 0) return "/uploads/" . ltrim(substr($rawPic, strlen("../uploads/")), "/");
    if (strpos($rawPic, "uploads/") === 0) return "/" . ltrim($rawPic, "/");
    if (strpos($rawPic, "/") === 0) return $rawPic;
    return "/uploads/" . $rawPic;
}

function save_band_image($file, $ownerId) {
    if (!is_array($file) || !isset($file["error"])) return null;
    if ((int)$file["error"] === UPLOAD_ERR_NO_FILE) return null;
    if ((int)$file["error"] !== UPLOAD_ERR_OK) return false;

    $finfo = function_exists("finfo_open") ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $detectedType = $finfo ? (string)finfo_file($finfo, $file["tmp_name"]) : (string)($file["type"] ?? "");
    if ($finfo) finfo_close($finfo);

    $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
    if (!in_array($detectedType, $allowed, true)) return false;

    $upload_dir = realpath(__DIR__ . "/../uploads");
    if ($upload_dir === false) {
        $upload_dir = __DIR__ . "/../uploads";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
        $upload_dir = realpath($upload_dir);
    }
    if ($upload_dir === false) return false;
    $upload_dir = rtrim($upload_dir, "/\\") . DIRECTORY_SEPARATOR;
    if (!is_writable($upload_dir)) return false;

    $extMap = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "image/gif" => "gif"
    ];
    $ext = $extMap[$detectedType] ?? strtolower((string)pathinfo($file["name"] ?? "", PATHINFO_EXTENSION));
    if ($ext === "") $ext = "jpg";
    $new = "band_" . (int)$ownerId . "_" . time() . "_" . mt_rand(100, 999) . "." . $ext;
    $path = $upload_dir . $new;
    if (!move_uploaded_file($file["tmp_name"], $path)) return false;
    return $new;
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
            "image_url" => normalize_pic($row["image_url"] ?? ""),
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
    $action = strtolower(trim((string)($data["action"] ?? "create")));
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

    if ($action === "update") {
        $id = (int)($data["id"] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Missing id"]);
            exit;
        }

        $chk = $conn->prepare("SELECT owner_id, image_url FROM bands WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $id);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        if (!$row) {
            http_response_code(404);
            echo json_encode(["error" => "Band not found"]);
            exit;
        }
        if ((int)$row["owner_id"] !== (int)$user->id) {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }

        $image = $row["image_url"] ?? "";
        if (isset($_FILES["band_image"])) {
            $saved = save_band_image($_FILES["band_image"], $user->id);
            if ($saved === false) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid image"]);
                exit;
            }
            if (is_string($saved) && $saved !== "") $image = $saved;
        }

        $stmt = $conn->prepare("UPDATE bands SET name=?, genre=?, city=?, looking_for=?, description=?, contact=?, image_url=? WHERE id=? AND owner_id=?");
        $stmt->bind_param("sssssssii", $name, $genre, $city, $looking, $description, $contact, $image, $id, $user->id);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["error" => "Update failed"]);
            exit;
        }
        echo json_encode(["success" => true, "id" => $id]);
        exit;
    }

    $image = "";
    if (isset($_FILES["band_image"])) {
        $saved = save_band_image($_FILES["band_image"], $user->id);
        if ($saved === false) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid image"]);
            exit;
        }
        if (is_string($saved) && $saved !== "") $image = $saved;
    }

    $stmt = $conn->prepare("INSERT INTO bands (owner_id, name, genre, city, looking_for, description, contact, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user->id, $name, $genre, $city, $looking, $description, $contact, $image);
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
