<?php

declare(strict_types=1);

namespace App\Utils;

final class SocialMediaUtils
{
    public static function makeYoutubeEmbed(string $content): string
    {
        // Match both full and short YouTube URLs
        $pattern = '~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})~';

        return preg_replace_callback($pattern, function ($matches) {
            $videoId = $matches[1];

            // Return embedded iframe
            return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . htmlspecialchars($videoId, ENT_QUOTES) . '" frameborder="0" allowfullscreen></iframe>';
        }, $content);
    }

    public static function replaceYouTubeWithThumbnail(string $text): string
    {
        $pattern = '~(?:https?://)?(?:www\.)?(?:youtube\.com/(?:watch\?v=|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})~';

        return preg_replace_callback($pattern, function ($matches) {
            $videoId = $matches[1];
            $thumbnailUrl = 'https://img.youtube.com/vi/' . htmlspecialchars($videoId, ENT_QUOTES) . '/hqdefault.jpg';
            $videoUrl = 'https://www.youtube.com/watch?v=' . htmlspecialchars($videoId, ENT_QUOTES);

            return '<a href="' . $videoUrl . '" target="_blank" rel="noopener noreferrer">
                        <img src="' . $thumbnailUrl . '" alt="YouTube video thumbnail" width="480" height="360">
                    </a>';
        }, $text);

        /*
        responsive thumbnails:

        .youtube-thumb img {
        max-width: 100%;
        height: auto;
        display: block;
        }
        */
    }

    public static function linkifyUrls(string $text): string
    {
        return preg_replace_callback('/(https?:\/\/[^\s<]+)/i', static function (array $matches) {
                $url = $matches[1];

                // Parse the host to filter
                $host = parse_url($url, PHP_URL_HOST);

                // Normalize and check for excluded domains
                $host = strtolower($host ?? '');
                $excludedHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'tiktok.com', 'www.tiktok.com'];

                foreach ($excludedHosts as $excluded) {
                    if (str_contains($host, $excluded)) {
                        return $url; // return plain text URL (no link)
                    }
                }

                // Convert to clickable link
                $escaped = htmlspecialchars($url, ENT_QUOTES);
                
                return '<a href="' . $escaped . '" target="_blank" rel="noopener noreferrer">' . $escaped . '</a>';
            }, $text);
    }
}
