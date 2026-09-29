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

namespace Bitrix24\SDK\Services\Pull;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Pull\Channel\Service\Channel;
use Bitrix24\SDK\Services\Pull\Configuration\Service\Configuration;
use Bitrix24\SDK\Services\Pull\Application\Service\Application;
use Bitrix24\SDK\Services\Pull\Watch\Service\Watch;

#[ApiServiceBuilderMetadata(new Scope(['pull_channel', 'pull']))]
class PullServiceBuilder extends AbstractServiceBuilder
{
    public function channel(): Channel
    {
        $this->serviceCache[__METHOD__] ??= new Channel($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function configuration(): Configuration
    {
        $this->serviceCache[__METHOD__] ??= new Configuration($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function application(): Application
    {
        $this->serviceCache[__METHOD__] ??= new Application($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }

    public function watch(): Watch
    {
        $this->serviceCache[__METHOD__] ??= new Watch($this->core, $this->log);

        return $this->serviceCache[__METHOD__];
    }
}
