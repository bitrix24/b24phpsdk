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

use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;

abstract class AbstractItemBuilder implements ItemBuilderInterface
{
    protected array $fields = [];

    public function build(): array
    {
        return $this->fields;
    }

    /**
     * Returns the list of field names supported by the concrete subclass.
     * Discovers public 1-parameter instance methods defined in the subclass only
     * (base class methods are excluded).
     *
     * @return string[]
     */
    public function getSupportedFieldNames(): array
    {
        $baseMethodNames = array_map(
            static fn(\ReflectionMethod $m): string => $m->getName(),
            (new \ReflectionClass(self::class))->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        $fieldNames = [];
        foreach ((new \ReflectionClass(static::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array($method->getName(), $baseMethodNames, true)) {
                continue;
            }

            if ($method->isStatic()) {
                continue;
            }

            if ($method->getNumberOfParameters() !== 1) {
                continue;
            }

            $fieldNames[] = $method->getName();
        }

        sort($fieldNames);

        return $fieldNames;
    }

    public function withUserField(string $userField, mixed $value): static
    {
        $this->fields[$userField] = $value;

        return $this;
    }
}