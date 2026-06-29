<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Veronica Akhmetova <264936994+fatestr1ngs@users.noreply.github.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Booking;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Booking\Booking\Service\Booking;
use Bitrix24\SDK\Services\Booking\BookingClient\Service\BookingClient;
use Bitrix24\SDK\Services\Booking\BookingExternalData\Service\BookingExternalData;
use Bitrix24\SDK\Services\Booking\ClientType\Service\ClientType;
use Bitrix24\SDK\Services\Booking\Resource\Service\Resource;
use Bitrix24\SDK\Services\Booking\ResourceSlots\Service\ResourceSlots;
use Bitrix24\SDK\Services\Booking\ResourceType\Service\ResourceType;
use Bitrix24\SDK\Services\Booking\WaitlistClient\Service\WaitlistClient;
use Bitrix24\SDK\Services\Booking\WaitlistExternalData\Service\WaitlistExternalData;
use Bitrix24\SDK\Services\Booking\Waitlist\Service\Waitlist;

#[ApiServiceBuilderMetadata(new Scope(['booking']))]
class BookingServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Booking service (booking.v1.booking.*)
     */
    public function booking(): Booking
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Booking($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Booking client service (booking.v1.booking.client.*)
     */
    public function bookingClient(): BookingClient
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new BookingClient($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Booking external data service (booking.v1.booking.externalData.*)
     */
    public function bookingExternalData(): BookingExternalData
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new BookingExternalData($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Resource service (booking.v1.resource.*)
     */
    public function resource(): Resource
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Resource($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Resource slot service (booking.v1.resource.slots.*)
     */
    public function resourceSlots(): ResourceSlots
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ResourceSlots($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Resource type service (booking.v1.resourceType.*)
     */
    public function resourceType(): ResourceType
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ResourceType($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Client type service (booking.v1.clienttype.*)
     */
    public function clientType(): ClientType
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new ClientType($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Waitlist service (booking.v1.waitlist.*)
     */
    public function waitlist(): Waitlist
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new Waitlist($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Waitlist client service (booking.v1.waitlist.client.*)
     */
    public function waitlistClient(): WaitlistClient
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new WaitlistClient($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }

    /**
     * Waitlist external data service (booking.v1.waitlist.externalData.*)
     */
    public function waitlistExternalData(): WaitlistExternalData
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new WaitlistExternalData($this->core, $this->log);
        }

        return $this->serviceCache[__METHOD__];
    }
}
