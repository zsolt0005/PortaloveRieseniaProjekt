<?php declare(strict_types=1);

namespace Zsolt\Pr\Facades;

use Exception;
use PDO;
use Zsolt\Pr\Controllers\ProductsController;
use Zsolt\Pr\Model\Data\NavigationData;
use Zsolt\Pr\Model\Data\ProductsDefaultData;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;
use Zsolt\Pr\Model\Entities\Services\ProductService;

/**
 * Facade
 *
 * @package Zsolt\Pr\Facades
 * @author Zsolt Döme
 */
final class ProductsFacade extends AFacade
{
    private ProductCategoryService $productCategoryService;
    private ProductService $productService;

    public function __construct(private PDO $database)
    {
        // No DI :/
        $this->productCategoryService = new ProductCategoryService($this->database);
        $this->productService = new ProductService($this->database);
    }

    /**
     * Prepare all the data the template needs
     *
     * @return ProductsDefaultData
     * @throws Exception
     */
    public function prepareDefaultData(): ProductsDefaultData
    {
        $data = new ProductsDefaultData();

        $data->productCategories = $this->productCategoryService->fetchAll();
        $data->products = $this->productService->fetchAll();

        return $data;
    }
}