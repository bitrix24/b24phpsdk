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
 * Class BoolFieldConditionBuilder
 *
 * Type-safe condition builder for boolean fields.
 * Automatically converts PHP bool values to Bitrix24's Y/N string format.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
class BoolFieldConditionBuilder
{
    public function __construct(
        private readonly string $fieldName,
        private readonly AbstractFilterBuilder $filter
    ) {
    }

    /**
     * Equals operator (=)
     *
     * @param bool $value
     * @return AbstractFilterBuilder
     */
    public function eq(bool $value): AbstractFilterBuilder
    {
        $boolStr = $value ? 'Y' : 'N';

        return $this->filter->addCondition($this->fieldName, '=', $boolStr);
    }

    /**
     * Not equal operator (!=)
     *
     * @param bool $value
     * @return AbstractFilterBuilder
     */
    public function neq(bool $value): AbstractFilterBuilder
    {
        $boolStr = $value ? 'Y' : 'N';

        return $this->filter->addCondition($this->fieldName, '!=', $boolStr);
    }
}
