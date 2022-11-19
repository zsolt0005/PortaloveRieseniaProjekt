<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Entities\Services;

use Exception;
use PDO;
use Zsolt\Pr\Model\Entities\ProductToCategory;

/**
 * Product to category service
 *
 * @package Zsolt\Pr\Model\Entities\Services
 * @author Zsolt Döme
 */
final class ProductToCategoryService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * Fetch category by id
     *
     * @param int $productId
     * @param int $categoryId
     *
     * @return ProductToCategory
     * @throws Exception
     */
    public function fetchByProductIdAndCategoryId(int $productId, int $categoryId): ProductToCategory
    {
        return new ProductToCategory($this->database, $productId, $categoryId);
    }

    /**
     * @param int $productDd
     *
     * @return array<ProductToCategory>
     * @throws Exception
     */
    public function fetchByProductId(int $productDd): array
    {
        $sql = 'SELECT * FROM ' . ProductToCategory::ENTITY_NAME . ' WHERE ' . ProductToCategory::PRODUCT_ID . ' = ' . $productDd;
        $rows = $this->database->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach ($rows as $row)
        {
            $entities[] = $this->fetchByProductIdAndCategoryId(
                (int) $row[ProductToCategory::PRODUCT_ID],
                (int) $row[ProductToCategory::CATEGORY_ID],
            );
        }

        return $entities;
    }
}