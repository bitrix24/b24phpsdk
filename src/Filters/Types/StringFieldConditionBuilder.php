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

/**
 * Class StringFieldConditionBuilder
 *
 * Type-safe condition builder for string fields.
 * Ensures compile-time type safety for text field filtering.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
readonly class StringFieldConditionBuilder
{
    public function __construct(
        private string $fieldName,
        private AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param string $value
     * @return AbstractFilterBuilder
     */
    public function eq(string $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '=', $value);
    }

    /**
     * Not equal operator (!=)
     *
     * @param string $value
     * @return AbstractFilterBuilder
     */
    public function neq(string $value): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, '!=', $value);
    }

    /**
     * In operator - value must be one of the given values
     *
     * @param array<int, string> $values
     * @return AbstractFilterBuilder
     */
    public function in(array $values): AbstractFilterBuilder
    {
        return $this->filter->addCondition($this->fieldName, 'in', $values);
    }
}
