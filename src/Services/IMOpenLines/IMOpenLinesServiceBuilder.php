<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\IMOpenLines\Bot\Service\Bot;
use Bitrix24\SDK\Services\IMOpenLines\Config\Service\Config;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Service\Chat;
use Bitrix24\SDK\Services\IMOpenLines\Message\Service\Message;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Service\Operator;
use Bitrix24\SDK\Services\IMOpenLines\Service\Network;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Service\Connector;
use Bitrix24\SDK\Services\IMOpenLines\Session\Service\Session;

#[ApiServiceBuilderMetadata(new Scope(['imopenlines']))]
class IMOpenLinesServiceBuilder extends AbstractServiceBuilder
{
    public function bot(): Bot
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Bot($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function config(): Config
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Config($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function crmChat(): Chat
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Chat($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function message(): Message
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Message($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function Network(): Network
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Network($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function connector(): Connector
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Connector($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function operator(): Operator
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Operator($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function session(): Session
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Session($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
