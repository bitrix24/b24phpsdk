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

namespace Bitrix24\SDK\Legacy\Services\Task;

use Bitrix24\SDK\Legacy\Services\Task\Service\Batch;
use Bitrix24\SDK\Legacy\Services\Task\Service\Task;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Task\Batch as TaskBatch;

/**
 * @deprecated Use {@see \Bitrix24\SDK\Services\Task\TaskServiceBuilder} via v3 API instead.
 *             Provides access to v1 task service. Will be removed once v3 reaches feature parity.
 */
class LegacyTaskServiceBuilder extends AbstractServiceBuilder
{
    public function task(): Task
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new TaskBatch($this->core, $this->log);
            $this->serviceCache[__METHOD__] = new Task(
                new Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
}
