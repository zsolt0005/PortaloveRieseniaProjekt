<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use Exception;
use PDOException;
use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Core\Utils;
use Zsolt\Pr\Facades\ProductAddFacade;
use Zsolt\Pr\Model\Entities\Product;
use Zsolt\Pr\Model\Entities\ProductToCategory;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;
use Zsolt\Pr\Model\Entities\Services\ProductService;
use Zsolt\Pr\Utils\TypeUtils;

/**
 * Product add controller
 *
 * @package Zsolt\Pr\Controllers
 * @author Zsolt Döme
 */
class ProductAddController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "products";

    public const ACTION_ADD_PRODUCT = "add_product";

    /** @var ProductAddFacade Facade */
    private ProductAddFacade $facade;

    private ProductService $productService;
    private ProductCategoryService $productCategoryService;

    /** @inheritDoc */
    public function beforeRender(): void
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new ProductAddFacade($this->database);
        $this->productService = new ProductService($this->database);
        $this->productCategoryService = new ProductCategoryService($this->database);
    }

    /** @inheritDoc */
    public function render(): void
    {
        $data = $this->facade->prepareDefaultData();

        $this->templateData = [
            'data' => $data,
            'navigation' => $this->facade->prepareNavigationData(self::NAME)
        ];

        if(isset($this->request->post[self::ACTION_ADD_PRODUCT]))
        {
            $this->createProduct($this->request->post);
        }
    }

    private function createProduct(array $args): void
    {
        $name = $args["name"] ?? null;
        $categoryCode = $args["category_code"] ?? null;
        $unitsSold = $args["units_sold"] ?? null;
        $unitsInStock = $args["units_in_stock"] ?? null;

        // Validate input data
        if(empty($name))
        {
            $this->sendErrorMessage("Product name is required");
            return;
        }

        if(empty($categoryCode))
        {
            $this->sendErrorMessage("Please select a category");
            return;
        }

        if(!is_numeric($unitsSold))
        {
            $this->sendErrorMessage("Invalid value for Units Sold: {$unitsSold}");
            return;
        }

        if(!is_numeric($unitsInStock))
        {
            $this->sendErrorMessage("Invalid value for Units Sold: {$unitsInStock}");
            return;
        }

        try
        {
            if($this->productService->isExistsByName($name))
            {
                $this->sendErrorMessage("Product name {$name} already exists!");
                return;
            }
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong. {$e->getMessage()}");
            return;
        }

        try
        {
            $category = $this->productCategoryService->fetchByCode($categoryCode);
        }
        catch (PDOException $e)
        {
            $this->sendErrorMessage("Selected category was not found in the database. {$categoryCode}");
            return;
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong while retrieving category. {$e->getMessage()}");
            return;
        }

        $this->database->beginTransaction();

        try
        {
            $product = new Product($this->database);
            $product->setName($name);
            $product->setUnitsSold(TypeUtils::strictConvertToInt($unitsSold));
            $product->setUnitsInStock(TypeUtils::strictConvertToInt($unitsInStock));
            $product->save(true);
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong while saving product. {$e->getMessage()}");
            $this->database->rollBack();
            return;
        }

        try
        {
            $productToCategory = new ProductToCategory($this->database);
            $productToCategory->setProductId($product->getId());
            $productToCategory->setCategoryId($category->getId());
            $productToCategory->save();
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong while saving relation product to category. {$e->getMessage()}");
            $this->database->rollBack();
            return;
        }

        $this->database->commit();

        $this->sendSuccessMessage("Product {$name} was created successfully");
    }
}