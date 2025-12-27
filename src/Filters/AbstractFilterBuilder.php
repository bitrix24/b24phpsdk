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

namespace Bitrix24\SDK\Filters;

/**
 * Class AbstractFilterBuilder
 *
 * Base implementation for all filter builders with support for AND/OR logic.
 *
 * @package Bitrix24\SDK\Filters\Core
 */
abstract class AbstractFilterBuilder implements FilterBuilderInterface
{
    /**
     * AND conditions - all must be satisfied
     *
     * @var array<int, array>
     */
    protected array $conditions = [];

    /**
     * OR groups - at least one condition in each group must be satisfied
     *
     * @var array<int, array<int, array>>
     */
    protected array $orGroups = [];

    /**
     * Convert filter to REST 3.0 array format
     *
     * @return array<int, array|array<string, mixed>>
     */
    public function toArray(): array
    {
        $result = $this->conditions;

        // Add OR groups
        foreach ($this->orGroups as $orGroup) {
            $result[] = [
                'logic' => 'or',
                'conditions' => $orGroup,
            ];
        }

        return $result;
    }

    /**
     * Add raw filter conditions (fallback for edge cases)
     *
     * @param array<int, array> $conditions Raw filter conditions in REST 3.0 format
     * @return static
     */
    public function setRaw(array $conditions): static
    {
        foreach ($conditions as $condition) {
            $this->conditions[] = $condition;
        }

        return $this;
    }

    /**
     * Add OR logic group with callback
     *
     * @param callable(static): void $callback Callback that receives fresh filter instance for OR conditions
     * @return static
     */
    public function or(callable $callback): static
    {
        // Create fresh filter instance for OR group
        /** @phpstan-var static $orFilter */
        $orFilter = new static(); // @phpstan-ignore-line new.static

        // Execute callback to populate conditions
        $callback($orFilter);

        // Add conditions to OR groups
        if (!empty($orFilter->conditions)) {
            $this->orGroups[] = $orFilter->conditions;
        }

        return $this;
    }

    /**
     * Add a condition to the filter
     *
     * @param string $field Field name
     * @param string $operator Operator (=, !=, >, >=, <, <=, in, between)
     * @param mixed $value Field value
     * @return static
     */
    public function addCondition(string $field, string $operator, mixed $value): static
    {
        $this->conditions[] = [$field, $operator, $value];

        return $this;
    }
}
