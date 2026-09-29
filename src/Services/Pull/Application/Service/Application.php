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

namespace Bitrix24\SDK\Services\Pull\Application\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Pull\Configuration\Result\PullConfigurationResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;

/** All application methods require OAuth application authorization; incoming webhooks are not supported. */
#[ApiServiceMetadata(new Scope(['pull']))]
class Application extends AbstractService
{
    /** @throws BaseException */
    #[ApiEndpointMetadata('pull.application.config.get', 'https://apidocs.bitrix24.com/settings/interactivity/pull-application-config-get.html', 'Returns Pull configuration.')]
    public function config(?bool $cache = null, ?bool $reopen = null): PullConfigurationResult
    {
        $parameters = [];
        if ($cache !== null) {
            $parameters['CACHE'] = $cache ? 'Y' : 'N';
        }

        if ($reopen !== null) {
            $parameters['REOPEN'] = $reopen ? 'Y' : 'N';
        }

        return new PullConfigurationResult($this->core->call('pull.application.config.get', $parameters));
    }

    /**
     * Requires OAuth application authorization. Omit userId for the shared channel.
     * Preserve a string user ID for non-administrator callers.
     * @param array<string, mixed> $params
     * @param int|string|array<int, int|string>|null $userId
     * @throws BaseException
     */
    #[ApiEndpointMetadata('pull.application.event.add', 'https://apidocs.bitrix24.com/settings/interactivity/pull-application-event-add.html', 'Sends an event to an application channel.')]
    public function event(string $command, array $params = [], ?string $moduleId = null, int|string|array|null $userId = null): UpdatedItemResult
    {
        $parameters = ['COMMAND' => $command, 'PARAMS' => $params];
        if ($moduleId !== null) {
            $parameters['MODULE_ID'] = $moduleId;
        }

        if ($userId !== null) {
            $parameters['USER_ID'] = $userId;
        }

        return new UpdatedItemResult($this->core->call('pull.application.event.add', $parameters));
    }

    /**
     * Requires OAuth application authorization and explicit recipients.
     * @param int|string|array<int, int|string> $userId
     * @throws BaseException
     */
    #[ApiEndpointMetadata('pull.application.push.add', 'https://apidocs.bitrix24.com/settings/interactivity/pull-application-push-add.html', 'Sends a push notification to application users.')]
    public function push(int|string|array $userId, string $text, ?string $avatar = null): UpdatedItemResult
    {
        $parameters = ['USER_ID' => $userId, 'TEXT' => $text];
        if ($avatar !== null) {
            $parameters['AVATAR'] = $avatar;
        }

        return new UpdatedItemResult($this->core->call('pull.application.push.add', $parameters));
    }
}
