<?php

namespace App\Services;

class EmailContentSanitizer
{
    public function sanitize(string $html): string
    {
        $allowed = '<p><br><div><span><strong><b><em><i><u><s><h1><h2><h3><h4><ul><ol><li><blockquote><hr><table><thead><tbody><tfoot><tr><th><td><a><img>';
        $html = strip_tags($html, $allowed);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*(?:javascript|data:text\/html)[^"\']*\2/iu', '$1="#"', $html) ?? '';

        return trim($html);
    }
}
