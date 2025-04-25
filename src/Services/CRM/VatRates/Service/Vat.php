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

namespace Bitrix24\SDK\Services\CRM\VatRates\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Userfield\Result\UserfieldTypesResult;
use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\CRM\VatRates\Result\VatRateResult;
use Bitrix24\SDK\Services\CRM\VatRates\Result\VatRatesResult;
use DateTime;
use MoneyPHP\Percentage\Percentage;

#[ApiServiceMetadata(new Scope(['crm']))]
class Vat extends AbstractService
{
    #[ApiEndpointMetadata(
        'crm.vat.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-fields.html',
        'Get VAT Rate Fields crm.vat.fields'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.vat.fields'));
    }

    #[ApiEndpointMetadata(
        'crm.vat.add',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-add.html',
        'Add VAT Rate crm.vat.add'
    )]
    public function add(string $name, Percentage $percentage, int $sort = 100, bool $isActive = true): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('crm.vat.add', [
            'fields' => [
                'TIMESTAMP_X' => (new DateTime())->format(DATE_ATOM),
                'ACTIVE' => $isActive ? 'Y' : 'N',
                'C_SORT' => $sort,
                'NAME' => $name,
                'RATE' => (string)$percentage
            ]
        ])
        );
    }

    /**
     * @throws TransportException
     * @throws InvalidArgumentException
     * @throws BaseException
     */
    #[ApiEndpointMetadata(
        'crm.vat.update',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-update.html',
        'Update Existing VAT Rate'
    )]
    public function update(
        int $vatId,
        ?string $name = null,
        ?Percentage $percentage = null,
        ?int $sort = null,
        ?bool $isActive = null
    ): UpdatedItemResult {
        $data = [];
        if ($name !== null) {
            $data['NAME'] = $name;
        }

        if ($percentage instanceof \MoneyPHP\Percentage\Percentage) {
            $data['RATE'] = (string)$percentage;
        }

        if ($sort !== null) {
            $data['SORT'] = $sort;
        }

        if ($isActive !== null) {
            $data['ACTIVE'] = $isActive ? 'Y' : 'N';
        }

        if ($data === []) {
            throw new InvalidArgumentException('you must set minimum one argument to update');
        }

        return new UpdatedItemResult($this->core->call(
            'crm.vat.update',
            [
                'id' => $vatId,
                'fields' => $data
            ]
        ));
    }

    #[ApiEndpointMetadata(
        'crm.vat.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-delete.html',
        'Delete VAT Rate'
    )]
    public function delete(int $vatId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('crm.vat.delete', [
            'ID' => $vatId
        ]));
    }

    #[ApiEndpointMetadata(
        'crm.vat.get',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-get.html',
        'Get VAT Rate by ID'
    )]
    public function get(int $vatId): VatRateResult
    {
        return new VatRateResult($this->core->call('crm.vat.get', [
            'ID' => $vatId
        ]));
    }

    #[ApiEndpointMetadata(
        'crm.vat.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/auxiliary/vat/crm-vat-list.html',
        'Get a list of VAT rates by filter crm.vat.list'
    )]
    public function list(
        array $order,
        array $filter,
        ?array $select = ['ID', 'TIMESTAMP_X', 'ACTIVE', 'C_SORT', 'NAME', 'RATE']
    ): VatRatesResult {
        return new VatRatesResult($this->core->call('crm.vat.list', [
            'order' => $order,
            'filter' => $filter,
            'select' => $select
        ]));
    }
}
