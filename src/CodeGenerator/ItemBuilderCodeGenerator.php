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

namespace Bitrix24\SDK\CodeGenerator;

readonly class ItemBuilderCodeGenerator
{
    /** @var array<string, string> OpenAPI type → PHP type */
    private const array TYPE_MAP = [
        'string'  => 'string',
        'integer' => 'int',
        'boolean' => 'bool',
        'array'   => 'array',
    ];

    private string $templatePath;

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? __DIR__ . '/Templates/ItemBuilder.tpl.php';
    }

    /**
     * Generates a PHP source file for a *ItemBuilder class.
     *
     * Methods are emitted in alphabetical order for determinism.
     * Entries with unknown OpenAPI types (e.g. 'object') are silently skipped.
     *
     * @param array<string, string> $writableFields fieldName → openApiType
     */
    public function generate(string $namespace, string $className, array $writableFields, string $operationPath = ''): string
    {
        $phpTypedFields = $this->mapToPhpTypes($writableFields);

        ob_start();
        extract([
            'namespace'     => $namespace,
            'className'     => $className,
            'phpTypedFields' => $phpTypedFields,
            'operationPath' => $operationPath,
        ]);
        include $this->templatePath;

        return (string) ob_get_clean();
    }

    /**
     * Maps OpenAPI field types to PHP types, skipping unknown/unsupported types.
     * Result is sorted alphabetically by field name.
     *
     * @param array<string, string> $writableFields fieldName → openApiType
     * @return array<string, string> fieldName → phpType
     */
    private function mapToPhpTypes(array $writableFields): array
    {
        $result = [];
        foreach ($writableFields as $fieldName => $openApiType) {
            if (!array_key_exists($openApiType, self::TYPE_MAP)) {
                continue;
            }

            $result[$fieldName] = self::TYPE_MAP[$openApiType];
        }

        ksort($result);

        return $result;
    }
}
