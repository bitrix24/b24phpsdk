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

namespace Bitrix24\SDK\Services\IMOpenLines;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\IMOpenLines\Service\Network;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Service\Connector;

#[ApiServiceBuilderMetadata(new Scope(['imopenlines']))]
class IMOpenLinesServiceBuilder extends AbstractServiceBuilder
{
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
}
