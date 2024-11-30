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

namespace Bitrix24\SDK\Tests\Builders\Services\CRM\Userfield;

use Random\RandomException;

class SystemUserfieldBuilder
{
    private string $name;
    private string $xmlId;
    private string $userTypeId;

    /**
     * @throws RandomException
     */
    public function __construct(string $userTypeId = 'string')
    {
        $this->name = sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time());
        $this->userTypeId = $userTypeId;
        $this->xmlId = sprintf('b24phpsdk_type_%s', $this->userTypeId);
    }

    public function build(): array
    {
        return [
            'FIELD_NAME' => $this->name,
            'EDIT_FORM_LABEL' => [
                'en' => 'test uf type string',
            ],
            'LIST_COLUMN_LABEL' => [
                'en' => 'test uf type string',
            ],
            'USER_TYPE_ID' => $this->userTypeId,
            'XML_ID' => $this->xmlId,
            'SETTINGS' => [],
        ];
    }
}