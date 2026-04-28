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

namespace Bitrix24\SDK\Core\Result;

use Carbon\CarbonImmutable;
use Typhoon\Reflection\TyphoonReflector;

use function Typhoon\Type\stringify;

abstract class AbstractAnnotatedItem extends AbstractItem
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private static array $annotatedTypesCache = [];

    /**
     * @param int|string $offset
     *
     * @return mixed
     */
    #[\Override]
    public function __get($offset)
    {
        $value = parent::__get($offset);
        if (!is_string($offset) || $value === null) {
            return $value;
        }

        return $this->castValue($value, $this->annotatedTypes()[static::class][$offset] ?? null);
    }

    /**
     * @return array<class-string, array<string, string>>
     */
    private function annotatedTypes(): array
    {
        $className = static::class;
        if (isset(self::$annotatedTypesCache[$className])) {
            return self::$annotatedTypesCache;
        }

        self::$annotatedTypesCache[$className] = [];
        foreach (TyphoonReflector::build()->reflectClass($className)->properties() as $collection) {
            if (!$collection->isAnnotated()) {
                continue;
            }

            if ($collection->isNative()) {
                continue;
            }

            self::$annotatedTypesCache[$className][$collection->id->name] = stringify($collection->type());
        }

        return self::$annotatedTypesCache;
    }

    private function castValue(mixed $value, ?string $type): mixed
    {
        if ($type === null) {
            return $value;
        }

        if (str_contains($type, CarbonImmutable::class)) {
            if ($value instanceof CarbonImmutable) {
                return $value;
            }

            if ($value === '') {
                return null;
            }

            return CarbonImmutable::parse($value);
        }

        if (str_contains($type, 'array')) {
            return $this->castArrayValue($value, $type);
        }

        if (str_contains($type, 'bool')) {
            return match ($value) {
                true, 'Y', 'y', '1', 1 => true,
                false, 'N', 'n', '0', 0, '' => false,
                default => (bool)$value,
            };
        }

        if (str_contains($type, 'int')) {
            return $value === '' ? null : (int)$value;
        }

        if (str_contains($type, 'float')) {
            return $value === '' ? null : (float)$value;
        }

        if (str_contains($type, 'string')) {
            return (string)$value;
        }

        return $value;
    }

    private function castArrayValue(mixed $value, string $type): array
    {
        $items = (array)$value;
        if (preg_match('/array<(?<itemClass>[^,>]+)>/', $type, $matches) !== 1) {
            return $items;
        }

        $itemClass = $matches['itemClass'];
        if (!class_exists($itemClass) || !is_a($itemClass, AbstractItem::class, true)) {
            return $items;
        }

        return array_map(
            static fn(mixed $item): mixed => is_array($item) ? new $itemClass($item) : $item,
            $items
        );
    }
}
