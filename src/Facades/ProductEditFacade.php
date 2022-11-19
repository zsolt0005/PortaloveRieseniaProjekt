<?php declare(strict_types=1);

namespace Zsolt\Pr\Facades;

use Exception;
use PDO;
use PDOException;
use Zsolt\Pr\Core\Utils;
use Zsolt\Pr\Model\Data\CategoryData;
use Zsolt\Pr\Model\Data\ProductData;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;
use Zsolt\Pr\Model\Entities\Services\ProductService;
use Zsolt\Pr\Model\Entities\Services\ProductToCategoryService;

/**
 * TODO Description
 *
 * @package Zsolt\Pr\Facades
 * @author Zsolt Döme
 */
class ProductEditFacade extends AFacade
{
    private ProductService $productService;
    private ProductCategoryService $productCategoryService;
    private ProductToCategoryService $productToCategoryService;

    public function __construct(private readonly PDO $database)
    {
        $this->productService = new ProductService($this->database);
        $this->productCategoryService = new ProductCategoryService($this->database);
        $this->productToCategoryService = new ProductToCategoryService($this->database);
    }

    /**
     * @throws Exception
     */
    public function prepareDefaultData(string $productCode): array
    {
        $data = [];

        $data['categories'] = $this->prepareCategoriesData();
        $data['product'] = $this->prepareProduct($productCode);

        return $data;
    }

    /**
     * @return array<CategoryData>
     * @throws Exception
     */
    private function prepareCategoriesData(): array
    {
        $categoriesData = [];

        $categories = $this->productCategoryService->fetchAll();

        foreach ($categories as $category)
        {
            $categoryData = new CategoryData();

            $categoryData->code = $category->getCode();
            $categoryData->name = $category->getName();

            $categoriesData[] = $categoryData;
        }

        return $categoriesData;
    }

    /**
     * @throws Exception
     */
    private function prepareProduct(string $productCode): ProductData
    {
        $productData = new ProductData();

        try
        {
            $product = $this->productService->fetchByCode($productCode);
        }
        catch (PDOException)
        {
            throw new Exception("Product with code {$productCode} does not exists!");
        }
        catch (Exception $e)
        {
            throw new Exception("Something went wrong. {$e->getMessage()}");
        }

        try
        {
            $productToCategories = $this->productToCategoryService->fetchByProductId($product->getId());
            $productToCategory = count($productToCategories) > 0 ? $productToCategories[0] : null;
        }
        catch (PDOException)
        {
            $productToCategory = null;
        }
        catch (Exception $e)
        {
            throw new Exception("Something went wrong. {$e->getMessage()}");
        }

        try
        {
            if($productToCategory !== null)
            {
                $category = $this->productCategoryService->fetchById($productToCategory->getCategoryId());
            }
            else
            {
                $category = null;
            }
        }
        catch (Exception $e)
        {
            throw new Exception("Something went wrong. {$e->getMessage()}");
        }

        $productData->product = $product;
        $productData->category = $category;
        $productData->productToCategory = $productToCategory;

        return $productData;
    }
}