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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Enum\Service;

use Bitrix24\SDK\Services\CRM\Enum\Service\Enum;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(Enum::class)]
#[CoversMethod(Enum::class, 'ownerType')]
#[CoversMethod(Enum::class, 'addressType')]
#[CoversMethod(Enum::class, 'settingsMode')]
#[CoversMethod(Enum::class, 'contentType')]
#[CoversMethod(Enum::class, 'orderOwnerTypes')]
#[CoversMethod(Enum::class, 'activityStatus')]
#[CoversMethod(Enum::class, 'activityNotifyType')]
#[CoversMethod(Enum::class, 'activityPriority')]
#[CoversMethod(Enum::class, 'activityDirection')]
#[CoversMethod(Enum::class, 'activityType')]

class EnumTest extends TestCase
{
    protected Enum $enumService;

    public function testOwnerType(): void
    {
        $this->assertGreaterThan(1, count($this->enumService->ownerType()->getItems()));
    }

    public function testActivityStatus(): void
    {
        foreach ($this->enumService->activityStatus()->getItems() as $activityStatusItemResult) {
            $this->assertEquals($activityStatusItemResult->ID, $activityStatusItemResult->ENUM->value);
        }
    }

    public function testAddressType(): void
    {
        foreach ($this->enumService->addressType()->getItems() as $addressTypeFieldItemResult) {
            $this->assertEquals($addressTypeFieldItemResult->ID, $addressTypeFieldItemResult->ENUM->value);
        }
    }

    public function testActivityNotifyType(): void
    {
        foreach ($this->enumService->activityNotifyType()->getItems() as $activityNotifyTypeItemResult) {
            $this->assertEquals($activityNotifyTypeItemResult->ID, $activityNotifyTypeItemResult->ENUM->value);
        }
    }

    public function testActivityPriority(): void
    {
        foreach ($this->enumService->activityPriority()->getItems() as $activityPriorityTypeItemResult) {
            $this->assertEquals($activityPriorityTypeItemResult->ID, $activityPriorityTypeItemResult->ENUM->value);
        }
    }

    public function testActivityDirection(): void
    {
        foreach ($this->enumService->activityDirection()->getItems() as $activityDirectionItemResult) {
            $this->assertEquals($activityDirectionItemResult->ID, $activityDirectionItemResult->ENUM->value);
        }
    }

    public function testActivityType(): void
    {
        foreach ($this->enumService->activityType()->getItems() as $activityTypeItemResult) {
            $this->assertEquals($activityTypeItemResult->ID, $activityTypeItemResult->ENUM->value);
        }
    }

    public function testSettingsMode(): void
    {
        foreach ($this->enumService->settingsMode()->getItems() as $contentTypeItemResult) {
            $this->assertEquals($contentTypeItemResult->ID, $contentTypeItemResult->ENUM->value);
        }
    }

    public function testContentType(): void
    {
        foreach ($this->enumService->contentType()->getItems() as $contentTypeItemResult) {
            $this->assertEquals($contentTypeItemResult->ID, $contentTypeItemResult->ENUM->value);
        }
    }

    public function testOrderOwnerType(): void
    {
        $this->assertGreaterThanOrEqual(1, count($this->enumService->orderOwnerTypes()->getItems()));
    }

    public function testFields(): void
    {
        $this->assertGreaterThan(1, count($this->enumService->fields()->getFieldsDescription()));
    }

    protected function setUp(): void
    {
        $this->enumService = Fabric::getServiceBuilder()->getCRMScope()->enum();
    }
}