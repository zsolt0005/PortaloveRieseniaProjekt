<?php declare(strict_types=1);

namespace Zsolt\Pr\Core;

/**
 * Common utils
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
final class Utils
{
    public static function dump(mixed $value): void
    {
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
    }
}