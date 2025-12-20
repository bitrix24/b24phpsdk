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

/**
 * Class IntFieldConditionBuilder
 *
 * Type-safe condition builder for integer fields.
 * Ensures compile-time type safety for numeric field filtering.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
class IntFieldConditionBuilder
{
    public function __construct(
        private readonly string $fieldName,
        private readonly AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function eq(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '=', $value);
    }

    /**
     * Not equal operator (!=)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function neq(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '!=', $value);
    }

    /**
     * Greater than operator (>)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function gt(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '>', $value);
    }

    /**
     * Greater than or equal operator (>=)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function gte(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '>=', $value);
    }

    /**
     * Less than operator (<)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function lt(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '<', $value);
    }

    /**
     * Less than or equal operator (<=)
     *
     * @param int $value
     * @return AbstractFilterBuilder
     */
    public function lte(int $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '<=', $value);
    }

    /**
     * In operator - value must be one of the given values
     *
     * @param array<int, int> $values
     * @return AbstractFilterBuilder
     */
    public function in(array $values): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, 'in', $values);
    }

    /**
     * Between operator - value must be in range (inclusive)
     *
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return AbstractFilterBuilder
     */
    public function between(int $min, int $max): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, 'between', [$min, $max]);
    }
}
