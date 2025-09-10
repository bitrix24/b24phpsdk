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

namespace Bitrix24\SDK\Services\Sale\Payment\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Carbon\CarbonImmutable;
use Money\Money;

/**
 * Class PaymentItemResult
 * Represents a single payment item (payment) returned by Bitrix24 REST API.
 *
 * Fields and their types are taken from Bitrix24 API (sale.payment.getfields).
 *
 * @property-read string|null $accountNumber Payment account number
 * @property-read string|null $comments Payment comments
 * @property-read int|null    $companyId Company identifier
 * @property-read string|null $currency Payment currency code
 * @property-read CarbonImmutable|null $dateBill Invoice date
 * @property-read CarbonImmutable|null $dateMarked Date when payment was marked
 * @property-read CarbonImmutable|null $datePaid Payment date
 * @property-read CarbonImmutable|null $datePayBefore Date by which the invoice must be paid
 * @property-read CarbonImmutable|null $dateResponsibleId Date when responsible was assigned
 * @property-read int|null    $empMarkedId User who marked the payment
 * @property-read int|null    $empPaidId User who made the payment
 * @property-read int|null    $empResponsibleId User who assigned the responsible
 * @property-read int|null    $empReturnId User who processed the return
 * @property-read bool|null $externalPayment External payment flag
 * @property-read int|null    $id Payment ID
 * @property-read string|null $id1c Identifier in QuickBooks
 * @property-read bool|null $isReturn Return processed flag
 * @property-read bool|null $marked Problem marking flag
 * @property-read int|null    $orderId Order ID
 * @property-read bool|null $paid Payment status
 * @property-read string|null $payReturnComment Return comment
 * @property-read CarbonImmutable|null $payReturnDate Return document date
 * @property-read string|null $payReturnNum Return document number
 * @property-read int|null    $paySystemId Payment system ID
 * @property-read bool|null $paySystemIsCash Is cash payment system
 * @property-read string|null $paySystemName Payment system name
 * @property-read string|null $paySystemXmlId Payment system XML ID
 * @property-read CarbonImmutable|null $payVoucherDate Payment document date
 * @property-read string|null $payVoucherNum Payment document number
 * @property-read Money|null $priceCod Cost of payment upon delivery
 * @property-read string|null $psCurrency Payment system currency
 * @property-read string|null $psInvoiceId Payment ID in payment system
 * @property-read CarbonImmutable|null $psResponseDate Payment system response date
 * @property-read bool|null $psStatus Payment system status
 * @property-read string|null $psStatusCode Payment system status code
 * @property-read string|null $psStatusDescription Description of payment system result
 * @property-read string|null $psStatusMessage Message from payment system
 * @property-read Money|null  $psSum Payment system amount
 * @property-read string|null $reasonMarked Reason for marking
 * @property-read int|null    $responsibleId User responsible for payment
 * @property-read Money|null  $sum Payment amount
 * @property-read bool|null $updated1c Updated via QuickBooks flag
 * @property-read string|null $version1c Payment document version
 * @property-read string|null $xmlId External identifier
 */
class PaymentItemResult extends AbstractItem
{
}
