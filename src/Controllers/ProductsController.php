<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use Exception;
use PDOException;
use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Core\Utils;
use Zsolt\Pr\Facades\ProductsFacade;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;
use Zsolt\Pr\Model\Entities\Services\ProductService;
use Zsolt\Pr\Model\Entities\Services\ProductToCategoryService;

/**
 * Product controller
 *
 * @package Zsolt\Pr\Controllers
 * @author Zsolt Döme
 */
final class ProductsController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "products";

    /** @var string Action to delete category */
    public const ACTION_DELETE_CATEGORY = "delete_category";

    /** @var string Action to delete product */
    public const ACTION_DELETE_PRODUCT = "delete_product";

    /** @var ProductsFacade Facade */
    private ProductsFacade $facade;

    private ProductService $productService;
    private ProductCategoryService $productCategoryService;
    private ProductToCategoryService $productToCategoryService;

    /** @inheritDoc */
    public function beforeRender(): void
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new ProductsFacade($this->database);
        $this->productService = new ProductService($this->database);
        $this->productCategoryService = new ProductCategoryService($this->database);
        $this->productToCategoryService = new ProductToCategoryService($this->database);
    }

    /** @inheritDoc */
    public function render(): void
    {
        if(isset($this->request->post[self::ACTION_DELETE_CATEGORY]))
        {
            $this->deleteCategory($this->request->post);
        }
        else if(isset($this->request->post[self::ACTION_DELETE_PRODUCT]))
        {
            $this->deleteProduct($this->request->post);
        }

        $this->templateData = [
            'data' => $this->facade->prepareDefaultData(),
            'navigation' => $this->facade->prepareNavigationData(self::NAME)
        ];
    }

    private function deleteCategory(array $args): void
    {
        $categoryCode = $args["delete_category_code"] ?? null;

        if(empty($categoryCode) || !is_string($categoryCode))
        {
            return;
        }

        try
        {
            $category = $this->productCategoryService->fetchByCode($categoryCode);
            $category->delete();
        }
        catch (PDOException $e)
        {
            if($e->getCode() === "23000")
            {
                $this->sendErrorMessage("Category {$category->getName()} can't be deleted, because a product is using this category!");
            }
            else{
                $this->sendErrorMessage("Category with code {$categoryCode} does not exists!");
            }

            return;
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong. {$e->getMessage()}");
            return;
        }

        $this->sendSuccessMessage("Category {$category->getName()} was successfully deleted");
    }

    private function deleteProduct(array $args): void
    {
        $productCode = $args["delete_product_code"] ?? null;

        if(empty($productCode) || !is_string($productCode))
        {
            return;
        }

        // Get the product
        try
        {
            $product = $this->productService->fetchByCode($productCode);
        }
        catch (PDOException)
        {
            $this->sendErrorMessage("Product with code {$productCode} does not exists!");
            return;
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong. {$e->getMessage()}");
            return;
        }

        // Get the relation table
        try
        {
            $productToCategories = $this->productToCategoryService->fetchByProductId($product->getId());
        }
        catch (PDOException)
        {
            $productToCategories = [];
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong. {$e->getMessage()}");
            return;
        }

        $this->database->beginTransaction();

        // Delete relations
        foreach ($productToCategories as $productToCategory)
        {
            try
            {
                $productToCategory->delete();
            }
            catch (Exception $e)
            {
                $this->sendErrorMessage(
                    "Something went wrong while deleting relation for Product: {$product->getName()} and Category: {$productToCategory->getCategory()->getName()}. 
                    {$e->getMessage()}"
                );
                $this->database->rollBack();
                return;
            }
        }

        try
        {
            $product->delete();
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong while deleting Product: {$product->getName()}. {$e->getMessage()}");
            $this->database->rollBack();
            return;
        }

        $this->database->commit();

        $this->sendSuccessMessage("Product {$product->getName()} was successfully deleted");
    }
}