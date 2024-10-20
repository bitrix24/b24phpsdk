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

namespace Bitrix24\SDK\Core\Fields;

class FieldsFilter
{
    /**
     * @param array<int, non-empty-string> $fieldCodes
     * @return array<int, non-empty-string>
     */
    public function filterSystemFields(array $fieldCodes): array
    {
        $res = [];
        foreach ($fieldCodes as $fieldCode) {
            if (!str_starts_with($fieldCode, 'UF_CRM_') && !str_starts_with($fieldCode, 'PARENT_ID_')) {
                $res[] = $fieldCode;
            }
        }

        return $res;
    }

    /**
     * @param array<int, non-empty-string> $fieldCodes
     * @return array<int, non-empty-string>
     */
    public function filterUserFields(array $fieldCodes): array
    {
        $res = [];
        foreach ($fieldCodes as $fieldCode) {
            if (str_starts_with($fieldCode, 'UF_CRM_')) {
                $res[] = $fieldCode;
            }
        }

        return $res;
    }

    /**
     * @param array<int, non-empty-string> $fieldCodes
     * @return array<int, non-empty-string>
     */
    public function filterSmartProcessFields(array $fieldCodes): array
    {
        $res = [];
        foreach ($fieldCodes as $fieldCode) {
            if (str_starts_with($fieldCode, 'PARENT_ID_')) {
                $res[] = $fieldCode;
            }
        }

        return $res;
    }
}
