<?php declare(strict_types=1);

namespace Zsolt\Pr\Model\Entities;

use Exception;
use InvalidArgumentException;
use PDO;
use Zsolt\Pr\Core\AEntity;
use Zsolt\Pr\Utils\TypeUtils;

/**
 * Product to category entity
 *
 * @package Zsolt\Pr\Model\Entities
 * @author Zsolt Döme
 */
final class ProductToCategory extends AEntity
{
    public const ENTITY_NAME = 'products_to_categories';
    public const PRODUCT_ID = 'product_id';
    public const CATEGORY_ID = 'category_id';

    private int $productId;
    private ?Product $product = null;
    private int $categoryId;
    private ?ProductCategory $category = null;

    /**
     * Constructor
     *
     * @param PDO      $database
     * @param int|null $productId
     * @param int|null $categoryId
     *
     * @throws Exception
     */
    public function __construct(PDO $database, ?int $productId = null, ?int $categoryId = null)
    {
        if($productId === null && $categoryId === null)
        {
            parent::__construct($database);
        }
        else if($productId !== null && $categoryId !== null)
        {
            parent::__construct($database, [
                self::PRODUCT_ID => $productId,
                self::CATEGORY_ID => $categoryId
            ]);
        }
        else
        {
            throw new InvalidArgumentException("Invalid parameter combination");
        }
    }

    /** @inheritDoc */
    protected function mapping(array $row): void
    {
        $this->productId =  TypeUtils::strictConvertToInt($row[self::PRODUCT_ID]);
        $this->categoryId = TypeUtils::strictConvertToInt($row[self::CATEGORY_ID]);
    }

    /** @inheritDoc */
    public static function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /** @inheritDoc */
    protected static function getRequiredFields(): array
    {
        return [self::PRODUCT_ID, self::CATEGORY_ID];
    }

    /** @inheritDoc */
    protected function getPrimaryKeyFieldValues(): array
    {
        return [
            self::PRODUCT_ID => $this->productId,
            self::CATEGORY_ID => $this->categoryId
        ];
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return Product
     * @throws Exception
     */
    public function getProduct(): Product
    {
        if($this->product === null)
        {
            $this->product = new Product($this->database, $this->productId);
        }

        return $this->product;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @return ProductCategory
     * @throws Exception
     */
    public function getCategory(): ProductCategory
    {
        if($this->category === null)
        {
            $this->category = new ProductCategory($this->database, $this->categoryId);
        }

        return $this->category;
    }

    public function setProductId(int $productId): ProductToCategory
    {
        if(!isset($this->productId) || $this->productId !== $productId)
        {
            $this->productId = $productId;
            $this->addModified(self::PRODUCT_ID, $productId);
        }

        return $this;
    }

    public function setCategoryId(int $categoryId): ProductToCategory
    {
        if(!isset($this->categoryId) || $this->categoryId !== $categoryId)
        {
            $this->categoryId = $categoryId;
            $this->addModified(self::CATEGORY_ID, $categoryId);
        }

        return $this;
    }
}