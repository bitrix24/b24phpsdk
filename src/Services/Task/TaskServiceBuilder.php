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
use Bitrix24\SDK\Services\Task\Userfield\Service\UserfieldConstraints;

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

    public function taskAccess(): Service\TaskAccess
    {
        $this->serviceCache[__METHOD__] ??= new Service\TaskAccess(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskChat(): Service\TaskChat
    {
        $this->serviceCache[__METHOD__] ??= new Service\TaskChat(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskChatMessageField(): ChatMessageField\Service\ChatMessageField
    {
        $this->serviceCache[__METHOD__] ??= new ChatMessageField\Service\ChatMessageField(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskFileField(): FileField\Service\FileField
    {
        $this->serviceCache[__METHOD__] ??= new FileField\Service\FileField(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskAccessField(): AccessField\Service\AccessField
    {
        $this->serviceCache[__METHOD__] ??= new AccessField\Service\AccessField(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskField(): TaskField\Service\TaskField
    {
        $this->serviceCache[__METHOD__] ??= new TaskField\Service\TaskField(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function taskFile(): Service\TaskFile
    {
        $this->serviceCache[__METHOD__] ??= new Service\TaskFile(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function userfield(): Userfield\Service\Userfield
    {
        $this->serviceCache[__METHOD__] ??= new Userfield\Service\Userfield(
            new UserfieldConstraints(),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function checklistitem(): Checklistitem\Service\Checklistitem
    {
        $this->serviceCache[__METHOD__] ??= new Checklistitem\Service\Checklistitem(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function commentitem(): Commentitem\Service\Commentitem
    {
        $this->serviceCache[__METHOD__] ??= new Commentitem\Service\Commentitem(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function result(): TaskResult\Service\Result
    {
        $this->serviceCache[__METHOD__] ??= new TaskResult\Service\Result(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function elapseditem(): Elapseditem\Service\Elapseditem
    {
        $this->serviceCache[__METHOD__] ??= new Elapseditem\Service\Elapseditem(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function stage(): Stage\Service\Stage
    {
        $this->serviceCache[__METHOD__] ??= new Stage\Service\Stage(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function planner(): Planner\Service\Planner
    {
        $this->serviceCache[__METHOD__] ??= new Planner\Service\Planner(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function flow(): Flow\Service\Flow
    {
        $this->serviceCache[__METHOD__] ??= new Flow\Service\Flow(
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

}
