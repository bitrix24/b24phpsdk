<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Builders\Services\Catalog\UserfieldDocument;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;

/**
 * Discovers an existing catalog-module userfield attached to warehouse accounting documents of type
 * «A» (goods receipt), or creates one if none exists yet on the test portal.
 *
 * Warehouse accounting document userfields use `entityId` in the form
 * `CAT_STORE_DOCUMENT_<documentType>` (e.g. `CAT_STORE_DOCUMENT_A`), and `fieldName` must be prefixed
 * with `UF_<entityId>_`.
 */
final class CatalogDocumentUserfieldFixture
{
    public const string DOCUMENT_TYPE = 'A';

    public const string ENTITY_ID = 'CAT_STORE_DOCUMENT_' . self::DOCUMENT_TYPE;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public static function getOrCreateFieldCode(CoreInterface $core): string
    {
        $existingFields = $core->call('userfieldconfig.list', [
            'moduleId' => 'catalog',
            'filter' => ['entityId' => self::ENTITY_ID],
        ])->getResponseData()->getResult()['fields'] ?? [];

        if ($existingFields !== []) {
            return sprintf('field%s', $existingFields[0]['id']);
        }

        $addedField = $core->call('userfieldconfig.add', [
            'moduleId' => 'catalog',
            'field' => [
                'entityId' => self::ENTITY_ID,
                'fieldName' => sprintf('UF_%s_TEST_%s', self::ENTITY_ID, time()),
                'userTypeId' => 'string',
                'editFormLabel' => ['en' => 'b24-php-sdk integration test field'],
            ],
        ])->getResponseData()->getResult()['field'];

        return sprintf('field%s', $addedField['id']);
    }
}
