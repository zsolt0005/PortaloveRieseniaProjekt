<?php declare(strict_types=1);

namespace Zsolt\Pr\Core;

use Exception;
use Latte\Engine;
use PDO;

/**
 * Base controller
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
abstract class ABaseController
{
    /** @var array Template data */
    public array $templateData = [];

    /** @var PDO Database instance */
    protected PDO $database;

    /** @var Engine Template */
    protected Engine $template;

    /** @var Request Request data object */
    protected Request $request;

    /**
     * Injects the database dependency
     *
     * @param PDO $database
     *
     * @return void
     */
    public function injectDatabase(PDO $database): void
    {
        $this->database = $database;
    }

    /**
     * Injects the template engine
     *
     * @param Engine $template
     *
     * @return void
     */
    public function injectTemplate(Engine $template): void
    {
        $this->template = $template;
    }

    /**
     * Injects the request data
     *
     * @param Request $request
     *
     * @return void
     */
    public function injectRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Runs before the render function
     * @return void
     */
    public function beforeRender(): void
    {
    }

    /**
     * Handles the request and returns the renderer
     *
     * @return void
     * @throws Exception
     */
    public abstract function render(): void;

    /**
     * Runs after the render function
     * @return void
     */
    public function afterRender(): void
    {
    }

    protected function sendErrorMessage(string $message): void
    {
        $_SESSION['errors'][] = $message;
    }

    protected function sendSuccessMessage(string $message): void
    {
        $_SESSION['successes'][] = $message;
    }
}