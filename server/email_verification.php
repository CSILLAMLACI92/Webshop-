<?php

require_once __DIR__ . "/email_config.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$LAST_EMAIL_ERROR = "";

function get_last_email_error() {
    global $LAST_EMAIL_ERROR;
    return $LAST_EMAIL_ERROR;
}
function is_email_domain_allowed($email, $allowedDomains) {
    if (!is_array($allowedDomains) || count($allowedDomains) === 0) {
        return true;
    }
    $parts = explode("@", strtolower(trim($email)));
    if (count($parts) !== 2) return false;
    $domain = $parts[1];
    foreach ($allowedDomains as $allowed) {
        if ($domain === strtolower(trim($allowed))) {
            return true;
        }
    }
    return false;
}

function ensure_email_verification_schema($conn, &$error) {
    $error = null;
    $columns = [
        "email_verified" => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0",
        "email_verification_token" => "ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(128) NULL",
        "email_verification_expires" => "ALTER TABLE users ADD COLUMN email_verification_expires DATETIME NULL"
    ];

    foreach ($columns as $col => $alterSql) {
        $check = $conn->query("SHOW COLUMNS FROM users LIKE '" . $conn->real_escape_string($col) . "'");
        if ($check && $check->num_rows > 0) {
            continue;
        }
        if (!$conn->query($alterSql)) {
            $error = "DB schema update failed for: " . $col;
            return false;
        }
    }
    return true;
}

function create_email_verification($conn, $userId, $ttlHours, &$rawToken) {
    $rawToken = bin2hex(random_bytes(32));
    $hash = hash("sha256", $rawToken);
    $expires = (new DateTime("+$ttlHours hours"))->format("Y-m-d H:i:s");

    $stmt = $conn->prepare(
        "UPDATE users SET email_verified = 0, email_verification_token = ?, email_verification_expires = ? WHERE id = ?"
    );
    if (!$stmt) return false;
    $uid = (int)$userId;
    $stmt->bind_param("ssi", $hash, $expires, $uid);
    return $stmt->execute();
}

function get_verification_link($token) {
    global $APP_URL;
    return rtrim($APP_URL, "/") . "/server/verify_email.php?token=" . urlencode($token);
}

function send_native_mail_fallback($to, $subject, $htmlBody, $textBody) {
    global $EMAIL_FROM, $EMAIL_FROM_NAME;
    $fromNameSafe = str_replace(["\r", "\n"], "", (string)$EMAIL_FROM_NAME);
    $fromEmailSafe = str_replace(["\r", "\n"], "", (string)$EMAIL_FROM);
    $headers = [
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "From: " . $fromNameSafe . " <" . $fromEmailSafe . ">",
        "Reply-To: " . $fromEmailSafe,
        "X-Mailer: PHP/" . phpversion()
    ];

    $ok = @mail($to, "=?UTF-8?B?" . base64_encode($subject) . "?=", $htmlBody, implode("\r\n", $headers));
    if ($ok) return true;

    // Last fallback: plain text mail for stricter MTAs.
    $plainHeaders = [
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
        "From: " . $fromNameSafe . " <" . $fromEmailSafe . ">",
        "Reply-To: " . $fromEmailSafe,
        "X-Mailer: PHP/" . phpversion()
    ];
    return @mail($to, "=?UTF-8?B?" . base64_encode($subject) . "?=", $textBody, implode("\r\n", $plainHeaders));
}

function send_verification_email($email, $token) {
    global $EMAIL_FROM, $EMAIL_FROM_NAME, $EMAIL_LOG_FALLBACK, $EMAIL_LOG_PATH;
    global $SMTP_HOST, $SMTP_PORT, $SMTP_ENCRYPTION, $SMTP_USER, $SMTP_PASS;
    global $APP_URL;

    $link = get_verification_link($token);
    $subject = "Email hitelesítés - KEB Hangszerbolt";
    $logoUrl = rtrim($APP_URL, "/") . "/uploads/LOGO.png";
    $avatarUrl = rtrim($APP_URL, "/") . "/uploads/Default.avatar.jpg";
    $logoPath = __DIR__ . "/../uploads/LOGO.png";
    $avatarPath = __DIR__ . "/../uploads/Default.avatar.jpg";
    $logoCid = "logoimg";
    $avatarCid = "avatarimg";
    $logoSrc = (is_file($logoPath) ? "cid:$logoCid" : $logoUrl);
    $avatarSrc = (is_file($avatarPath) ? "cid:$avatarCid" : $avatarUrl);
    $htmlBody = "
<!doctype html>
<html lang=\"hu\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>Email hitelesítés</title>
</head>
<body style=\"margin:0;padding:0;background:#071a31;\">
  <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"background:#071a31;padding:24px 12px;\">
    <tr>
      <td align=\"center\">
        <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"max-width:620px;background:#0b2342;border:1px solid rgba(137,206,255,.28);border-radius:18px;overflow:hidden;\">
          <tr>
            <td style=\"padding:18px 20px;background:linear-gradient(135deg,#0e355f,#154a7d);border-bottom:1px solid rgba(137,206,255,.28);\" align=\"center\">
              <img src=\"$logoSrc\" alt=\"KEB Hangszerbolt\" style=\"max-width:180px;height:auto;display:block;margin:0 auto 8px;\">
              <img src=\"$avatarSrc\" alt=\"Profilkép\" style=\"width:64px;height:64px;border-radius:999px;border:2px solid #58bcff;object-fit:cover;display:block;margin:0 auto;\">
            </td>
          </tr>
          <tr>
            <td style=\"padding:22px 20px;color:#e8f5ff;font-family:Segoe UI,Arial,sans-serif;\">
              <h2 style=\"margin:0 0 10px;font-size:26px;line-height:1.25;color:#f2f9ff;\">Hitelesítsd az emailed</h2>
              <p style=\"margin:0 0 16px;font-size:16px;line-height:1.55;color:#b8daf7;\">
                Köszönjük a regisztrációt! Az aktiváláshoz kattints az alábbi gombra.
              </p>
              <p style=\"margin:0 0 18px;\">
                <a href=\"$link\" style=\"display:inline-block;padding:12px 18px;border-radius:10px;background:#2f86df;color:#ffffff;text-decoration:none;font-weight:700;\">
                  Email hitelesítése
                </a>
              </p>
              <p style=\"margin:0 0 14px;font-size:14px;line-height:1.5;color:#a8ccea;\">
                Ha nem te kérted, hagyd figyelmen kívül ezt az üzenetet.
              </p>
              <p style=\"margin:0;font-size:12px;color:#7ea9cc;\">KEB Hangszerbolt</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>";
    $textBody =
        "Szia!\n\n" .
        "Köszönjük a regisztrációt. Az aktiváláshoz hitelesítsd az emailed az alábbi linken:\n" .
        $link . "\n\n" .
        "Ha nem te kérted, hagyd figyelmen kívül ezt az emailt.\n" .
        "KEB Hangszerbolt\n";

    global $LAST_EMAIL_ERROR;
    $sent = false;
    $errorInfo = "";
    $smtpConfigured = trim((string)$SMTP_HOST) !== "" && trim((string)$SMTP_USER) !== "" && trim((string)$SMTP_PASS) !== "";

    if ($smtpConfigured) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $SMTP_USER;
            $mail->Password = $SMTP_PASS;
            $enc = strtolower(trim((string)$SMTP_ENCRYPTION));
            if ($enc === "tls" || $enc === "starttls") {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($enc === "ssl") {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = (int)$SMTP_PORT;
            $mail->SMTPOptions = [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                ]
            ];

            $mail->CharSet = "UTF-8";
            if (is_file($logoPath)) {
                $mail->addEmbeddedImage($logoPath, $logoCid);
            }
            if (is_file($avatarPath)) {
                $mail->addEmbeddedImage($avatarPath, $avatarCid);
            }
            $mail->setFrom($EMAIL_FROM, $EMAIL_FROM_NAME);
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;
            $mail->isHTML(true);

            $sent = $mail->send();
            if (!$sent) {
                $errorInfo = $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $sent = false;
            $errorInfo = $e->getMessage();
        }
    } else {
        $errorInfo = "SMTP not configured (SMTP_HOST/SMTP_USER/SMTP_PASS missing).";
    }

    if (!$sent) {
        $nativeOk = send_native_mail_fallback($email, $subject, $htmlBody, $textBody);
        if ($nativeOk) {
            $sent = true;
        } elseif ($errorInfo === "") {
            $errorInfo = "SMTP and native mail() both failed.";
        }
    }

    $LAST_EMAIL_ERROR = $errorInfo;

    if ($sent) return true;

    if ($EMAIL_LOG_PATH) {
        $line = date("Y-m-d H:i:s") . " | " . $email . " | " . $link;
        if ($errorInfo) $line .= " | SMTP error: " . $errorInfo;
        $line .= PHP_EOL;
        @file_put_contents($EMAIL_LOG_PATH, $line, FILE_APPEND);
    }

    if ($EMAIL_LOG_FALLBACK) {
        return true;
    }

    return false;
}

function start_verification_flow($conn, $userId, $email, &$error) {
    global $EMAIL_TOKEN_TTL_HOURS;

    if (!ensure_email_verification_schema($conn, $error)) {
        return false;
    }

    $token = null;
    if (!create_email_verification($conn, $userId, $EMAIL_TOKEN_TTL_HOURS, $token)) {
        $error = "Verification token update failed";
        return false;
    }

    if (!send_verification_email($email, $token)) {
        $error = "Email send failed";
        return false;
    }

    return true;
}












