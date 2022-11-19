<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Data;

use Zsolt\Pr\Model\Entities\Product;
use Zsolt\Pr\Model\Entities\ProductCategory;
use Zsolt\Pr\Model\Entities\ProductToCategory;

/**
 * TODO Description
 *
 * @package Zsolt\Pr\Model\Data
 * @author Zsolt Döme
 */
final class ProductData
{
    public Product $product;
    public ?ProductCategory $category = null;
    public ?ProductToCategory $productToCategory = null;
}