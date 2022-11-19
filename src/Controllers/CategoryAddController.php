<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use Exception;
use PDOException;
use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Core\Utils;
use Zsolt\Pr\Facades\CategoryAddFacade;
use Zsolt\Pr\Model\Entities\ProductCategory;
use Zsolt\Pr\Model\Entities\Services\ProductCategoryService;

/**
 * @package Zsolt\Pr\Controllers
 * @author Zsolt Döme
 */
class CategoryAddController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "products";

    /** @var string Flag for creating a category */
    public const ACTION_CREATE_CATEGORY = "create_category";

    /** @var CategoryAddFacade Facade */
    private CategoryAddFacade $facade;

    private ProductCategoryService $productCategoryService;

    /** @inheritDoc */
    public function beforeRender(): void
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new CategoryAddFacade($this->database);
        $this->productCategoryService = new ProductCategoryService($this->database);
    }

    /** @inheritDoc */
    public function render(): void
    {
        $this->templateData = [
            'data' => [],
            'navigation' => $this->facade->prepareNavigationData(self::NAME)
        ];

        if(isset($this->request->post[self::ACTION_CREATE_CATEGORY]))
        {
            $this->createCategory($this->request->post);
        }
    }

    private function createCategory(array $args): void
    {
        $name = $args["name"] ?? null;

        if(empty($name))
        {
            $this->sendErrorMessage("Category name is required!");
        }

        try
        {
            if($this->productCategoryService->isExistsByName($name))
            {
                $this->sendErrorMessage("Category name {$name} already exists!");
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
            $category = new ProductCategory($this->database);
            $category->setName($name);
            $category->save();
        }
        catch (Exception $e)
        {
            $this->sendErrorMessage("Something went wrong while saving category. {$e->getMessage()}");
        }

        $this->sendSuccessMessage("Category {$name} was created successfully");
    }
}