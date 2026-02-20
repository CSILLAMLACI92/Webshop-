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
    $p = trim((string)($rawPic ?? ""));
    $p = str_replace("\\", "/", $p);
    $pl = strtolower(basename($p));
    if (in_array($pl, ["default.png", "default_avatar.png", "default.avatar.jpg"], true)) {
        $p = "Default.avatar.jpg";
    }
    if ($p === "") return "/uploads/Default.avatar.jpg";
    if (preg_match("#^https?://#i", $p)) return $p;
    if (preg_match("#^/?images/#i", $p)) {
        $p = "/uploads/" . ltrim(preg_replace("#^/?images/#i", "", $p), "/");
    }
    if (strpos($p, "/uploads/") === 0) return $p;
    if (strpos($p, "../uploads/") === 0) return "/uploads/" . ltrim(substr($p, strlen("../uploads/")), "/");
    if (strpos($p, "uploads/") === 0) return "/" . $p;
    if (strpos($p, "/") === 0) {
        if (!preg_match('/\.(jpg|jpeg|png|webp|gif|jfif)$/i', $p)) return "/uploads/Default.avatar.jpg";
        return $p;
    }
    if (!preg_match('/\.(jpg|jpeg|png|webp|gif|jfif)$/i', $p)) return "/uploads/Default.avatar.jpg";
    return "/uploads/" . ltrim($p, "/");
}

function normalize_upload_path($raw) {
    $p = trim((string)($raw ?? ""));
    if ($p === "") return null;
    if (preg_match("#^https?://#i", $p)) return $p;
    if (strpos($p, "/uploads/") === 0) return $p;
    if (strpos($p, "../uploads/") === 0) return "/uploads/" . ltrim(substr($p, strlen("../uploads/")), "/");
    if (strpos($p, "uploads/") === 0) return "/" . $p;
    if (strpos($p, "/") === 0) return $p;
    return "/uploads/" . ltrim($p, "/");
}

function read_json() {
    $ct = strtolower($_SERVER["CONTENT_TYPE"] ?? "");
    if (strpos($ct, "application/json") !== false) {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

function str_len($text) {
    if (function_exists("mb_strlen")) return mb_strlen((string)$text, "UTF-8");
    return strlen((string)$text);
}

function save_chat_image($file, $userId) {
    if (!is_array($file) || !isset($file["error"])) return [null, null];
    $err = (int)$file["error"];
    if ($err === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($err !== UPLOAD_ERR_OK) return [false, "Feltöltési hiba"];
    if (empty($file["tmp_name"]) || !is_uploaded_file($file["tmp_name"])) return [false, "Érvénytelen feltöltési forrás"];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file["tmp_name"]) : "";
    if ($finfo) finfo_close($finfo);
    $allowed = ["image/jpeg", "image/png", "image/webp", "image/gif"];
    if (!in_array($mime, $allowed, true)) return [false, "Csak képfájl küldhető"];

    $ext = strtolower((string)pathinfo((string)($file["name"] ?? ""), PATHINFO_EXTENSION));
    if ($ext === "") {
        if ($mime === "image/png") $ext = "png";
        elseif ($mime === "image/webp") $ext = "webp";
        elseif ($mime === "image/gif") $ext = "gif";
        else $ext = "jpg";
    }

    $uploadDir = realpath(__DIR__ . "/../uploads");
    if ($uploadDir === false) {
        $uploadDir = __DIR__ . "/../uploads";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $uploadDir = realpath($uploadDir);
    }
    if ($uploadDir === false) return [false, "Uploads mappa nem elérhető"];
    $uploadDir = rtrim($uploadDir, "/\\") . DIRECTORY_SEPARATOR;
    if (!is_writable($uploadDir)) return [false, "Uploads mappa nem írható"];

    $name = "chat_" . (int)$userId . "_" . time() . "_" . bin2hex(random_bytes(3)) . "." . $ext;
    $target = $uploadDir . $name;
    if (!move_uploaded_file($file["tmp_name"], $target)) return [false, "Kép mentése sikertelen"];

    return ["/uploads/" . $name, $mime];
}

$user = verify_jwt();
if (!$user) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $uid = (int)$user->id;
    $threads = (int)($_GET["threads"] ?? 0);
    $withUserId = (int)($_GET["with_user_id"] ?? 0);

    if ($threads === 1) {
        $sql = "SELECT m.id, m.sender_id, m.recipient_id, m.message, m.media_url, m.item_id, m.is_read, m.created_at,
                       u.id AS partner_id, u.username AS partner_username, u.profile_pic AS partner_pic
                FROM direct_messages m
                JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.recipient_id ELSE m.sender_id END
                WHERE m.sender_id = ? OR m.recipient_id = ?
                ORDER BY m.created_at DESC, m.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $uid, $uid, $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $map = [];
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row["partner_id"];
            if (!isset($map[$pid])) {
                $map[$pid] = [
                    "partner_id" => $pid,
                    "partner_username" => $row["partner_username"],
                    "partner_pic" => normalize_pic($row["partner_pic"] ?? ""),
                    "last_message" => trim((string)($row["message"] ?? "")) !== "" ? $row["message"] : ((string)($row["media_url"] ?? "") !== "" ? "[Kép]" : ""),
                    "last_created_at" => $row["created_at"],
                    "unread" => 0
                ];
            }
            if ((int)$row["recipient_id"] === $uid && (int)$row["is_read"] === 0) {
                $map[$pid]["unread"]++;
            }
        }
        echo json_encode(array_values($map), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($withUserId > 0) {
        $peerStmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE id = ? LIMIT 1");
        $peerStmt->bind_param("i", $withUserId);
        $peerStmt->execute();
        $peerRes = $peerStmt->get_result();
        $peer = $peerRes ? $peerRes->fetch_assoc() : null;
        if (!$peer) {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
            exit;
        }

        $sql = "SELECT m.id, m.sender_id, m.recipient_id, m.item_id, m.reply_to_message_id, m.message, m.media_url, m.media_type, m.created_at,
                       rm.message AS reply_message, rm.media_url AS reply_media_url
                FROM direct_messages m
                LEFT JOIN direct_messages rm ON rm.id = m.reply_to_message_id
                WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)
                ORDER BY m.id ASC
                LIMIT 300";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $uid, $withUserId, $withUserId, $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $messages = [];
        while ($row = $res->fetch_assoc()) {
            $messages[] = [
                "id" => (int)$row["id"],
                "sender_id" => (int)$row["sender_id"],
                "recipient_id" => (int)$row["recipient_id"],
                "item_id" => $row["item_id"] !== null ? (int)$row["item_id"] : null,
                "reply_to_message_id" => $row["reply_to_message_id"] !== null ? (int)$row["reply_to_message_id"] : null,
                "reply_message" => $row["reply_message"] ?? null,
                "reply_media_url" => normalize_upload_path($row["reply_media_url"] ?? null),
                "message" => $row["message"],
                "media_url" => normalize_upload_path($row["media_url"] ?? null),
                "media_type" => $row["media_type"] ?? null,
                "can_delete" => ((int)$row["sender_id"] === $uid),
                "created_at" => $row["created_at"]
            ];
        }

        $mark = $conn->prepare("UPDATE direct_messages SET is_read = 1 WHERE sender_id = ? AND recipient_id = ? AND is_read = 0");
        $mark->bind_param("ii", $withUserId, $uid);
        $mark->execute();

        echo json_encode([
            "peer" => [
                "id" => (int)$peer["id"],
                "username" => $peer["username"],
                "profile_pic" => normalize_pic($peer["profile_pic"] ?? "")
            ],
            "messages" => $messages
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Missing query"]);
    exit;
}

if ($method === "POST") {
    $isMultipart = strpos(strtolower((string)($_SERVER["CONTENT_TYPE"] ?? "")), "multipart/form-data") !== false;
    $data = $isMultipart ? $_POST : read_json();
    $recipientId = (int)($data["recipient_id"] ?? 0);
    $itemId = isset($data["item_id"]) ? (int)$data["item_id"] : null;
    $replyToMessageId = isset($data["reply_to_message_id"]) ? (int)$data["reply_to_message_id"] : null;
    $message = trim((string)($data["message"] ?? ""));
    $mediaUrl = null;
    $mediaType = null;

    if ($isMultipart && isset($_FILES["image"])) {
        [$saved, $savedType] = save_chat_image($_FILES["image"], (int)$user->id);
        if ($saved === false) {
            http_response_code(400);
            echo json_encode(["error" => $savedType ?: "Kép feltöltés sikertelen"]);
            exit;
        }
        $mediaUrl = $saved;
        $mediaType = $savedType;
    }

    if ($message !== "" && str_len($message) > 150) {
        http_response_code(400);
        echo json_encode(["error" => "Az üzenet legfeljebb 150 karakter lehet"]);
        exit;
    }

    if ($recipientId <= 0 || ($message === "" && !$mediaUrl)) {
        http_response_code(400);
        echo json_encode(["error" => "Missing recipient, message or image"]);
        exit;
    }
    if ($recipientId === (int)$user->id) {
        http_response_code(400);
        echo json_encode(["error" => "Cannot message yourself"]);
        exit;
    }
    if ($replyToMessageId !== null && $replyToMessageId <= 0) {
        $replyToMessageId = null;
    }

    $chk = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $chk->bind_param("i", $recipientId);
    $chk->execute();
    $exists = $chk->get_result()->fetch_assoc();
    if (!$exists) {
        http_response_code(404);
        echo json_encode(["error" => "Recipient not found"]);
        exit;
    }

    $sid = (int)$user->id;
    if ($replyToMessageId !== null) {
        $replyCheck = $conn->prepare("SELECT id FROM direct_messages
            WHERE id = ?
              AND (
                (sender_id = ? AND recipient_id = ?)
                OR
                (sender_id = ? AND recipient_id = ?)
              )
            LIMIT 1");
        $replyCheck->bind_param("iiiii", $replyToMessageId, $sid, $recipientId, $recipientId, $sid);
        $replyCheck->execute();
        $replyExists = $replyCheck->get_result()->fetch_assoc();
        if (!$replyExists) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid reply target"]);
            exit;
        }
    }

    if ($itemId !== null && $itemId <= 0) $itemId = null;
    $stmt = $conn->prepare("INSERT INTO direct_messages (sender_id, recipient_id, item_id, reply_to_message_id, message, media_url, media_type)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiisss", $sid, $recipientId, $itemId, $replyToMessageId, $message, $mediaUrl, $mediaType);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Insert failed"]);
        exit;
    }
    echo json_encode([
        "success" => true,
        "id" => $stmt->insert_id,
        "media_url" => normalize_upload_path($mediaUrl),
        "media_type" => $mediaType
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === "DELETE") {
    $data = read_json();
    $messageId = (int)($data["message_id"] ?? $data["id"] ?? 0);
    if ($messageId <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Missing message id"]);
        exit;
    }

    $uid = (int)$user->id;
    $stmt = $conn->prepare("DELETE FROM direct_messages WHERE id = ? AND sender_id = ? LIMIT 1");
    $stmt->bind_param("ii", $messageId, $uid);
    $stmt->execute();
    if ($stmt->affected_rows <= 0) {
        http_response_code(403);
        echo json_encode(["error" => "Not allowed or not found"]);
        exit;
    }
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
