<?php declare(strict_types=1);

namespace Zsolt\Pr\Facades;

use Zsolt\Pr\Controllers\DefaultController;
use Zsolt\Pr\Model\Data\NavigationData;

/**
 * @package Zsolt\Pr\Facades
 * @author Zsolt Döme
 */
abstract class AFacade
{
    /**
     * Prepares the navigation data
     *
     * @param string $path
     *
     * @return NavigationData
     */
    public function prepareNavigationData(string $path): NavigationData
    {
        $navigationData = new NavigationData();
        $navigationData->path = $path;
        return $navigationData;
    }
}