<?php

function sanitize_profanity_text(string $text): string {
    $clean = trim($text);
    if ($clean === "") return $clean;

    $patterns = [
        '/\b(?:szar|kurva|geci|fasz|baszd|bazd)\b/ui',
        '/\b(?:fuck|shit|bitch|asshole|motherfucker)\b/ui'
    ];

    foreach ($patterns as $pattern) {
        $clean = preg_replace_callback($pattern, function ($m) {
            $w = (string)($m[0] ?? "");
            if ($w === "") return $w;
            return str_repeat("*", mb_strlen($w, "UTF-8"));
        }, $clean);
    }

    return $clean;
}

