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

namespace Bitrix24\SDK\Services\Timeman;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Timeman\Record\Service\Record;
use Bitrix24\SDK\Services\Timeman\RecordField\Service\RecordField;
use Bitrix24\SDK\Services\Timeman\Service\Timeman;

#[ApiServiceBuilderMetadata(new Scope(['timeman']))]
class TimemanServiceBuilder extends AbstractServiceBuilder
{
    public function timeman(): Timeman
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Timeman(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function record(): Record
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Record(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function recordField(): RecordField
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new RecordField(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}

