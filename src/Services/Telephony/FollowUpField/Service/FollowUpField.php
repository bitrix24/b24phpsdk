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

namespace Bitrix24\SDK\Services\Telephony\FollowUpField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldResult;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldsResult;

#[ApiServiceMetadata(new Scope(['call']))]
class FollowUpField extends AbstractService
{
    /**
     * Returns the description of a single Follow-up field by its name.
     *
     * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-field-get.html
     *
     * @param non-empty-string $name   Field name, e.g. 'callId'. Available names can be obtained via list()
     * @param string[]         $select Descriptor fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable, editable, multiple,
     *                                 elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'call.followup.field.get',
        'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-field-get.html',
        'Returns the description of a single Follow-up field by its name.',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): FollowUpFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        return new FollowUpFieldResult(
            $this->core->call(
                'call.followup.field.get',
                array_filter(
                    [
                        'name'   => $name,
                        'select' => $select,
                    ],
                    static fn (mixed $value): bool => $value !== []
                ),
                ApiVersion::v3
            )
        );
    }

    /**
     * Returns the list of available Follow-up field descriptors.
     *
     * @link https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-field-list.html
     *
     * @param string[] $select Descriptor fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable, editable, multiple,
     *                         elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'call.followup.field.list',
        'https://apidocs.bitrix24.com/api-reference/telephony/follow-up/call-followup-field-list.html',
        'Returns the list of available Follow-up field descriptors.',
        ApiVersion::v3
    )]
    public function list(array $select = []): FollowUpFieldsResult
    {
        return new FollowUpFieldsResult(
            $this->core->call(
                'call.followup.field.list',
                array_filter(['select' => $select], static fn (mixed $value): bool => $value !== []),
                ApiVersion::v3
            )
        );
    }
}
