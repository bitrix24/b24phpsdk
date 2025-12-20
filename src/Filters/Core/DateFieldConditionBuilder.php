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

use DateTime;

/**
 * Class DateFieldConditionBuilder
 *
 * Type-safe condition builder for date/datetime fields.
 * Accepts DateTime objects or string dates and automatically converts DateTime to string format.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
class DateFieldConditionBuilder
{
    public function __construct(
        private readonly string $fieldName,
        private readonly AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function eq(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '=', $dateStr);
    }

    /**
     * Not equal operator (!=)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function neq(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '!=', $dateStr);
    }

    /**
     * Greater than operator (>)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function gt(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '>', $dateStr);
    }

    /**
     * Greater than or equal operator (>=)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function gte(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '>=', $dateStr);
    }

    /**
     * Less than operator (<)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function lt(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '<', $dateStr);
    }

    /**
     * Less than or equal operator (<=)
     *
     * @param DateTime|string $value Date as DateTime object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function lte(DateTime|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTime ? $value->format('Y-m-d') : $value;

        return $this->filter->addCondition($this->fieldName, '<=', $dateStr);
    }

    /**
     * Between operator - value must be in range (inclusive)
     *
     * @param DateTime|string $from Start date (inclusive)
     * @param DateTime|string $to End date (inclusive)
     * @return AbstractFilterBuilder
     */
    public function between(DateTime|string $from, DateTime|string $to): AbstractFilterBuilder
    {
        $fromStr = $from instanceof DateTime ? $from->format('Y-m-d') : $from;
        $toStr = $to instanceof DateTime ? $to->format('Y-m-d') : $to;

        return $this->filter->addCondition($this->fieldName, 'between', [$fromStr, $toStr]);
    }
}
