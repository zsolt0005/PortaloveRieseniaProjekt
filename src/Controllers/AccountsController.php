<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Facades\AccountsFacade;

/**
 * Account controller
 *
 * @package Zsolt\Pr\Controllers
 * @author Zsolt Döme
 */
final class AccountsController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "accounts";

    /** @var AccountsFacade Facade */
    private AccountsFacade $facade;

    /** Constructor */
    public function __construct()
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new AccountsFacade();
    }

    /** @inheritDoc */
    public function render(): void
    {
        $this->templateData = [
            'data' => [],
            'navigation' => $this->facade->prepareNavigationData(self::NAME)
        ];
    }
}