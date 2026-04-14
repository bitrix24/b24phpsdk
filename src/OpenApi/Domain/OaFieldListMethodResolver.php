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

namespace Bitrix24\SDK\OpenApi\Domain;

use InvalidArgumentException;

readonly class OaFieldListMethodResolver
{
    private const FIELD_LIST_SUFFIX = '.field.list';

    public function __construct(
        private OaSchemaMethodReader $oaSchemaMethodReader,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getEntityKeys(string $schemaFile): array
    {
        $entityKeys = [];
        foreach ($this->oaSchemaMethodReader->readMethodNames($schemaFile) as $methodName) {
            if (!str_ends_with($methodName, self::FIELD_LIST_SUFFIX)) {
                continue;
            }

            $entityKeys[] = substr($methodName, 0, -strlen(self::FIELD_LIST_SUFFIX));
        }

        sort($entityKeys);

        return array_values(array_unique($entityKeys));
    }

    public function resolveFieldListMethodName(string $schemaFile, string $entityKey): string
    {
        $normalizedEntityKey = trim($entityKey);
        if ($normalizedEntityKey === '') {
            throw new InvalidArgumentException('Entity key cannot be empty');
        }

        $methodName = $normalizedEntityKey . self::FIELD_LIST_SUFFIX;
        if (!in_array($methodName, $this->oaSchemaMethodReader->readMethodNames($schemaFile), true)) {
            throw new InvalidArgumentException(
                sprintf('Unknown v3 field metadata entity "%s"', $normalizedEntityKey)
            );
        }

        return $methodName;
    }
}
