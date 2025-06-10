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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;


use Bitrix24\SDK\Services\CRM\Common\ContactConnection;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Services\CRM\Lead\Service\LeadContact;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\ContactBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(LeadContact::class)]
#[CoversMethod(LeadContact::class, 'fields')]
#[CoversMethod(LeadContact::class, 'setItems')]
#[CoversMethod(LeadContact::class, 'deleteItems')]
#[CoversMethod(LeadContact::class, 'get')]
#[CoversMethod(LeadContact::class, 'add')]
#[CoversMethod(LeadContact::class, 'delete')]
class LeadContactTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdLeads = [];
    private Lead  $leadService;
    private array $createdContacts = [];
    private Lead  $contactService;

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
        $this->leadService = $this->sb->getCRMScope()->lead();
        $this->contactService = $this->contactService;
    }

    public function tearDown(): void
    {
        foreach ($this->leadService->batch->delete($this->createdLeads) as $result) {
        }

        foreach ($this->contactService->batch->delete($this->createdContacts) as $result) {
        }
    }

    public function testSet(): void
    {
        $leadId = $this->leadService->add(['TITLE' => 'test LeadContact Set'])->getId();
        $this->createdLeads[] = $leadId;

        $contactIdOne = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->sb->getCRMScope()->leadContact()->setItems($leadId, [
            new ContactConnection($contactIdOne, 100, true),
            new ContactConnection($contactIdTwo, 100, false),
        ]);

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->leadContact()->get($leadId)->getContactConnections();

        foreach ($connectedContacts as $item) {
            $this->assertContains($item->CONTACT_ID, $connectedId);
        }
    }

    public function testAdd(): void
    {
        $leadId = $this->leadService->add((['TITLE' => 'test LeadContact Add'])->build())->getId();
        $this->createdLeads[] = $leadId;

        $contactIdOne = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;

        $this->assertTrue(
            $this->sb->getCRMScope()->leadContact()->add(
                $leadId,
                new ContactConnection($contactIdOne, 100, true)
            )->isSuccess()
        );

        $contactIdTwo = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->leadContact()->add(
                $leadId,
                new ContactConnection($contactIdTwo, 100, true)
            )->isSuccess()
        );

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->leadContact()->get($leadId)->getContactConnections();

        foreach ($connectedContacts as $item) {
            $this->assertContains($item->CONTACT_ID, $connectedId);
        }
    }

    public function testDeleteItems(): void
    {
        $leadId = $this->leadService->add((['TITLE' => 'test LeadContact Del items'])->build())->getId();
        $this->createdLeads[] = $leadId;

        $contactIdOne = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->leadContact()->setItems($leadId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->leadContact()->deleteItems($leadId)->isSuccess());

        $this->assertCount(0, $this->sb->getCRMScope()->leadContact()->get($leadId)->getContactConnections());
    }

    public function testDelete(): void
    {
        $leadId = $this->leadService->add((['TITLE' => 'test LeadContact Del'])->build())->getId();
        $this->createdLeads[] = $leadId;

        $contactIdOne = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->contactService->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->leadContact()->setItems($leadId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->leadContact()->delete($leadId, $contactIdTwo)->isSuccess());

        $this->assertCount(1, $this->sb->getCRMScope()->leadContact()->get($leadId)->getContactConnections());
    }

    public function testSetWithEmptyConnections(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        $this->sb->getCRMScope()->leadContact()->setItems(1, []);
    }

    public function testSetWithWrongType(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        /** @phpstan-ignore */
        $this->sb->getCRMScope()->leadContact()->setItems(1, [new \DateTime()]);
    }
}