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

namespace Bitrix24\SDK\Services\Paysystem;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Paysystem\Handler\Service\Handler;

/**
 * Class PaysystemServiceBuilder
 *
 * @package Bitrix24\SDK\Services\Paysystem
 */
#[ApiServiceBuilderMetadata(new Scope(['pay_system']))]
class PaysystemServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Payment system handlers service (sale.paysystem.handler.*)
     */
    public function handler(): Handler
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Handler(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}