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

function default_cover_url() {
    return "/uploads/kek-feher-zene-formak-hatterkep-5498.jpg";
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $username = trim($_GET["username"] ?? "");
    $user_id = (int)($_GET["user_id"] ?? 0);

    if ($username === "" && $user_id === 0) {
        $user = verify_jwt();
        if ($user) $user_id = (int)$user->id;
    }

    if ($username !== "") {
        $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            exit;
        }
        $userRow = $res->fetch_assoc();
        $user_id = (int)$userRow["id"];
    } else {
        $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            exit;
        }
        $userRow = $res->fetch_assoc();
    }

    $metaStmt = $conn->prepare("SELECT * FROM profile_meta WHERE user_id = ? LIMIT 1");
    $metaStmt->bind_param("i", $user_id);
    $metaStmt->execute();
    $metaRes = $metaStmt->get_result();
    $metaRow = $metaRes ? $metaRes->fetch_assoc() : null;

    $meta = [
        "bio" => "",
        "country" => "",
        "age" => "",
        "city" => "",
        "instrument" => "",
        "experience" => "",
        "studio" => "",
        "tags" => [],
        "custom_tags" => [],
        "cover_url" => default_cover_url(),
        "background" => "aurora"
    ];

    if ($metaRow) {
        $meta["bio"] = $metaRow["bio"] ?? "";
        $meta["country"] = $metaRow["country"] ?? "";
        $meta["age"] = $metaRow["age"] ?? "";
        $meta["city"] = $metaRow["city"] ?? "";
        $meta["instrument"] = $metaRow["instrument"] ?? "";
        $meta["experience"] = $metaRow["experience"] ?? "";
        $meta["studio"] = $metaRow["studio"] ?? "";
        $meta["tags"] = json_decode($metaRow["tags_json"] ?? "[]", true) ?: [];
        $meta["custom_tags"] = json_decode($metaRow["custom_tags_json"] ?? "[]", true) ?: [];
        $cover = trim((string)($metaRow["cover_url"] ?? ""));
        $meta["cover_url"] = $cover !== "" ? $cover : default_cover_url();
        $meta["background"] = $metaRow["background"] ?: "aurora";
    }

    echo json_encode([
        "user" => [
            "id" => (int)$userRow["id"],
            "username" => $userRow["username"],
            "profile_pic" => normalize_pic($userRow["profile_pic"] ?? "")
        ],
        "meta" => $meta
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
    $bio = trim((string)($data["bio"] ?? ""));
    $country = trim((string)($data["country"] ?? ""));
    $age = $data["age"] ?? "";
    $age = $age === "" ? null : (int)$age;
    if ($age !== null && ($age < 0 || $age > 120)) $age = null;
    $city = trim((string)($data["city"] ?? ""));
    $instrument = trim((string)($data["instrument"] ?? ""));
    $experience = trim((string)($data["experience"] ?? ""));
    $studio = trim((string)($data["studio"] ?? ""));
    $tags = $data["tags"] ?? [];
    $custom = $data["custom_tags"] ?? [];
    if (!is_array($tags)) $tags = [];
    if (!is_array($custom)) $custom = [];
    $cover = trim((string)($data["cover_url"] ?? ""));
    $background = trim((string)($data["background"] ?? "aurora"));

    $tagsJson = json_encode(array_values($tags), JSON_UNESCAPED_UNICODE);
    $customJson = json_encode(array_values($custom), JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare(
        "INSERT INTO profile_meta (user_id, bio, country, age, city, instrument, experience, studio, tags_json, custom_tags_json, cover_url, background) " .
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) " .
        "ON DUPLICATE KEY UPDATE bio=VALUES(bio), country=VALUES(country), age=VALUES(age), city=VALUES(city), instrument=VALUES(instrument), experience=VALUES(experience), studio=VALUES(studio), tags_json=VALUES(tags_json), custom_tags_json=VALUES(custom_tags_json), cover_url=VALUES(cover_url), background=VALUES(background)"
    );

    $ageParam = $age === null ? null : $age;
    $stmt->bind_param(
        "ississssssss",
        $user->id,
        $bio,
        $country,
        $ageParam,
        $city,
        $instrument,
        $experience,
        $studio,
        $tagsJson,
        $customJson,
        $cover,
        $background
    );

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
