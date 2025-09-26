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

namespace Bitrix24\SDK\Services\Disk;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

/**
 * Class DiskServiceBuilder
 *
 * @package Bitrix24\SDK\Services\Disk
 */
#[ApiServiceBuilderMetadata(new Scope(['disk']))]
class DiskServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Get Folder service
     */
    public function folder(): Folder\Service\Folder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Folder\Service\Folder(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get File service
     */
    public function file(): File\Service\File
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new File\Service\File(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Get Storage service
     */
    public function storage(): Storage\Service\Storage
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Storage\Service\Storage(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
