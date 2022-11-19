<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Entities\Services;

use Exception;
use PDO;
use PDOException;
use Zsolt\Pr\Model\Entities\Product;

/**
 * Product service
 *
 * @package Zsolt\Pr\Model\Entities\Services
 * @author Zsolt Döme
 */
final class ProductService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * Fetch product by id
     *
     * @param int $id
     *
     * @return Product
     * @throws Exception
     */
    public function fetchById(int $id): Product
    {
        return new Product($this->database, $id);
    }

    /**
     * Fetch product by code
     *
     * @param string $code
     *
     * @return Product
     * @throws Exception
     */
    public function fetchByCode(string $code): Product
    {
        return new Product($this->database, code: $code);
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
            new Product($this->database, name: $name);
        }
        catch (PDOException)
        {
            return false;
        }

        return true;
    }

    /**
     * Fetch all products
     *
     * @return Product[]
     * @throws Exception
     */
    public function fetchAll(): array
    {
        $sql = 'SELECT ' . Product::ID . ' FROM ' . Product::ENTITY_NAME;
        $rows = $this->database->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach ($rows as $row)
        {
            $entities[] = $this->fetchById($row[Product::ID]);
        }

        return $entities;
    }
}