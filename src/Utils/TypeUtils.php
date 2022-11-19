<?php declare(strict_types=1);

namespace Zsolt\Pr\Utils;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Type conversion utils
 *
 * @author Zsolt Döme
 */
final class TypeUtils
{
    /** Private constructor to prevent instantiation */
    private function __construct()
    {
    }

    /**
     * Converts input to ImmutableDateTime
     *
     * @param mixed $dateTime
     *
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    public static function strictConvertToDateTime(mixed $dateTime): ?DateTimeImmutable
    {
        if($dateTime === null)
        {
            throw new InvalidArgumentException("dateTime parameter cannot be null");
        }

        return new DateTimeImmutable($dateTime);
    }

    /**
     * Converts input to int
     *
     * @param mixed $input
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function strictConvertToInt(mixed $input): int
    {
        if($input === null)
        {
            throw new InvalidArgumentException("input cannot be null");
        }

        if(is_int($input))
        {
            return $input;
        }

        if(is_float($input))
        {
            $intInput = (int) $input;
            if($input - $intInput !== 0.0)
            {
                throw new InvalidArgumentException('Float input has a decimal value');
            }
            return $intInput;
        }

        if(is_bool($input))
        {
            return $input ? 1 : 0;
        }

        if(is_string($input))
        {
            $intInput = (int) $input;
            if($input !== (string) $intInput)
            {
                throw new InvalidArgumentException('Input ' . $input . ' is not a valid integer');
            }
            return $intInput;
        }

        throw new InvalidArgumentException('Unsupported type');
    }
}