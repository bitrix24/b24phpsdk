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

namespace Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormPlacementListPagination;
use Bitrix24\SDK\Services\CRM\V3\FormOpenLine\Result\OpenLinesResult;

#[ApiServiceMetadata(new Scope(['crm']))]
class FormOpenLine extends AbstractService
{
    /**
     * Returns all Open Channels of the portal with the placement state of one CRM form (`bound`).
     *
     * The API requires a filter by form, so `$formId` is mandatory; only the `=` operator and sorting by id are supported.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-openline-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.openline.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-openline-list.html',
        'Returns Open Channels with the placement state of a CRM form',
        ApiVersion::v3
    )]
    public function list(
        int $formId,
        ?SortOrder $idOrder = null,
        ?FormPlacementListPagination $pagination = null,
    ): OpenLinesResult {
        return new OpenLinesResult(
            $this->core->call(
                'crm.form.openline.list',
                array_filter(
                    [
                        'filter'     => [['formId', '=', $formId]],
                        'order'      => $idOrder instanceof SortOrder ? ['id' => $idOrder->value] : null,
                        'pagination' => $pagination?->toArray(),
                    ],
                    static fn (?array $value): bool => $value !== null && $value !== []
                ),
                ApiVersion::v3
            )
        );
    }
}
