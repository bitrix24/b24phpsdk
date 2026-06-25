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

namespace Bitrix24\SDK\Services\Landing\RepoWidget\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetDebugResult;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetGetListResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['landing']))]
class RepoWidget extends AbstractService
{
    public function __construct(CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Registers a new Vibe widget or updates its content if a widget with the same code
     * already exists.
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-register.html
     *
     * @param string $code   Unique widget code (recommended to include an app-specific prefix)
     * @param array  $fields Widget field values: NAME, PREVIEW, DESCRIPTION, CONTENT,
     *                       SECTIONS, WIDGET_PARAMS, ACTIVE, SITE_TEMPLATE_ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repowidget.register',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-register.html',
        'Registers a new Vibe widget or updates its content if a widget with the same code already exists.'
    )]
    public function register(string $code, array $fields): AddedItemResult
    {
        return new AddedItemResult(
            $this->core->call('landing.repowidget.register', [
                'code'   => $code,
                'fields' => $fields,
            ])
        );
    }

    /**
     * Removes a Vibe widget. Returns true on success, false if the widget did not exist.
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-unregister.html
     *
     * @param string $code Unique code of the widget to remove
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repowidget.unregister',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-unregister.html',
        'Removes a Vibe widget. Returns true on success, false if the widget did not exist.'
    )]
    public function unregister(string $code): DeletedItemResult
    {
        return new DeletedItemResult(
            $this->core->call('landing.repowidget.unregister', ['code' => $code])
        );
    }

    /**
     * Returns the list of Vibe widgets registered by the current application.
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-get-list.html
     *
     * @param array $select Fields to select (empty means all fields)
     * @param array $filter Filter conditions
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repowidget.getlist',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-get-list.html',
        'Returns the list of Vibe widgets registered by the current application.'
    )]
    public function getList(array $select = [], array $filter = []): RepoWidgetGetListResult
    {
        $params = [];
        if ($select !== []) {
            $params['select'] = $select;
        }

        if ($filter !== []) {
            $params['filter'] = $filter;
        }

        $callParams = [];
        if ($params !== []) {
            $callParams['params'] = $params;
        }

        return new RepoWidgetGetListResult(
            $this->core->call('landing.repowidget.getlist', $callParams)
        );
    }

    /**
     * Enables or disables debug mode for all Vibe widgets of the current application.
     * Debug mode increases the number of error messages in the JavaScript console.
     *
     * @link https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-debug.html
     *
     * @param bool $enable true to enable debug mode, false to disable
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'landing.repowidget.debug',
        'https://apidocs.bitrix24.com/api-reference/vibe/landing-repowidget-debug.html',
        'Enables or disables debug mode for all Vibe widgets of the current application.'
    )]
    public function debug(bool $enable): RepoWidgetDebugResult
    {
        return new RepoWidgetDebugResult(
            $this->core->call('landing.repowidget.debug', ['enable' => $enable])
        );
    }
}

