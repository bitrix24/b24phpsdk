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

namespace Bitrix24\SDK\Services\Timeman\RecordField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldResult;
use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldsResult;

#[ApiServiceMetadata(new Scope(['timeman']))]
class RecordField extends AbstractService
{
    /**
     * Returns the description of a single work-time record field by its name.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-field-get.html
     *
     * @param non-empty-string $name   Field name, e.g. 'startTime'
     * @param string[]         $select Descriptor fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable, editable, multiple,
     *                                 elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.record.field.get',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-field-get.html',
        'Returns the description of a single work-time record field by its name.',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): RecordFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new RecordFieldResult(
            $this->core->call('timeman.record.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Returns the list of available work-time record field descriptors.
     *
     * @link https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-field-list.html
     *
     * @param string[] $select Descriptor fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable, editable, multiple,
     *                         elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'timeman.record.field.list',
        'https://apidocs.bitrix24.com/api-reference/rest-v3/timeman/timeman-record-field-list.html',
        'Returns the list of available work-time record field descriptors.',
        ApiVersion::v3
    )]
    public function list(array $select = []): RecordFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new RecordFieldsResult(
            $this->core->call('timeman.record.field.list', $params, ApiVersion::v3)
        );
    }
}
