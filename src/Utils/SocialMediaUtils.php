<?php

declare(strict_types=1);

namespace App\Utils;

final class SocialMediaUtils
{
    public static function makeYoutubeEmbed(string $content): string
    {
        // Match both full and short/embed YouTube URLs
        $pattern = '~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([a-zA-Z0-9_-]{11})(?:[^\s<]*)?~i';

        $transformedContent = preg_replace_callback($pattern, function ($matches) {
            $videoId = $matches[1];

            return \sprintf(
                '<iframe width="560" height="315" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>',
                htmlspecialchars($videoId, \ENT_QUOTES)
            );
        }, $content);

        return $transformedContent ?? $content;
    }

    public static function replaceYouTubeWithThumbnail(string $content): string
    {
        $pattern = '~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})~';

        $transformedContent = preg_replace_callback($pattern, function ($matches) {
            $videoId = $matches[1];
            $thumbnailUrl = 'https://img.youtube.com/vi/'.htmlspecialchars($videoId, \ENT_QUOTES).'/hqdefault.jpg';
            $videoUrl = 'https://www.youtube.com/watch?v='.htmlspecialchars($videoId, \ENT_QUOTES);

            return '<a href="'.$videoUrl.'" target="_blank" rel="noopener noreferrer">
                        <img src="'.$thumbnailUrl.'" alt="YouTube video thumbnail" width="480" height="360">
                    </a>';
        }, $content);

        return $transformedContent ?? $content;
    }

    public static function linkifyUrls(string $content, bool $excludeSocialMedia = false): string
    {
        $transformedContent = preg_replace_callback('/(https?:\/\/[^\s<]+)/i', static function (array $matches) use ($excludeSocialMedia) {
            $url = $matches[1];

            // Parse the host to filter
            $host = strtolower((string) parse_url($url, \PHP_URL_HOST));

            if (true === $excludeSocialMedia) {
                $excludedHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'tiktok.com', 'www.tiktok.com'];

                foreach ($excludedHosts as $excluded) {
                    if (str_contains($host, $excluded)) {
                        return $url; // return plain text URL (no link)
                    }
                }
            }

            // Convert to clickable link
            $escaped = htmlspecialchars($url, \ENT_QUOTES);

            return '<a href="'.$escaped.'" target="_blank" rel="noopener noreferrer">'.$escaped.'</a>';
        }, $content);

        return $transformedContent ?? $content;
    }
}
