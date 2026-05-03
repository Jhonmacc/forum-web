<?php

namespace App\Http\Controllers;

use App\Models\LinkPreview;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LinkPreviewController extends Controller
{
    private const MAX_BYTES = 262144;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url:http,https', 'max:2048'],
        ]);

        $url = $this->normalizeUrl($validated['url']);
        $this->assertPublicHttpUrl($url);

        $hash = hash('sha256', $url);
        $existing = LinkPreview::where('url_hash', $hash)->first();

        if (!$this->youtubeVideoId($url) && $existing && $existing->status === 'ready' && $existing->fetched_at && $existing->fetched_at->gt(now()->subDays(14))) {
            return response()->json($this->serialize($existing));
        }

        $preview = $existing ?: new LinkPreview([
            'url_hash' => $hash,
            'url' => $url,
            'domain' => parse_url($url, PHP_URL_HOST),
        ]);

        if ($youtube = $this->youtubeMetadata($url)) {
            $preview->fill([
                'url' => $url,
                'domain' => parse_url($url, PHP_URL_HOST),
                'title' => $youtube['title'],
                'description' => $youtube['description'],
                'image_url' => $youtube['image_url'],
                'status' => 'ready',
                'fetched_at' => now(),
            ])->save();

            return response()->json($this->serialize($preview));
        }

        try {
            $response = Http::timeout(4)
                ->connectTimeout(2)
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml',
                    'User-Agent' => 'TechDevsLinkPreview/1.0',
                ])
                ->withOptions([
                    'allow_redirects' => ['max' => 2, 'track_redirects' => true],
                    'stream' => true,
                ])
                ->get($url);

            if (!$response->successful()) {
                throw ValidationException::withMessages(['url' => __('validation.url')]);
            }

            if ((int) $response->header('Content-Length', 0) > self::MAX_BYTES) {
                throw ValidationException::withMessages(['url' => __('validation.url')]);
            }

            $effectiveUrl = (string) ($response->handlerStats()['url'] ?? $url);
            $this->assertPublicHttpUrl($effectiveUrl);

            $html = mb_substr($response->body(), 0, self::MAX_BYTES);
            $metadata = $this->extractMetadata($html, $effectiveUrl);

            $preview->fill([
                'url' => $effectiveUrl,
                'domain' => parse_url($effectiveUrl, PHP_URL_HOST),
                'title' => $metadata['title'],
                'description' => $metadata['description'],
                'image_url' => $metadata['image_url'],
                'status' => 'ready',
                'fetched_at' => now(),
            ])->save();
        } catch (ConnectionException $exception) {
            $preview->fill([
                'status' => 'failed',
                'fetched_at' => now(),
            ])->save();
        }

        return response()->json($this->serialize($preview));
    }

    private function normalizeUrl(string $url): string
    {
        return trim($url);
    }

    private function youtubeMetadata(string $url): ?array
    {
        $videoId = $this->youtubeVideoId($url);

        if (!$videoId) {
            return null;
        }

        $fallback = [
            'title' => 'YouTube video',
            'description' => parse_url($url, PHP_URL_HOST),
            'image_url' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
        ];

        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->acceptJson()
                ->get('https://www.youtube.com/oembed', [
                    'url' => $url,
                    'format' => 'json',
                ]);

            if (!$response->successful()) {
                return $fallback;
            }

            $data = $response->json();

            return [
                'title' => $this->cleanText($data['title'] ?? null, 160) ?: $fallback['title'],
                'description' => $this->cleanText($data['author_name'] ?? null, 280) ?: $fallback['description'],
                'image_url' => filter_var($data['thumbnail_url'] ?? null, FILTER_VALIDATE_URL)
                    ? $data['thumbnail_url']
                    : $fallback['image_url'],
            ];
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function youtubeVideoId(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $query['v'])) {
                return $query['v'];
            }

            if (preg_match('/^(shorts|embed)\/([a-zA-Z0-9_-]{6,20})$/', $path, $matches)) {
                return $matches[2];
            }
        }

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true) && preg_match('/^([a-zA-Z0-9_-]{6,20})$/', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function assertPublicHttpUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if (!in_array($scheme, ['http', 'https'], true) || !$host || $host === 'localhost' || str_ends_with($host, '.local')) {
            throw ValidationException::withMessages(['url' => __('validation.url')]);
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        if (!$ips) {
            throw ValidationException::withMessages(['url' => __('validation.url')]);
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw ValidationException::withMessages(['url' => __('validation.url')]);
            }
        }
    }

    private function extractMetadata(string $html, string $url): array
    {
        $title = $this->matchMeta($html, 'property', 'og:title')
            ?: $this->matchTag($html, 'title')
            ?: parse_url($url, PHP_URL_HOST);
        $description = $this->matchMeta($html, 'property', 'og:description')
            ?: $this->matchMeta($html, 'name', 'description');
        $image = $this->matchMeta($html, 'property', 'og:image');

        return [
            'title' => $this->cleanText($title, 160),
            'description' => $this->cleanText($description, 280),
            'image_url' => $image && filter_var($image, FILTER_VALIDATE_URL) ? $image : null,
        ];
    }

    private function matchMeta(string $html, string $attribute, string $value): ?string
    {
        $pattern = '/<meta\b(?=[^>]*\b' . preg_quote($attribute, '/') . '=["\']' . preg_quote($value, '/') . '["\'])(?=[^>]*\bcontent=["\']([^"\']*)["\'])[^>]*>/iu';

        return preg_match($pattern, $html, $matches) ? html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
    }

    private function matchTag(string $html, string $tag): ?string
    {
        return preg_match('/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/isu', $html, $matches)
            ? html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : null;
    }

    private function cleanText(?string $value, int $limit): ?string
    {
        if (!$value) {
            return null;
        }

        return mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($value))), 0, $limit);
    }

    private function serialize(LinkPreview $preview): array
    {
        return [
            'id' => $preview->id,
            'url' => $preview->url,
            'domain' => $preview->domain,
            'title' => $preview->title ?: $preview->domain,
            'description' => $preview->description,
            'image_url' => $preview->image_url,
            'status' => $preview->status,
        ];
    }
}
