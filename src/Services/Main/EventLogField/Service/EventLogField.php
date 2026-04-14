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

namespace Bitrix24\SDK\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldResult;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldsResult;

#[ApiServiceMetadata(new Scope(['main']))]
class EventLogField extends AbstractService
{
    /**
     * Get metadata for a single event log field by name.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-get.html
     *
     * @param non-empty-string $name   Field code, e.g. 'timestampX'
     * @param string[]         $select Fields to return. Available: name, type, title, description,
     *                                 validationRules, requiredGroups, filterable, sortable,
     *                                 editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.field.get',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-get.html',
        'Get metadata for a single event log field by name',
        ApiVersion::v3
    )]
    public function get(string $name, array $select = []): EventLogFieldResult
    {
        $this->guardNonEmptyString($name, 'field name must not be empty');

        $params = ['name' => $name];
        if ($select !== []) {
            $params['select'] = $select;
        }

        return new EventLogFieldResult(
            $this->core->call('main.eventlog.field.get', $params, ApiVersion::v3)
        );
    }

    /**
     * Get list of all available event log field descriptors.
     *
     * @link https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-list.html
     *
     * @param string[] $select Fields to return. Available: name, type, title, description,
     *                         validationRules, requiredGroups, filterable, sortable,
     *                         editable, multiple, elementType
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'main.eventlog.field.list',
        'https://apidocs.bitrix24.ru/api-reference/rest-v3/main/main-eventlog-field-list.html',
        'Get list of all available event log field descriptors',
        ApiVersion::v3
    )]
    public function list(array $select = []): EventLogFieldsResult
    {
        $params = $select !== [] ? ['select' => $select] : [];

        return new EventLogFieldsResult(
            $this->core->call('main.eventlog.field.list', $params, ApiVersion::v3)
        );
    }
}
