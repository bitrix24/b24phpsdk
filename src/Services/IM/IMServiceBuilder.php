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

namespace Bitrix24\SDK\Services\IM;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\Placement\Service\Placement;

#[ApiServiceBuilderMetadata(new Scope(['im']))]
class IMServiceBuilder extends AbstractServiceBuilder
{
    public function disk(): Disk
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Disk($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function notify(): Notify
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Notify($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function placementLocationCodes(): PlacementLocationCodes
    {
        return new PlacementLocationCodes();
    }

    public function placements(): Placements
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Placements(new Placement($this->core, $this->log));
        }

        return $this->serviceCache[__METHOD__];
    }
}
