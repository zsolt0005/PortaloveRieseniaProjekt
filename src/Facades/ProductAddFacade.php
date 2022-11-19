<?php declare(strict_types=1);

namespace Zsolt\Pr\Facades;

use Exception;
use PDO;
use Zsolt\Pr\Model\Data\CategoryData;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;

/**
 * @package Zsolt\Pr\Facades
 * @author Zsolt Döme
 */
class ProductAddFacade extends AFacade
{
    private ProductCategoryService $productCategoryService;

    public function __construct(private PDO $database)
    {
        $this->productCategoryService = new ProductCategoryService($this->database);
    }

    /**
     * @throws Exception
     */
    public function prepareDefaultData(): array
    {
        $data = [];

        $data['categories'] = $this->prepareCategoriesData();

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
}