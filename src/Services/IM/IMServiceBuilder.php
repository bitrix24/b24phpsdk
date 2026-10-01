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
use Bitrix24\SDK\Services\IM\Chat\Service\Batch as ChatBatch;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUser;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUserBatch;
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Services\IM\Department\Service\Batch as DepartmentBatch;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Services\IM\Dialog\Service\Batch as DialogBatch;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Disk\Service\Batch as DiskBatch;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Services\IM\Message\Service\Batch as MessageBatch;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Services\IM\Notify\Service\Batch as NotifyBatch;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\IM\Recent\Service\Batch as RecentBatch;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Services\IM\Revision\Service\Revision;
use Bitrix24\SDK\Services\IM\Search\Service\Batch as SearchBatch;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;
use Bitrix24\SDK\Services\IM\User\Service\UserStatusBatch;
use Bitrix24\SDK\Services\IM\User\Service\User;
use Bitrix24\SDK\Services\IM\EventV2\Service\EventV2;
use Bitrix24\SDK\Services\IM\FileV2\Service\FileV2;
use Bitrix24\SDK\Services\Placement\Service\Placement;

#[ApiServiceBuilderMetadata(new Scope(['im']))]
class IMServiceBuilder extends AbstractServiceBuilder
{
    public function recent(): Recent
    {
        $this->serviceCache[__METHOD__] ??= new Recent(
            new RecentBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function search(): Search
    {
        $this->serviceCache[__METHOD__] ??= new Search(
            new SearchBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function disk(): Disk
    {
        $this->serviceCache[__METHOD__] ??= new Disk(
            new DiskBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function notify(): Notify
    {
        $this->serviceCache[__METHOD__] ??= new Notify(
            new NotifyBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function chat(): Chat
    {
        $this->serviceCache[__METHOD__] ??= new Chat(
            new ChatBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function chatUser(): ChatUser
    {
        $this->serviceCache[__METHOD__] ??= new ChatUser(
            new ChatUserBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function message(): Message
    {
        $this->serviceCache[__METHOD__] ??= new Message(
            new MessageBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function dialog(): Dialog
    {
        $this->serviceCache[__METHOD__] ??= new Dialog(
            new DialogBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function revision(): Revision
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Revision($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function counters(): Counters
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Counters($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function department(): Department
    {
        $this->serviceCache[__METHOD__] ??= new Department(
            new DepartmentBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function userStatus(): UserStatus
    {
        $this->serviceCache[__METHOD__] ??= new UserStatus(
            new UserStatusBatch(new Batch($this->core, $this->log), $this->log),
            $this->core,
            $this->log
        );

        return $this->serviceCache[__METHOD__];
    }

    public function user(): User
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new User($this->core, $this->log);
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

    public function eventV2(): EventV2
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new EventV2($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function fileV2(): FileV2
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new FileV2($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
