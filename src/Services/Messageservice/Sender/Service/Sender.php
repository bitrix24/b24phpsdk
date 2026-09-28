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

namespace Bitrix24\SDK\Services\Messageservice\Sender\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Core\ValueObjects\ValueObjectResolver;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderAddResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderDeleteResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SenderUpdateResult;
use Bitrix24\SDK\Services\Messageservice\Sender\Result\SendersListResult;

/**
 * String handler URLs and raw localized arrays are deprecated; prefer Url and LocalizedString. Plain name and description strings remain supported.
 */
#[ApiServiceMetadata(new Scope(['messageservice']))]
class Sender extends AbstractService
{
    /**
     * Register a new message service provider (sender).
     *
     * @param string $code Sender code. Allowed characters: a-z, A-Z, 0-9, ., -, _
     * @param string $type Provider type. Supported value: SMS
     * @param string|Url $handler Application handler URL called on message send
     * @param string|LocalizedString|array<string, string> $name Provider name. Can be a string or an associative array of localized strings
     * @param string|LocalizedString|array<string, string>|null $description Provider description. Can be a string or localized array
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-add.html
     */
    #[ApiEndpointMetadata(
        'messageservice.sender.add',
        'https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-add.html',
        'Register a new message service provider (sender)'
    )]
    public function add(
        string $code,
        string $type,
        string|Url $handler,
        string|LocalizedString|array $name,
        string|LocalizedString|array|null $description = null
    ): SenderAddResult {
        $params = [
            'CODE' => $code,
            'TYPE' => $type,
            'HANDLER' => ValueObjectResolver::resolveUrl($handler),
            'NAME' => is_string($name) ? $name : ValueObjectResolver::resolveLocalizedString($name),
        ];

        if ($description !== null) {
            $params['DESCRIPTION'] = is_string($description) ? $description : ValueObjectResolver::resolveLocalizedString($description);
        }

        return new SenderAddResult(
            $this->core->call('messageservice.sender.add', $params)
        );
    }

    /**
     * Update a registered message service provider (sender).
     *
     * @param string $code Sender code to update. Can be retrieved via messageservice.sender.list
     * @param string|Url|null $handler New application handler URL
     * @param string|LocalizedString|array<string, string>|null $name New provider name. Can be a string or localized array
     * @param string|LocalizedString|array<string, string>|null $description New provider description. Can be a string or localized array
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-update.html
     */
    #[ApiEndpointMetadata(
        'messageservice.sender.update',
        'https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-update.html',
        'Update a registered message service provider (sender)'
    )]
    public function update(
        string $code,
        string|Url|null $handler = null,
        string|LocalizedString|array|null $name = null,
        string|LocalizedString|array|null $description = null
    ): SenderUpdateResult {
        $params = [
            'CODE' => $code,
        ];

        if ($handler !== null) {
            $params['HANDLER'] = ValueObjectResolver::resolveUrl($handler);
        }

        if ($name !== null) {
            $params['NAME'] = is_string($name) ? $name : ValueObjectResolver::resolveLocalizedString($name);
        }

        if ($description !== null) {
            $params['DESCRIPTION'] = is_string($description) ? $description : ValueObjectResolver::resolveLocalizedString($description);
        }

        return new SenderUpdateResult(
            $this->core->call('messageservice.sender.update', $params)
        );
    }

    /**
     * Get list of sender codes registered by the current application.
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-list.html
     */
    #[ApiEndpointMetadata(
        'messageservice.sender.list',
        'https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-list.html',
        'Get list of sender codes registered by the current application'
    )]
    public function list(): SendersListResult
    {
        return new SendersListResult(
            $this->core->call('messageservice.sender.list', [])
        );
    }

    /**
     * Delete a registered message service provider (sender).
     *
     * @param string $code Sender code to delete. Can be retrieved via messageservice.sender.list
     *
     * @throws BaseException
     * @throws TransportException
     * @link https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-delete.html
     */
    #[ApiEndpointMetadata(
        'messageservice.sender.delete',
        'https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-delete.html',
        'Delete a registered message service provider (sender)'
    )]
    public function delete(string $code): SenderDeleteResult
    {
        return new SenderDeleteResult(
            $this->core->call('messageservice.sender.delete', ['CODE' => $code])
        );
    }
}
