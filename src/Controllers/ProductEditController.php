<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use Exception;
use PDOException;
use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Facades\ProductEditFacade;
use Zsolt\Pr\Model\Data\ProductData;
use Zsolt\Pr\Model\Entities\ProductToCategory;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;
use Zsolt\Pr\Utils\TypeUtils;

/**
 * TODO Description
 *
 * @package Zsolt\Pr\Controllers
 * @author Zsolt Döme
 */
class ProductEditController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "products";

    public const ACTION_EDIT_PRODUCT = "edit_product";

    /** @var ProductEditFacade Facade */
    private ProductEditFacade $facade;

    private ProductCategoryService $productCategoryService;

    /** @inheritDoc */
    public function beforeRender(): void
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new ProductEditFacade($this->database);
        $this->productCategoryService = new ProductCategoryService($this->database);
    }

    /** @inheritDoc */
    public function render(): void
    {
        try
        {
            $data = $this->facade->prepareDefaultData($this->request->get['productCode']);
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage($e->getMessage());
            header('Location: products');
            exit;
        }

        if(isset($this->request->post[self::ACTION_EDIT_PRODUCT]))
        {
            $this->editProduct($this->request->post, $data);
        }

        $this->templateData = [
            'data' => $data,
            'navigation' => $this->facade->prepareNavigationData(self::NAME)
        ];
    }

    private function editProduct(array $args, array &$data): void
    {
        $name = $args["name"] ?? null;
        $categoryCode = $args["category_code"] ?? null;
        $unitsSold = $args["units_sold"] ?? null;
        $unitsInStock = $args["units_in_stock"] ?? null;

        /** @var ProductData $productData */
        $productData = $data['product'];

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

        $this->database->beginTransaction();

        try
        {
            $product = $productData->product;
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

        if($categoryCode !== $productData->category->getCode())
        {
            try
            {
                $category = $this->productCategoryService->fetchByCode($categoryCode);
            }
            catch (PDOException $e)
            {
                $this->sendErrorMessage("Selected category was not found in the database. {$categoryCode}");
                $this->database->rollBack();
                return;
            }
            catch (Exception $e)
            {
                $this->sendErrorMessage("Something went wrong while retrieving category. {$e->getMessage()}");
                $this->database->rollBack();
                return;
            }

            try
            {
                $productData->productToCategory->delete();

                $productToCategory = new ProductToCategory($this->database);
                $productToCategory->setProductId($product->getId());
                $productToCategory->setCategoryId($category->getId());
                $productToCategory->save();

                $productData->productToCategory = $productToCategory;
                $productData->category = $category;
            }
            catch (Exception $e)
            {
                $this->sendErrorMessage("Something went wrong while saving relation product to category. {$e->getMessage()}");
                $this->database->rollBack();
                return;
            }
        }

        $this->database->commit();

        $this->sendSuccessMessage("Product {$name} was edited successfully");
    }
}