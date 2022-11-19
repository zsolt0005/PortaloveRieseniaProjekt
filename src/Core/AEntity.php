<?php declare(strict_types = 1);

namespace Zsolt\Pr\Core;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 * Entity base
 *
 * @package Zsolt\Pr\Core
 * @author Zsolt Döme
 */
abstract class AEntity
{
    /** @var array<string, mixed> Fields modified with new values */
    private array $fieldsModified = [];

    /** @var bool Whether the entity is loaded or not */
    private bool $loaded = false;

    /**
     * Constructor
     *
     * @param PDO        $database
     * @param array<string, mixed>|null $loadBy
     *
     * @throws PDOException
     * @throws Exception
     */
    public function __construct(protected PDO $database, array $loadBy = null)
    {
        if($loadBy !== null)
        {
            $this->load($loadBy);
        }
    }

    /**
     * Maps the database columns to the entity properties
     *
     * @param array<string, string|null> $row
     *
     * @return void
     *
     * @throws Exception
     */
    protected abstract function mapping(array $row): void;

    /**
     * Get the entity table name
     *
     * @return string
     */
    abstract public static function getEntityName(): string;

    /**
     * Gets the required fields of the entity
     *
     * @return string[]
     */
    protected abstract static function getRequiredFields(): array;

    /**
     * Get the primary key field names
     *
     * @return string[]
     */
    protected abstract function getPrimaryKeyFieldValues(): array;

    /**
     * Adds the field that was modified
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return void
     */
    final protected function addModified(string $field, mixed $value): void
    {
        $this->fieldsModified[$field] = $value;
    }

    /**
     * Loads the entity
     *
     * @param array<string, mixed> $loadBy
     *
     * @return void
     * @throws PDOException
     * @throws Exception
     */
    final protected function load(array $loadBy): void
    {
        $entityName = static::getEntityName();

        // Get the where statements
        $whereStatements = [];
        foreach ($loadBy as $fieldName => $fieldValue)
        {
            if($fieldValue === null)
            {
                $whereStatements[] = $fieldName.' IS NULL';
            }
            else
            {
                $whereStatements[] = $fieldName . ' = ' . "'" . $fieldValue . "'";
            }
        }

        // Create the query
        $query =
            'SELECT * ' .
            'FROM ' . $entityName . ' ';

        for ($i = 0; $i < count($whereStatements); $i++)
        {
            $whereStatement = $whereStatements[$i];

            if($i === 0)
            {
                $query .= 'WHERE ';
            }
            else
            {
                $query .= 'AND ';
            }

            $query .= $whereStatement;
        }

        $row = $this->database
            ->query($query)
            ->fetch(PDO::FETCH_ASSOC);

        if($row === false)
        {
            throw new PDOException('At least one row was expected');
        }

        $this->mapping($row);
        $this->loaded = true;
    }

    protected function processLastInsertId(string $lastInsertId): void
    {
    }

    /**
     * Insert or Update
     *
     * @param bool $reload
     *
     * @return void
     * @throws Exception
     */
    public function save(bool $reload = false): void
    {
        if($this->loaded === false)
        {
            $this->insert();
        }
        else
        {
            $this->update();
        }

        $this->loaded = true;
        $this->fieldsModified = [];

        if($reload)
        {
            $this->load($this->getPrimaryKeyFieldValues());
        }
    }

    /**
     * Inserts entity to database
     *
     * @return void
     */
    private function insert(): void
    {
        $editedFields = array_keys($this->fieldsModified);
        $editedFieldValues = array_values($this->fieldsModified);
        $requiredFieldNames = static::getRequiredFields();

        if(count(array_intersect($requiredFieldNames, $editedFields)) !== count($requiredFieldNames))
        {
            throw new InvalidArgumentException('Not all required fields are set! '.
                'Required: '.implode(', ', $requiredFieldNames).'. '.
                'Missing: '.implode(', ', array_diff($requiredFieldNames, $editedFields)).'.');
        }

        $entityName = static::getEntityName();

        // Prepare values
        $editedFieldValues = array_map(function (mixed $value)
        {
            return "'{$value}'";
        }, $editedFieldValues);

        $query =
            'INSERT INTO ' . $entityName . '(' . implode(',', $editedFields) . ') ' .
            'VALUES(' . implode(',', $editedFieldValues) . ')';

        $this->database->query($query);

        $lastInsertId = $this->database->lastInsertId();
        $this->processLastInsertId($lastInsertId);
    }

    /**
     * Updates entity in the database
     *
     * @return void
     */
    private function update(): void
    {
        if(empty($this->fieldsModified))
        {
            return;
        }

        $primaryKeys = array_keys($this->getPrimaryKeyFieldValues());
        $editedFields = array_keys($this->fieldsModified);
        $entityName = static::getEntityName();

        if(count(array_intersect($primaryKeys, $editedFields)) === count($editedFields))
        {
            throw new InvalidArgumentException('Primary key fields can not be modified!');
        }

        // Prepare where statements
        $whereStatements = [];
        foreach ($this->getPrimaryKeyFieldValues() as $key => $value)
        {
            $whereStatements[] = "{$key} = '{$value}'";
        }

        // Prepare set statements
        $setStatements = [];
        foreach ($this->fieldsModified as $key => $value)
        {
            // If is an object, it is a DateTimeImmutable
            if(gettype($value) === "object")
            {
                /** @var DateTimeImmutable $value */
                $value = $value->format('Y-m-d H:i:s');
            }

            $setStatements[] = "{$key} = '{$value}'";
        }

        $query =
            'UPDATE ' . $entityName . ' ' .
            'SET ' . implode(',', $setStatements) . ' ' .
            'WHERE ' . implode('AND', $whereStatements);

        $this->database->query($query);
    }

    /**
     * Deletes entity from database
     *
     * @return void
     */
    public function delete(): void
    {
        $entityName = static::getEntityName();

        // Prepare where statements
        $whereStatements = [];
        foreach ($this->getPrimaryKeyFieldValues() as $key => $value)
        {
            $whereStatements[] = "{$key} = '{$value}'";
        }

        $query =
            'DELETE FROM ' . $entityName . ' ' .
            'WHERE ' . implode('AND ', $whereStatements);

        $this->database->query($query);
    }
}