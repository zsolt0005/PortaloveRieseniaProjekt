<?php declare(strict_types=1);

namespace Zsolt\Pr\Controllers;

use DateTimeImmutable;
use Zsolt\Pr\Core\ABaseController;
use Zsolt\Pr\Facades\DefaultFacade;
use Zsolt\Pr\Model\Entities\ProductCategory;

/**
 * Default presenter for index
 *
 * @author Zsolt Döme
 */
final class DefaultController extends ABaseController
{
    /** @var string Controller path */
    public const NAME = "dashboard";

    /** @var DefaultFacade Facade */
    private DefaultFacade $facade;

    /** Constructor */
    public function __construct()
    {
        // Just a new instance, we don't have a DI container
        $this->facade = new DefaultFacade();
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