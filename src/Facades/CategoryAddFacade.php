<?php declare(strict_types=1);

namespace Zsolt\Pr\Facades;

use PDO;

/**
 * @package Zsolt\Pr\Facades
 * @author Zsolt Döme
 */
class CategoryAddFacade extends AFacade
{
    public function __construct(private PDO $database)
    {
    }
}