<?php

namespace App\Services;

class HtmlContentSanitizer
{
    private array $allowedTags = [
        'a', 'blockquote', 'br', 'code', 'div', 'em', 'h1', 'h2', 'h3', 'i', 'img',
        'li', 'ol', 'p', 'pre', 's', 'span', 'strong', 'u', 'ul',
    ];

    private array $allowedClasses = [
        'mention-link',
        'post-reference-card',
        'post-reference-title',
        'post-reference-meta',
        'link-preview-card',
        'link-preview-domain',
        'link-preview-title',
        'link-preview-description',
        'link-preview-image',
    ];

    public function cleanPost(?string $html): string
    {
        return $this->clean($html);
    }

    public function cleanComment(?string $html): string
    {
        return $this->clean($html);
    }

    public function plainText(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    public function extractMentionUsernames(?string $html): array
    {
        preg_match_all('/@([a-zA-Z0-9._]+)/', $this->plainText($html), $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function clean(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|svg|math)\b[^>]*>.*?<\/\1>/isu', '', $html);
        $html = strip_tags($html, '<' . implode('><', $this->allowedTags) . '>');
        $html = preg_replace_callback('/<([a-z0-9]+)([^>]*)>/iu', function (array $match) {
            $tag = strtolower($match[1]);
            $attributes = $this->sanitizeAttributes($tag, $match[2] ?? '');

            return '<' . $tag . $attributes . '>';
        }, $html);

        return trim($html);
    }

    private function sanitizeAttributes(string $tag, string $attributeText): string
    {
        preg_match_all('/([a-zA-Z0-9:_-]+)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))/u', $attributeText, $matches, PREG_SET_ORDER);

        $attributes = [];

        foreach ($matches as $match) {
            $name = strtolower($match[1]);
            $value = html_entity_decode($match[3] ?? $match[4] ?? $match[5] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if (str_starts_with($name, 'on') || in_array($name, ['style', 'srcdoc'], true)) {
                continue;
            }

            if ($name === 'href' && $tag === 'a' && $this->isSafeUrl($value)) {
                $attributes['href'] = $value;
                $attributes['target'] = '_blank';
                $attributes['rel'] = 'noopener noreferrer nofollow ugc';
                continue;
            }

            if ($name === 'src' && $tag === 'img' && $this->isSafeImageUrl($value)) {
                $attributes['src'] = $value;
                continue;
            }

            if ($name === 'alt' && $tag === 'img') {
                $attributes['alt'] = mb_substr($value, 0, 160);
                continue;
            }

            if ($name === 'class') {
                $classes = array_filter(explode(' ', $value), fn ($class) => in_array($class, $this->allowedClasses, true));
                if ($classes) {
                    $attributes['class'] = implode(' ', $classes);
                }
                continue;
            }

            if (str_starts_with($name, 'data-') && preg_match('/^data-(post-id|preview-id|username|user-id|url)$/', $name)) {
                $attributes[$name] = mb_substr($value, 0, 2048);
            }
        }

        return collect($attributes)
            ->map(fn ($value, $name) => ' ' . $name . '="' . e($value) . '"')
            ->implode('');
    }

    private function isSafeImageUrl(string $url): bool
    {
        if (str_starts_with($url, '/storage/')) {
            return true;
        }

        return $this->isSafeUrl($url);
    }

    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if (str_starts_with($url, '/posts/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true)
            && !preg_match('/^\s*(javascript|data|vbscript):/i', $url);
    }
}
