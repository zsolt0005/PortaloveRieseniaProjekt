<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Data;

use Zsolt\Pr\Controllers\ProductsController;
use Zsolt\Pr\Model\Entities\Product;
use Zsolt\Pr\Model\Entities\ProductCategory;

/**
 * Default data for {@see ProductsController}
 *
 * @package Zsolt\Pr\Model\Data
 * @author Zsolt Döme
 */
final class ProductsDefaultData
{
    /** @var ProductCategory[] */
    public array $productCategories = [];

    /** @var Product[] */
    public array $products = [];
}