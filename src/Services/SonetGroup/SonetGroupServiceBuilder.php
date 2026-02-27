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

namespace Bitrix24\SDK\Services\SonetGroup;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * Class SonetGroupServiceBuilder
 *
 * @package Bitrix24\SDK\Services\SonetGroup
 */
#[ApiServiceBuilderMetadata(new Scope(['sonet_group', 'socialnetwork']))]
class SonetGroupServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Social network groups service (sonet_group.*)
     */
    public function sonetGroup(): Service\SonetGroup
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Service\SonetGroup(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
