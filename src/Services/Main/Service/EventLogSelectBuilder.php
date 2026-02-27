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

namespace Bitrix24\SDK\Services\Main\Service;

use Bitrix24\SDK\Services\AbstractSelectBuilder;

class EventLogSelectBuilder extends AbstractSelectBuilder
{
    public function __construct()
    {
        $this->select[] = 'id';
    }

    public function timestampX(): self
    {
        $this->select[] = 'timestampX';
        return $this;
    }

    public function severity(): self
    {
        $this->select[] = 'severity';
        return $this;
    }

    public function auditTypeId(): self
    {
        $this->select[] = 'auditTypeId';
        return $this;
    }

    public function moduleId(): self
    {
        $this->select[] = 'moduleId';
        return $this;
    }

    public function itemId(): self
    {
        $this->select[] = 'itemId';
        return $this;
    }

    public function remoteAddr(): self
    {
        $this->select[] = 'remoteAddr';
        return $this;
    }

    public function userAgent(): self
    {
        $this->select[] = 'userAgent';
        return $this;
    }

    public function requestUri(): self
    {
        $this->select[] = 'requestUri';
        return $this;
    }

    public function siteId(): self
    {
        $this->select[] = 'siteId';
        return $this;
    }

    public function userId(): self
    {
        $this->select[] = 'userId';
        return $this;
    }

    public function guestId(): self
    {
        $this->select[] = 'guestId';
        return $this;
    }

    public function description(): self
    {
        $this->select[] = 'description';
        return $this;
    }
}
