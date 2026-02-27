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

namespace Bitrix24\SDK\Legacy;

use Bitrix24\SDK\Legacy\Services\Task\LegacyTaskServiceBuilder;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

class LegacyServiceBuilder extends AbstractServiceBuilder
{
    public function getTaskScope(): LegacyTaskServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new LegacyTaskServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
