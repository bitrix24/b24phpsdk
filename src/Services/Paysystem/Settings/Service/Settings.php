<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Paysystem\Settings\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Paysystem\Settings\Result\SettingsItemResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['pay_system']))]
class Settings extends AbstractService
{
    /**
     * Returns the settings of the payment system.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-get.html
     *
     * @param int $paySystemId Payment system identifier
     * @param int $personTypeId Payer type identifier (pass 0 to get default settings)
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.settings.get',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-get.html',
        'Returns the settings of the payment system'
    )]
    public function get(int $paySystemId, int $personTypeId): SettingsItemResult
    {
        return new SettingsItemResult(
            $this->core->call(
                'sale.paysystem.settings.get',
                [
                    'ID' => $paySystemId,
                    'PERSON_TYPE_ID' => $personTypeId,
                ]
            )->getResponseData()->getResult()
        );
    }

    /**
     * Updates the payment system settings.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-update.html
     *
     * @param int $paySystemId Payment system identifier
     * @param array $settings Settings to be updated. Each setting should have structure:
     *                        ['PARAMETER_NAME' => ['TYPE' => 'VALUE', 'VALUE' => 'parameter_value']]
     * @param int|null $personTypeId Payer type identifier (optional)
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.settings.update',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-update.html',
        'Updates the payment system settings'
    )]
    public function update(int $paySystemId, array $settings, ?int $personTypeId = null): UpdatedItemResult
    {
        $params = [
            'ID' => $paySystemId,
            'SETTINGS' => $settings,
        ];

        if ($personTypeId !== null) {
            $params['PERSON_TYPE_ID'] = $personTypeId;
        }

        return new UpdatedItemResult(
            $this->core->call('sale.paysystem.settings.update', $params)
        );
    }

    /**
     * Returns the payment system settings for a specific payment.
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-payment-get.html
     *
     * @param int $paymentId Payment identifier
     * @param int $paySystemId Payment system identifier
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.settings.payment.get',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-payment-get.html',
        'Returns the payment system settings for a specific payment'
    )]
    public function getForPayment(int $paymentId, int $paySystemId): SettingsItemResult
    {
        return new SettingsItemResult(
            $this->core->call(
                'sale.paysystem.settings.payment.get',
                [
                    'PAYMENT_ID' => $paymentId,
                    'PAY_SYSTEM_ID' => $paySystemId,
                ]
            )->getResponseData()->getResult()
        );
    }

    /**
     * Returns the payment system settings for a specific invoice (legacy version).
     *
     * @link https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-invoice-get.html
     *
     * @param int $invoiceId Legacy invoice identifier
     * @param int|null $paySystemId Payment system identifier (optional if bxRestHandler is provided)
     * @param string|null $bxRestHandler Symbolic identifier of the payment system REST handler (optional if paySystemId is provided)
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'sale.paysystem.settings.invoice.get',
        'https://apidocs.bitrix24.com/api-reference/pay-system/sale-pay-system-settings-invoice-get.html',
        'Returns the payment system settings for a specific invoice (legacy version)'
    )]
    public function getForInvoice(int $invoiceId, ?int $paySystemId = null, ?string $bxRestHandler = null): SettingsItemResult
    {
        if ($paySystemId === null && $bxRestHandler === null) {
            throw new \InvalidArgumentException('Either paySystemId or bxRestHandler parameter must be provided');
        }

        $params = ['INVOICE_ID' => $invoiceId];

        if ($paySystemId !== null) {
            $params['PAY_SYSTEM_ID'] = $paySystemId;
        }

        if ($bxRestHandler !== null) {
            $params['BX_REST_HANDLER'] = $bxRestHandler;
        }

        return new SettingsItemResult(
            $this->core->call(
                'sale.paysystem.settings.invoice.get',
                $params
            )->getResponseData()->getResult()
        );
    }
}
