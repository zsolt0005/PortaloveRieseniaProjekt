<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Entities\Services;

use Exception;
use PDO;
use PDOException;
use Zsolt\Pr\Model\Entities\ProductCategory;

/**
 * Entity service
 *
 * @package Zsolt\Pr\Model\Entities\Services
 * @author Zsolt Döme
 */
final class ProductCategoryService
{
    public function __construct(private readonly PDO $database)
    {
    }

    /**
     * Fetch category by id
     *
     * @param int $id
     *
     * @return ProductCategory
     * @throws Exception
     */
    public function fetchById(int $id): ProductCategory
    {
        return new ProductCategory($this->database, $id);
    }

    /**
     * Fetch category by code
     *
     * @param string $code
     *
     * @return ProductCategory
     *
     * @throws PDOException
     * @throws Exception
     */
    public function fetchByCode(string $code): ProductCategory
    {
        return new ProductCategory($this->database, code: $code);
    }

    /**
     * Check if the category exists or not
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function isExistsByName(string $name): bool
    {
        try
        {
            new ProductCategory($this->database, name: $name);
        }
        catch (PDOException)
        {
            return false;
        }

        return true;
    }

    /**
     * Fetch all categories
     *
     * @return ProductCategory[]
     * @throws Exception
     */
    public function fetchAll(): array
    {
        $sql = 'SELECT ' . ProductCategory::CODE . ' FROM ' . ProductCategory::ENTITY_NAME;
        $rows = $this->database->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach ($rows as $row)
        {
            $entities[] = $this->fetchByCode($row[ProductCategory::CODE]);
        }

        return $entities;
    }
}