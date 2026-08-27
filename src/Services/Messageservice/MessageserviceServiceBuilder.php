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

namespace Bitrix24\SDK\Services\Messageservice;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Messageservice\Message\Status\Service\MessageStatus;
use Bitrix24\SDK\Services\Messageservice\Sender\Service\Sender;

#[ApiServiceBuilderMetadata(new Scope(['messageservice']))]
class MessageserviceServiceBuilder extends AbstractServiceBuilder
{
    public function sender(): Sender
    {
        $this->serviceCache[__METHOD__] ??= new Sender(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function messageStatus(): MessageStatus
    {
        $this->serviceCache[__METHOD__] ??= new MessageStatus(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }
}
