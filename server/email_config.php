<?php

function env_or_default($key, $default = "") {
    $value = getenv($key);
    if ($value === false || $value === null || $value === "") return $default;
    return $value;
}

function detect_app_url() {
    $https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || ((string)($_SERVER["SERVER_PORT"] ?? "") === "443");
    $scheme = $https ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $serverDir = rtrim(dirname($script), "/");
    $base = dirname($serverDir);
    if ($base === "." || $base === "\\" || $base === "/") $base = "";
    $base = rtrim(str_replace("\\", "/", $base), "/");
    return $scheme . "://" . $host . $base;
}

// Base URL for verification links (no trailing slash).
// Production default points to the live domain unless APP_URL is explicitly set.
$APP_URL = rtrim(env_or_default("APP_URL", "http://kebshop.hu"), "/");

// Email sender settings.
$SMTP_USER_DEFAULT = env_or_default("SMTP_USER", "kebshop200@gmail.com");
$EMAIL_FROM = env_or_default("EMAIL_FROM", $SMTP_USER_DEFAULT !== "" ? $SMTP_USER_DEFAULT : "no-reply@" . ($_SERVER["HTTP_HOST"] ?? "localhost"));
$EMAIL_FROM_NAME = env_or_default("EMAIL_FROM_NAME", "KEB Hangszerbolt");

// SMTP settings.
$SMTP_HOST = env_or_default("SMTP_HOST", "smtp.gmail.com");
$SMTP_PORT = (int)env_or_default("SMTP_PORT", "587");
$SMTP_ENCRYPTION = strtolower(env_or_default("SMTP_ENCRYPTION", "tls")); // "tls", "ssl", "starttls", ""
$SMTP_USER = env_or_default("SMTP_USER", "kebshop200@gmail.com");
$SMTP_PASS = env_or_default("SMTP_PASS", "eviqkfpaubucppye");

// Allowed email domains. Empty array means allow all.
$domainsCsv = trim((string)env_or_default("ALLOWED_EMAIL_DOMAINS", ""));
$ALLOWED_EMAIL_DOMAINS = $domainsCsv === ""
    ? []
    : array_values(array_filter(array_map("trim", explode(",", $domainsCsv))));

// Verification token TTL in hours.
$EMAIL_TOKEN_TTL_HOURS = (int)env_or_default("EMAIL_TOKEN_TTL_HOURS", "24");
if ($EMAIL_TOKEN_TTL_HOURS <= 0) $EMAIL_TOKEN_TTL_HOURS = 24;

// Whether login requires email verification.
// Default is ON. Can be overridden via env: EMAIL_VERIFICATION_REQUIRED=0
$EMAIL_VERIFICATION_REQUIRED = filter_var(env_or_default("EMAIL_VERIFICATION_REQUIRED", "1"), FILTER_VALIDATE_BOOLEAN);

// Fallback logging.
$EMAIL_LOG_FALLBACK = filter_var(env_or_default("EMAIL_LOG_FALLBACK", "0"), FILTER_VALIDATE_BOOLEAN);
$EMAIL_LOG_PATH = env_or_default("EMAIL_LOG_PATH", __DIR__ . "/email_outbox.log");
