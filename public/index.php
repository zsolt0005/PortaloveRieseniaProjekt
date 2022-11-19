<?php declare(strict_types = 1);

require_once '../vendor/autoload.php';

use Zsolt\Pr\Controllers\AccountsController;
use Zsolt\Pr\Controllers\CategoryAddController;
use Zsolt\Pr\Controllers\DefaultController;
use Zsolt\Pr\Controllers\ProductAddController;
use Zsolt\Pr\Controllers\ProductEditController;
use Zsolt\Pr\Controllers\ProductsController;
use Zsolt\Pr\Core\App;
use Zsolt\Pr\Core\Router;

$router = new Router();
$router->register('/',          DefaultController::class);
$router->register('/dashboard', DefaultController::class);
$router->register('/products',  ProductsController::class);
$router->register('/accounts',  AccountsController::class);
$router->register('/product-add',  ProductAddController::class);
$router->register('/product-edit',  ProductEditController::class);
$router->register('/category-add',  CategoryAddController::class);

// We want to throw the exceptions
$app = new App($router);

