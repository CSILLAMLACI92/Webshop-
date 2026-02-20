<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "auth.php";
require_once "connect.php";

$action = $_GET["action"] ?? ($_POST["action"] ?? "users");

$writeActions = ["create_product", "delete_product", "delete_user", "delete_review"];
if (in_array($action, $writeActions, true)) {
    require_admin();
}

function respond($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_uploads_path($name) {
    return "/uploads/" . ltrim($name, "/");
}

function ensure_upload_dir($dir) {
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        return false;
    }
    return is_dir($dir);
}

function is_auto_comment($comment) {
    $c = mb_strtolower(trim((string)$comment), "UTF-8");
    if ($c === "") return true;
    return preg_match('/^jó\s+választás\s*:/u', $c) || preg_match('/^jo\s+valasztas\s*:/u', $c);
}

function handle_product_image_upload($file) {
    if (!isset($file) || !is_array($file) || ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [null, null, null, null];
    }

    $tmpPath = $file["tmp_name"] ?? "";
    if ($tmpPath === "" || !is_uploaded_file($tmpPath)) {
        return [null, null, null, null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmpPath);
    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "image/gif" => "gif"
    ];

    if (!isset($allowed[$mime])) {
        respond(["error" => "Unsupported image type"], 400);
    }

    $uploadsDir = realpath(__DIR__ . "/..") . DIRECTORY_SEPARATOR . "uploads";
    if (!ensure_upload_dir($uploadsDir)) {
        respond(["error" => "Upload directory unavailable"], 500);
    }

    $ext = $allowed[$mime];
    $safeBase = preg_replace('/[^a-z0-9_-]+/i', "_", pathinfo((string)($file["name"] ?? "product"), PATHINFO_FILENAME));
    $safeBase = trim((string)$safeBase, "_");
    if ($safeBase === "") {
        $safeBase = "product";
    }

    $newName = $safeBase . "_" . date("Ymd_His") . "_" . random_int(1000, 999999) . "." . $ext;
    $target = $uploadsDir . DIRECTORY_SEPARATOR . $newName;

    if (!move_uploaded_file($tmpPath, $target)) {
        respond(["error" => "Image upload failed"], 500);
    }

    $webPath = normalize_uploads_path($newName);
    $size = isset($file["size"]) ? (int)$file["size"] : null;
    return [$newName, $webPath, $mime, $size];
}

if ($action === "users") {
    $res = $conn->query("SELECT id, username, email, role, profile_pic, created_at FROM users WHERE role <> 'admin' ORDER BY id DESC");
    if (!$res) {
        respond(["error" => "User query failed"], 500);
    }

    $out = [];
    while ($u = $res->fetch_assoc()) {
        $out[] = [
            "id" => (int)$u["id"],
            "username" => $u["username"],
            "email" => $u["email"],
            "role" => $u["role"],
            "profile_pic" => $u["profile_pic"],
            "created_at" => $u["created_at"] ?? ""
        ];
    }

    respond($out);
}

if ($action === "user_reviews") {
    $userId = intval($_GET["user_id"] ?? 0);
    if ($userId <= 0) {
        respond(["error" => "Missing user id"], 400);
    }

    $stmt = $conn->prepare(
        "SELECT r.id, r.product_id, p.name AS product_name, r.rating, r.comment, r.created_at
         FROM reviews r
         LEFT JOIN products p ON p.id = r.product_id
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        if (is_auto_comment($r["comment"] ?? "")) {
            continue;
        }
        $rows[] = [
            "id" => (int)$r["id"],
            "product_id" => (int)($r["product_id"] ?? 0),
            "product_name" => $r["product_name"] ?? "",
            "rating" => (int)($r["rating"] ?? 0),
            "comment" => $r["comment"] ?? "",
            "created_at" => $r["created_at"] ?? ""
        ];
    }

    respond($rows);
}

if ($action === "user_orders") {
    $userId = intval($_GET["user_id"] ?? 0);
    if ($userId <= 0) {
        respond(["error" => "Missing user id"], 400);
    }

    $orders = [];
    $stmt = $conn->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 100");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $oid = (int)$row["id"];
            $orders[$oid] = [
                "id" => $oid,
                "total" => (float)($row["total"] ?? 0),
                "status" => $row["status"] ?? "",
                "created_at" => $row["created_at"] ?? "",
                "items" => []
            ];
        }
    } else {
        // If orders table is not available in a local environment, return empty list.
        respond([]);
    }

    if (count($orders) > 0) {
        $ids = implode(",", array_map("intval", array_keys($orders)));
        $itemsRes = $conn->query("SELECT order_id, product_name, unit_price, quantity FROM order_items WHERE order_id IN ($ids) ORDER BY id ASC");
        if ($itemsRes) {
            while ($it = $itemsRes->fetch_assoc()) {
                $oid = (int)$it["order_id"];
                if (!isset($orders[$oid])) continue;
                $orders[$oid]["items"][] = [
                    "product_name" => $it["product_name"] ?? "",
                    "unit_price" => (float)($it["unit_price"] ?? 0),
                    "quantity" => (int)($it["quantity"] ?? 0)
                ];
            }
        }
    }

    respond(array_values($orders));
}

if ($action === "categories") {
    $res = $conn->query("SELECT id, nev, slug FROM kategoria ORDER BY id ASC");
    if (!$res) {
        respond(["error" => "Category query failed"], 500);
    }

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            "id" => (int)$r["id"],
            "name" => $r["nev"],
            "slug" => $r["slug"]
        ];
    }

    respond($rows);
}

if ($action === "products") {
    $sql = "SELECT
                p.id,
                p.name,
                p.price,
                p.description,
                p.created_at,
                k.nev AS category_name,
                pic.path AS image_path
            FROM products p
            LEFT JOIN kategoria k ON k.id = p.category_id
            LEFT JOIN (
                SELECT owner_id, MIN(id) AS pic_id
                FROM pictures
                WHERE owner_type = 'product' AND is_active = 1
                GROUP BY owner_id
            ) px ON px.owner_id = p.id
            LEFT JOIN pictures pic ON pic.id = px.pic_id
            ORDER BY p.id DESC";

    $res = $conn->query($sql);
    if (!$res) {
        respond(["error" => "Product query failed"], 500);
    }

    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            "id" => (int)$r["id"],
            "name" => $r["name"],
            "price" => (float)$r["price"],
            "description" => $r["description"] ?? "",
            "created_at" => $r["created_at"] ?? "",
            "category_name" => $r["category_name"] ?? "",
            "image_path" => $r["image_path"] ?? null
        ];
    }

    respond($out);
}

if ($action === "create_product") {
    if (strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
        respond(["error" => "Method not allowed"], 405);
    }

    $name = trim((string)($_POST["name"] ?? ""));
    $description = trim((string)($_POST["description"] ?? ""));
    $priceRaw = str_replace(",", ".", trim((string)($_POST["price"] ?? "")));
    $categoryIdRaw = trim((string)($_POST["category_id"] ?? ""));
    $hang = trim((string)($_POST["hang"] ?? ""));

    if ($name === "") {
        respond(["error" => "Product name is required"], 400);
    }
    if ($description === "") {
        respond(["error" => "Description is required"], 400);
    }
    if ($priceRaw === "" || !is_numeric($priceRaw) || (float)$priceRaw < 0) {
        respond(["error" => "Invalid price"], 400);
    }

    $price = (float)$priceRaw;

    $categoryId = null;
    if ($categoryIdRaw !== "") {
        if (!ctype_digit($categoryIdRaw)) {
            respond(["error" => "Invalid category"], 400);
        }

        $categoryId = (int)$categoryIdRaw;
        $chk = $conn->prepare("SELECT id FROM kategoria WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $categoryId);
        $chk->execute();
        $exists = $chk->get_result()->fetch_assoc();

        if (!$exists) {
            respond(["error" => "Category not found"], 400);
        }
    }

    $image = $_FILES["image"] ?? null;
    [$filename, $path, $mimeType, $sizeBytes] = handle_product_image_upload($image);

    $sql = "INSERT INTO products (name, description, price, hang, category_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        respond(["error" => "Product prepare failed"], 500);
    }

    $hangParam = $hang !== "" ? $hang : null;
    $categoryParam = $categoryId;
    $stmt->bind_param("ssdsi", $name, $description, $price, $hangParam, $categoryParam);

    if (!$stmt->execute()) {
        respond(["error" => "Product insert failed", "detail" => $conn->error], 500);
    }

    $productId = (int)$conn->insert_id;

    if ($path !== null) {
        $picStmt = $conn->prepare(
            "INSERT INTO pictures (owner_type, owner_id, filename, path, alt_text, mime_type, size_bytes, is_active)
             VALUES ('product', ?, ?, ?, ?, ?, ?, 1)"
        );

        if (!$picStmt) {
            respond(["error" => "Picture prepare failed"], 500);
        }

        $altText = $name;
        $picStmt->bind_param("issssi", $productId, $filename, $path, $altText, $mimeType, $sizeBytes);

        if (!$picStmt->execute()) {
            respond(["error" => "Picture insert failed", "detail" => $conn->error], 500);
        }
    }

    respond([
        "success" => true,
        "message" => "Product created",
        "product_id" => $productId,
        "image_path" => $path
    ]);
}

if ($action === "delete_product") {
    $id = intval($_GET["id"] ?? 0);
    if ($id <= 0) {
        respond(["error" => "Missing product id"], 400);
    }

    $conn->begin_transaction();
    try {
        $stmts = [
            "DELETE FROM reviews WHERE product_id = ?",
            "DELETE FROM cart WHERE product_id = ?",
            "DELETE FROM product_variants WHERE product_id = ?",
            "DELETE FROM pictures WHERE owner_type='product' AND owner_id = ?",
            "DELETE FROM products WHERE id = ?"
        ];

        foreach ($stmts as $sql) {
            $st = $conn->prepare($sql);
            if (!$st) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $st->bind_param("i", $id);
            if (!$st->execute()) {
                throw new Exception("Execute failed: " . $st->error);
            }
        }

        $conn->commit();
        respond(["message" => "Product deleted"]);
    } catch (Throwable $e) {
        $conn->rollback();
        respond(["error" => "Delete product failed", "detail" => $e->getMessage()], 500);
    }
}

if ($action === "delete_user") {
    $id = intval($_GET["id"] ?? 0);
    if ($id <= 0) {
        respond(["error" => "Missing user id"], 400);
    }

    $stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    respond(["message" => "User and reviews deleted"]);
}

if ($action === "delete_review") {
    $id = intval($_GET["id"] ?? 0);
    if ($id <= 0) {
        respond(["error" => "Missing review id"], 400);
    }

    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    respond(["message" => "Review deleted"]);
}

respond(["error" => "Invalid action"], 400);
