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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Result;

use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormSelectBuilder;
use Carbon\CarbonImmutable;

/**
 * Nested blocks are returned only when they are listed in select.
 *
 * @property-read int                                    $id
 * @property-read string|null                            $code
 * @property-read string|null                            $name
 * @property-read bool|null                              $active
 * @property-read bool|null                              $system            system forms cannot be changed or deleted
 * @property-read string|null                            $languageId
 * @property-read string|null                            $templateId
 * @property-read CarbonImmutable|null                   $createdTime
 * @property-read CarbonImmutable|null                   $updatedTime       form version for update/delete/activate
 * @property-read int|null                               $activeChangedById
 * @property-read CarbonImmutable|null                   $activeChangedTime
 * @property-read FormAppearanceItemResult|null          $appearance
 * @property-read FormCrmSettingsItemResult|null         $crm
 * @property-read array|null                             $dependencies      field dependency groups as returned by the API
 * @property-read array<FormPresetFieldItemResult>|null  $presetFields
 * @property-read FormResponsibleItemResult|null         $responsible
 * @property-read FormAgreementsItemResult|null          $agreements
 * @property-read FormSubmissionItemResult|null          $submission
 * @property-read FormPaymentItemResult|null             $payment
 * @property-read FormCallbackItemResult|null            $callback
 * @property-read FormBookingItemResult|null             $booking
 * @property-read FormAnalyticsItemResult|null           $analytics
 * @property-read FormSpamProtectionItemResult|null      $spamProtection
 * @property-read FormIntegrationsItemResult|null        $integrations
 * @property-read array<FormFieldItemResult>|null        $fields
 */
#[OpenApiEntity(
    entityKey:     'bitrix.crm.formdto',
    selectBuilder: FormSelectBuilder::class,
)]
class FormItemResult extends AbstractAnnotatedItem
{
}
