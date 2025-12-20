<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Filters\Core;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

/**
 * Class FieldConditionBuilder
 *
 * Provides type-safe operator methods for building filter conditions.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
class FieldConditionBuilder
{
    public function __construct(
        private readonly string $fieldName,
        private readonly AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function eq(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '=', $value);
    }

    /**
     * Not equal operator (!=)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function neq(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '!=', $value);
    }

    /**
     * Greater than operator (>)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function gt(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '>', $value);
    }

    /**
     * Greater than or equal operator (>=)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function gte(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '>=', $value);
    }

    /**
     * Less than operator (<)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function lt(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '<', $value);
    }

    /**
     * Less than or equal operator (<=)
     *
     * @param mixed $value
     * @return AbstractFilterBuilder
     */
    public function lte(mixed $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '<=', $value);
    }

    /**
     * In operator - value must be one of the given values
     *
     * @param array<int, mixed> $values
     * @return AbstractFilterBuilder
     */
    public function in(array $values): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, 'in', $values);
    }

    /**
     * Between operator - value must be in range (inclusive)
     *
     * @param array{0: mixed, 1: mixed} $range Array with exactly 2 elements [min, max]
     * @return AbstractFilterBuilder
     * @throws InvalidArgumentException If range doesn't contain exactly 2 values
     */
    public function between(array $range): AbstractFilterBuilder
    {
        if (count($range) !== 2) {
            throw new InvalidArgumentException(
                sprintf(
                    'between() requires exactly 2 values [min, max], got %d values: %s',
                    count($range),
                    json_encode($range)
                )
            );
        }

        return $this->filter->addCondition($this->fieldName, 'between', $range);
    }
}
