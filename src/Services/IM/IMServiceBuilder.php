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
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Chat\Service\ChatUser;
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Services\IM\Department\Service\Department;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Disk\Service\Disk;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\IM\Placements\Placements;
use Bitrix24\SDK\Services\IM\Recent\Service\Recent;
use Bitrix24\SDK\Services\IM\Revision\Service\Revision;
use Bitrix24\SDK\Services\IM\Search\Service\Search;
use Bitrix24\SDK\Services\IM\User\Service\UserStatus;
use Bitrix24\SDK\Services\IM\User\Service\User;
use Bitrix24\SDK\Services\Placement\Service\Placement;

#[ApiServiceBuilderMetadata(new Scope(['im']))]
class IMServiceBuilder extends AbstractServiceBuilder
{
    public function recent(): Recent
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Recent($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function search(): Search
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Search($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

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

    public function chat(): Chat
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Chat($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function chatUser(): ChatUser
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ChatUser($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function message(): Message
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Message($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function dialog(): Dialog
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Dialog($this->core, $this->log);
        }

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
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Department($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    public function userStatus(): UserStatus
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new UserStatus($this->core, $this->log);
        }

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
}
