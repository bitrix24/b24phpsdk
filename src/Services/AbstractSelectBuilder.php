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

namespace Bitrix24\SDK\Services;

use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;

abstract class AbstractSelectBuilder implements SelectBuilderInterface
{
    protected array $select = [];

    public function buildSelect(): array
    {
        return array_unique($this->select);
    }

    public function withUserFields(array $userFields): self
    {
        $this->select = array_merge($this->select, $userFields);
        return $this;
    }

    /**
     * Selects all system fields defined in the concrete builder class.
     *
     * Uses reflection to discover all public zero-parameter methods declared in the
     * concrete subclass (not inherited from AbstractSelectBuilder) and calls each one.
     * This means any new field method added to a descendant is automatically included
     * without touching this base class.
     *
     * Deduplication is handled by buildSelect() via array_unique().
     */
    public function allSystemFields(): static
    {
        $baseMethodNames = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            (new \ReflectionClass(self::class))->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        foreach ((new \ReflectionClass(static::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->getName(), $baseMethodNames, true)) {
                continue;
            }

            if ($method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $this->{$method->getName()}();
        }

        return $this;
    }
}