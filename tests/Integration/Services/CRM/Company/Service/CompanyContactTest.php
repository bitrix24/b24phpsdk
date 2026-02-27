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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Company\Service;


use Bitrix24\SDK\Services\CRM\Common\ContactConnection;
use Bitrix24\SDK\Services\CRM\Company\Service\CompanyContact;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\ContactBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(CompanyContact::class)]
#[CoversMethod(CompanyContact::class, 'fields')]
#[CoversMethod(CompanyContact::class, 'setItems')]
#[CoversMethod(CompanyContact::class, 'deleteItems')]
#[CoversMethod(CompanyContact::class, 'get')]
#[CoversMethod(CompanyContact::class, 'add')]
#[CoversMethod(CompanyContact::class, 'delete')]
class CompanyContactTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdCompanies = [];

    private array $createdContacts = [];

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
    }

    protected function tearDown(): void
    {
    }

    public function testSet(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->sb->getCRMScope()->companyContact()->setItems($companyId, [
            new ContactConnection($contactIdOne, 100, true),
            new ContactConnection($contactIdTwo, 100, false),
        ]);

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->companyContact()->get($companyId)->getContactConnections();

        foreach ($connectedContacts as $connectedContact) {
            $this->assertContains($connectedContact->CONTACT_ID, $connectedId);
        }
    }

    public function testAdd(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;

        $this->assertTrue(
            $this->sb->getCRMScope()->companyContact()->add(
                $companyId,
                new ContactConnection($contactIdOne, 100, true)
            )->isSuccess()
        );

        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->companyContact()->add(
                $companyId,
                new ContactConnection($contactIdTwo, 100, true)
            )->isSuccess()
        );

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->companyContact()->get($companyId)->getContactConnections();

        foreach ($connectedContacts as $connectedContact) {
            $this->assertContains($connectedContact->CONTACT_ID, $connectedId);
        }
    }

    public function testDeleteItems(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->companyContact()->setItems($companyId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->companyContact()->deleteItems($companyId)->isSuccess());

        $this->assertCount(0, $this->sb->getCRMScope()->companyContact()->get($companyId)->getContactConnections());
    }

    public function testDelete(): void
    {
        $companyId = $this->sb->getCRMScope()->company()->add((new CompanyBuilder())->build())->getId();
        $this->createdCompanies[] = $companyId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->companyContact()->setItems($companyId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->companyContact()->delete($companyId, $contactIdTwo)->isSuccess());

        $this->assertCount(1, $this->sb->getCRMScope()->companyContact()->get($companyId)->getContactConnections());
    }

    public function testSetWithEmptyConnections(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        $this->sb->getCRMScope()->companyContact()->setItems(1, []);
    }

    public function testSetWithWrongType(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        /** @phpstan-ignore */
        $this->sb->getCRMScope()->companyContact()->setItems(1, [new \DateTime()]);
    }
}