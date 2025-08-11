<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;

#[ApiServiceBuilderMetadata(new Scope(['task']))]
class TaskServiceBuilder extends AbstractServiceBuilder
{
    public function task(): Service\Task
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $batch = new Batch(
                $this->core,
                $this->log
            );
            $this->serviceCache[__METHOD__] = new Service\Task(
                new Service\Batch($batch, $this->log),
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function checklistitem(): Checklistitem\Service\Checklistitem
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Checklistitem\Service\Checklistitem(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function commentitem(): Commentitem\Service\Commentitem
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Commentitem\Service\Commentitem(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function result(): TaskResult\Service\Result
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new TaskResult\Service\Result(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

    public function elapseditem(): Elapseditem\Service\Elapseditem
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Elapseditem\Service\Elapseditem(
                $this->core,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }

}
