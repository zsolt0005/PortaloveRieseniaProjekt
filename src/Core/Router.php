<?php declare(strict_types=1);

namespace Zsolt\Pr\Core;

use Couchbase\InvalidConfigurationException;
use Exception;
use Latte\Engine;
use PDO;
use ReflectionClass;

/**
 * Basic router
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
final class Router
{
    /** @var array<string, class-string<ABaseController>> defined routes */
    private array $routes = [];

    /**
     * Register a new route
     * @param string                        $url
     * @param class-string<ABaseController> $controller
     *
     * @return bool
     */
    public function register(string $url, string $controller): bool
    {
        if (array_key_exists($url, $this->routes)) {
            return false;
        }

        $this->routes[$url] = $controller;

        return true;
    }

    /**
     * Handles the request and renders the page
     *
     * @throws Exception
     * @throws InvalidConfigurationException
     */
    public function handleRequest(Request $request, array $config): void
    {
        $controllerClass = $this->routes[$request->pathInfo] ?? throw new Exception("Route not found");

        /** @var ABaseController $controller */
        $controller = new $controllerClass();

        $databaseConfig = $config['database'] ?? throw new InvalidConfigurationException("Database config not found");
        $database = new PDO("mysql:host={$databaseConfig['host']};dbname={$databaseConfig['schema']}", $databaseConfig['user'], $databaseConfig['password']);

        $classReflection = new ReflectionClass($controller);
        $templateFile = str_replace("Controller", "", $classReflection->getShortName());
        $latte = new Engine();

        // Inject dependencies (We don't have a DI container, so it's an easy hack)
        $controller->injectDatabase($database);
        $controller->injectTemplate($latte);
        $controller->injectRequest($request);

        $controller->beforeRender();
        $controller->render();
        $controller->afterRender();

        $templateData = $controller->templateData;
        $errorMessages = $_SESSION['errors'] ?? [];
        $successMessages = $_SESSION['successes'] ?? [];

        $templateData['errorMessages'] = $errorMessages;
        $templateData['successMessages'] = $successMessages;

        $_SESSION['errors'] = [];
        $_SESSION['successes'] = [];

        $latte->render(__DIR__ . "/../Templates/{$templateFile}.latte", $templateData);
    }
}