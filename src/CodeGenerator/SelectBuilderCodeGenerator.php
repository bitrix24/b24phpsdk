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

readonly class SelectBuilderCodeGenerator
{
    private string $templatePath;

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? __DIR__ . '/Templates/SelectBuilder.tpl.php';
    }

    /**
     * Generates a PHP source file for a *SelectBuilder class.
     *
     * Methods are emitted in alphabetical order for determinism.
     * The 'id' field is placed in the constructor, not as a method.
     * Dot-notation fields sharing a prefix are grouped into one array_merge method.
     *
     * @param list<string> $selectableFields flat list, may include dot-notation (e.g. 'chat.id')
     */
    public function generate(string $namespace, string $className, array $selectableFields, string $entityKey = ''): string
    {
        $groups = $this->groupByPrefix($selectableFields);

        ob_start();
        extract(['namespace' => $namespace, 'className' => $className, 'groups' => $groups, 'entityKey' => $entityKey]);
        include $this->templatePath;

        return (string) ob_get_clean();
    }

    /**
     * Groups flat field list by dot-prefix.
     * Fields without dots form their own single-element group.
     * Result is sorted by key (prefix) for determinism.
     *
     * @param list<string> $fields
     * @return array<string, list<string>>
     */
    private function groupByPrefix(array $fields): array
    {
        $groups = [];
        foreach ($fields as $field) {
            if ($field === 'id') {
                continue;
            }

            $prefix = strstr($field, '.', before_needle: true);
            if ($prefix === false) {
                $groups[$field][] = $field;
            } else {
                $groups[$prefix][] = $field;
            }
        }

        ksort($groups);

        return $groups;
    }
}
