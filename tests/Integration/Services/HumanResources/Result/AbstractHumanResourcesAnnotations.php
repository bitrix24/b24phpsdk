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

namespace Bitrix24\SDK\Tests\Integration\Services\HumanResources\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\TestCase;

abstract class AbstractHumanResourcesAnnotations extends TestCase
{
    use CustomBitrix24Assertions;

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws BaseException
     */
    protected function callOrSkipIfHumanResourcesUnavailable(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (BaseException $exception) {
            if (str_contains($exception->getMessage(), 'ERROR_METHOD_NOT_FOUND')
                || str_contains($exception->getMessage(), 'Method not found')
                || str_contains($exception->getMessage(), 'insufficientscopeexception')
                || str_contains($exception->getMessage(), 'отсутствует необходимый scope')) {
                self::markTestSkipped('Portal does not support humanresources REST API methods.');
            }

            throw $exception;
        }
    }

    /**
     * @param class-string $resultItemClassName
     * @param array<string, array<string, mixed>> $fieldsDescription
     */
    protected function assertHumanResourcesFieldsAnnotated(array $fieldsDescription, string $resultItemClassName): void
    {
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fieldsDescription), $resultItemClassName);
    }

    /**
     * @param class-string $resultItemClassName
     * @param array<string, array<string, mixed>> $fieldsDescription
     */
    protected function assertHumanResourcesFieldsHaveValidTypeAnnotations(
        array $fieldsDescription,
        string $resultItemClassName
    ): void {
        $fieldTypes = [];
        foreach ($fieldsDescription as $fieldCode => $fieldData) {
            $fieldTypes[$fieldCode] = [
                'type' => $this->normalizeFieldType((string)$fieldCode, (string)($fieldData['type'] ?? 'string')),
            ];
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fieldTypes, $resultItemClassName);
    }

    /**
     * @param class-string $resultItemClassName
     */
    protected function assertFieldDescriptorItemAnnotations(array $rawItem, AbstractItem $item, string $resultItemClassName): void
    {
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), $resultItemClassName);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, $resultItemClassName);
    }

    private function normalizeFieldType(string $fieldCode, string $type): string
    {
        if ($type === 'boolean') {
            return 'char';
        }

        if (in_array($fieldCode, ['createdAt', 'updatedAt'], true)) {
            return 'datetime';
        }

        return $type;
    }
}
