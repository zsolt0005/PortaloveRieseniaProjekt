<?php declare(strict_types=1);

namespace Zsolt\Pr\Core;

/**
 * Request object
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
class Request
{
    /** @var array Prams - Includes GET, POST and COOKIES */
    public array $params;

    /** @var array Post params */
    public array $post;

    /** @var array Get params */
    public array $get;

    /** @var string Path information */
    public string $pathInfo;
}