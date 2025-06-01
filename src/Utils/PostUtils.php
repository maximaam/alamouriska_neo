<?php

declare(strict_types=1);

namespace App\Utils;

use App\Enum\PostType;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;

#[AsRoutingConditionService(alias: 'post_utils')]
final class PostUtils
{

    /**
     * @var array<string, PostType>
    */
    public static array $typesSeoSlugs = [
        'mots-algeriens' => PostType::word,
        'expressions-algeriennes' => PostType::expression,
        'proverbes-algeriens' => PostType::proverb,
        'blagues-algeriennes' => PostType::joke,
    ];

    public static function getTypeBySeoSlug(string $seoSlug): ?PostType
    {
        return self::$typesSeoSlugs[$seoSlug] ?? null;
    }

    public static function getValidSeoSlugs(): string
    {
        return implode('|', array_keys(static::$typesSeoSlugs));
    }
}