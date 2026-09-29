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

namespace Bitrix24\SDK\Services\Pull\Configuration\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Pull\Configuration\Result\PullConfigurationResult;

#[ApiServiceMetadata(new Scope(['pull_channel']))]
class Configuration extends AbstractService
{
    /** @throws BaseException */
    #[ApiEndpointMetadata('pull.config.get', 'https://github.com/bitrix24/b24jssdk/blob/main/packages/jssdk/src/pullClient/client.ts', 'Returns Pull configuration.')]
    public function get(?bool $cache = null, ?bool $reopen = null): PullConfigurationResult
    {
        $parameters = [];
        if ($cache !== null) {
            $parameters['CACHE'] = $cache ? 'Y' : 'N';
        }

        if ($reopen !== null) {
            $parameters['REOPEN'] = $reopen ? 'Y' : 'N';
        }

        return new PullConfigurationResult($this->core->call('pull.config.get', $parameters));
    }
}
