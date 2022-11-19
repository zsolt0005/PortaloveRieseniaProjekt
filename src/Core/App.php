<?php declare(strict_types=1);

namespace Zsolt\Pr\Core;

use Couchbase\InvalidConfigurationException;
use HttpRequestException;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

/**
 * Application
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
final class App
{
    /**
     * Constructor
     *
     * @throws Exception
     * @throws InvalidConfigurationException
     */
    public function __construct(private Router $router)
    {
        $this->handleRequest();
    }

    /**
     * Handles the initial request data
     *
     * @throws Exception
     * @throws InvalidConfigurationException
     */
    private function handleRequest(): void
    {
        session_start();

        $config = $this->loadConfig();

        $request = new Request();

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestUriSegments = explode('?', $requestUri);
        $requestUri = count($requestUriSegments) > 0 ? $requestUriSegments[0] : '/';

        $request->pathInfo = $requestUri;
        $request->params = $_REQUEST;
        $request->get = $_GET;
        $request->post = $_POST;

        $this->router->handleRequest($request, $config);
    }

    /**
     * Load application config
     *
     * @throws Exception
     */
    private function loadConfig(): array
    {
        return Neon::decodeFile(__DIR__ . '/../Config/config.neon');
    }
}