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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Checks typed nested items against the raw REST v3 payload they were built from.
 * Use together with CustomBitrix24Assertions.
 */
trait NestedItemAnnotationsTrait
{
    /**
     * Compares annotations with the union of keys of the raw items and checks casting of every item.
     *
     * @param array<int, array<string, mixed>> $rawItems
     * @param array<int, mixed>                $items
     * @param class-string<AbstractItem>       $class
     */
    private function assertNestedItems(array $rawItems, array $items, string $class): void
    {
        self::assertNotEmpty($rawItems, sprintf('no live data to check «%s»', $class));
        $keys = [];
        foreach ($rawItems as $index => $rawItem) {
            $keys = [...$keys, ...array_keys($rawItem)];
            self::assertInstanceOf($class, $items[$index]);
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($items[$index], $class);
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_values(array_unique($keys)), $class);
    }

    /**
     * Replaces live `object` metadata types: such fields must be typed items or arrays of them.
     *
     * @param array<string, array<string, mixed>> $fields
     * @param class-string                        $class
     * @return array<string, array<string, mixed>>
     */
    private function withoutNestedObjectFields(array $fields, string $class): array
    {
        $annotations = [];
        foreach (\Typhoon\Reflection\TyphoonReflector::build()->reflectClass($class)->properties() as $property) {
            if ($property->isAnnotated() && !$property->isNative()) {
                $annotations[$property->id->name] = \Typhoon\Type\stringify($property->type());
            }
        }

        foreach ($fields as $name => $field) {
            if ($field['type'] === 'object') {
                self::assertMatchesRegularExpression('/ItemResult|array/', $annotations[$name], sprintf('nested field «%s»', $name));
                unset($fields[$name]);
            }
        }

        return $fields;
    }
}
