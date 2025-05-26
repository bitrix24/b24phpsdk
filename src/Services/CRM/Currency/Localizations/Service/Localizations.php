<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Currency\Localizations\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\Currency\Localizations\Result\LocalizationsResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['crm']))]
class Localizations extends AbstractService
{
    /**
     * Currency constructor.
     */
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * add/update new or existing localization
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-set.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.localizations.set',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-set.html',
        'Method adds new currency'
    )]
    public function set(string $id, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call(
                'crm.currency.localizations.set',
                [
                    'id' => $id,
                    'localizations' => $fields,
                ]
            )
        );
    }

    /**
     * Deletes the specified localizations by the currency ID
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-delete.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.localizations.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-delete.html',
        'Deletes the specified localizations'
    )]
    public function delete(string $id, array $lids): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call(
                'crm.currency.localizations.delete',
                [
                    'id' => $id,
                    'lids' => $lids
                ]
            )
        );
    }

    /**
     * Returns the description of the localization fields.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.localizations.fields',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-fields.html',
        'Returns the description of the currency fields.'
    )]
    public function fields(): FieldsResult
    {
        return new FieldsResult($this->core->call('crm.currency.localizations.fields'));
    }

    /**
     * Returns localizations by the currency ID.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-get.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.currency.get',
        'https://apidocs.bitrix24.com/api-reference/crm/currency/localizations/crm-currency-localizations-get.html',
        'Returns localizations by the currency ID.'
    )]
    public function get(string $id): LocalizationsResult
    {
        return new LocalizationsResult($this->core->call('crm.currency.localizations.get', ['id' => $id]));
    }

}
