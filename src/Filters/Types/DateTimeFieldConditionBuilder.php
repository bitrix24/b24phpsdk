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

namespace Bitrix24\SDK\Filters\Types;

use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use DateTimeInterface;

/**
 * Class DateFieldConditionBuilder
 *
 * Type-safe condition builder for date/datetime fields.
 * Accepts DateTimeInterface objects or string dates and automatically converts DateTimeInterface to string format.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
readonly class DateTimeFieldConditionBuilder
{
    public function __construct(
        private string $fieldName,
        private AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function eq(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '=', $dateStr);
    }

    /**
     * Not equal operator (!=)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function neq(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '!=', $dateStr);
    }

    /**
     * Greater than operator (>)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function gt(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '>', $dateStr);
    }

    /**
     * Greater than or equal operator (>=)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function gte(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '>=', $dateStr);
    }

    /**
     * Less than operator (<)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function lt(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '<', $dateStr);
    }

    /**
     * Less than or equal operator (<=)
     *
     * @param DateTimeInterface|string $value Date as DateTimeInterface object or string (Y-m-d format)
     * @return AbstractFilterBuilder
     */
    public function lte(DateTimeInterface|string $value): AbstractFilterBuilder
    {
        $dateStr = $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : $value;

        return $this->filter->addCondition($this->fieldName, '<=', $dateStr);
    }

    /**
     * Between operator - value must be in range (inclusive)
     *
     * @param DateTimeInterface|string $from Start date (inclusive)
     * @param DateTimeInterface|string $to End date (inclusive)
     * @return AbstractFilterBuilder
     */
    public function between(DateTimeInterface|string $from, DateTimeInterface|string $to): AbstractFilterBuilder
    {
        $fromStr = $from instanceof DateTimeInterface ? $from->format(DATE_ATOM) : $from;
        $toStr = $to instanceof DateTimeInterface ? $to->format(DATE_ATOM) : $to;

        return $this->filter->addCondition($this->fieldName, 'between', [$fromStr, $toStr]);
    }
}
