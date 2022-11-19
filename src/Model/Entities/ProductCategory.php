<?php declare(strict_types = 1);

namespace Zsolt\Pr\Model\Entities;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use Zsolt\Pr\Core\AEntity;
use Zsolt\Pr\Utils\TypeUtils;

/**
 * Product category entity
 *
 * @package Zsolt\Pr\Model\Entities
 * @author Zsolt Döme
 */
final class ProductCategory extends AEntity
{
    public const ENTITY_NAME = 'product_categories';
    public const ID = 'id';
    public const CODE = 'code';
    public const NAME = 'name';
    public const DATE_CREATED = 'date_created';
    public const DATE_UPDATED = 'date_updated';

    private int $id;
    private string $code;
    private string $name;
    private DateTimeImmutable $dateCreated;
    private DateTimeImmutable $dateUpdated;

    /**
     * Constructor
     *
     * @param PDO         $database
     * @param int|null    $id
     * @param string|null $code
     * @param string|null $name
     *
     * @throws PDOException
     * @throws Exception
     */
    public function __construct(
        PDO $database,
        ?int $id = null,
        ?string $code = null,
        ?string $name = null
    )
    {
        if($id === null && $code === null && $name === null)
        {
            parent::__construct($database);
        }
        else if($id !== null)
        {
            parent::__construct($database, [self::ID => $id]);
        }
        else if($code !== null)
        {
            parent::__construct($database, [self::CODE => $code]);
        }
        else if($name !== null)
        {
            parent::__construct($database, [self::NAME => $name]);
        }
        else
        {
            throw new InvalidArgumentException("Invalid parameter combination");
        }
    }

    /** @inheritDoc */
    protected function mapping(array $row): void
    {
        $this->id =               TypeUtils::strictConvertToInt($row[self::ID]);
        $this->code =                                           $row[self::CODE];
        $this->name =                                           $row[self::NAME];
        $this->dateCreated = TypeUtils::strictConvertToDateTime($row[self::DATE_CREATED]);
        $this->dateUpdated = TypeUtils::strictConvertToDateTime($row[self::DATE_UPDATED]);
    }

    /** @inheritDoc */
    public static function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /** @inheritDoc */
    protected static function getRequiredFields(): array
    {
        return [self::NAME];
    }

    /** @inheritDoc */
    protected function getPrimaryKeyFieldValues(): array
    {
        return [self::ID => $this->id];
    }

    protected function processLastInsertId(string $lastInsertId): void
    {
        $this->id = TypeUtils::strictConvertToInt($lastInsertId);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDateCreated(): DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getDateUpdated(): DateTimeImmutable
    {
        return $this->dateUpdated;
    }

    public function setName(string $name): self
    {
        if(!isset($this->name) || $this->name !== $name)
        {
            $this->name = $name;
            $this->addModified(self::NAME, $name);
        }

        return $this;
    }
}