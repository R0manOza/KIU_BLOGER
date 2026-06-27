<?php

namespace App\Support;

/**
 * A small, dependency-free HTML sanitizer for rich-text post bodies.
 *
 * The post editor (Trix) produces HTML. Because that HTML is later rendered
 * unescaped, we must strip anything dangerous to prevent stored XSS. We use a
 * strict tag allowlist plus removal of event-handler attributes and unsafe
 * URL schemes.
 */
class HtmlSanitizer
{
    /** Tags the rich-text editor is allowed to produce. */
    private const ALLOWED_TAGS = '<p><br><div><span><strong><b><em><i><u><s><del>'
        . '<a><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><hr>';

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // 1. Keep only allowlisted tags.
        $clean = strip_tags($html, self::ALLOWED_TAGS);

        // 2. Remove inline event handlers (onclick, onerror, ...).
        $clean = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $clean);
        $clean = preg_replace('/\son[a-z]+\s*=\s*[^\s>]+/i', '', $clean);

        // 3. Neutralise dangerous URL schemes in href/src.
        $clean = preg_replace('/(href|src)\s*=\s*"\s*javascript:[^"]*"/i', '$1="#"', $clean);
        $clean = preg_replace("/(href|src)\s*=\s*'\s*javascript:[^']*'/i", '$1=\'#\'', $clean);
        $clean = preg_replace('/(href|src)\s*=\s*"\s*data:[^"]*"/i', '$1="#"', $clean);

        return trim($clean);
    }

    /**
     * True when the content has visible text once tags/whitespace are removed.
     */
    public static function isEmpty(?string $html): bool
    {
        return trim(strip_tags((string) $html)) === '';
    }
}
