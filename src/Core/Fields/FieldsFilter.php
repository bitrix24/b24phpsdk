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
    private const CRM_USER_FIELD_PREFIX = 'UF_CRM_';

    private const SMART_PROCESS_FIELD_PREFIX = 'PARENT_ID_';

    private const PRODUCT_USER_FIELD_PREFIX = 'PROPERTY_';

    /**
     * @param array<int, non-empty-string> $fieldCodes
     * @return array<int, non-empty-string>
     */
    public function filterSystemFields(array $fieldCodes): array
    {
        $res = [];
        foreach ($fieldCodes as $fieldCode) {
            if (!str_starts_with($fieldCode, self::CRM_USER_FIELD_PREFIX) &&
                !str_starts_with($fieldCode, self::SMART_PROCESS_FIELD_PREFIX) &&
                !str_starts_with($fieldCode, self::PRODUCT_USER_FIELD_PREFIX)) {
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
            if (str_starts_with($fieldCode, self::CRM_USER_FIELD_PREFIX)) {
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
            if (str_starts_with($fieldCode, self::SMART_PROCESS_FIELD_PREFIX)) {
                $res[] = $fieldCode;
            }
        }

        return $res;
    }
}
