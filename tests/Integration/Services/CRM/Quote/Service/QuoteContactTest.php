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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service;


use Bitrix24\SDK\Services\CRM\Common\ContactConnection;
use Bitrix24\SDK\Services\CRM\Quote\Service\QuoteContact;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\ContactBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(QuoteContact::class)]
#[CoversMethod(QuoteContact::class, 'fields')]
#[CoversMethod(QuoteContact::class, 'setItems')]
#[CoversMethod(QuoteContact::class, 'deleteItems')]
#[CoversMethod(QuoteContact::class, 'get')]
#[CoversMethod(QuoteContact::class, 'add')]
#[CoversMethod(QuoteContact::class, 'delete')]
class QuoteContactTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ServiceBuilder $sb;

    private array $createdQuotes = [];

    private array $createdContacts = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->sb->getCRMScope()->quote()->batch->delete($this->createdQuotes) as $result) {
            // ###
        }
        
        foreach ($this->sb->getCRMScope()->contact()->batch->delete($this->createdContacts) as $result) {
            // ###
        }
    }

    public function testSet(): void
    {
        $quoteId = $this->sb->getCRMScope()->quote()->add(['TITLE' => 'test quote'])->getId();
        $this->createdQuotes[] = $quoteId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->sb->getCRMScope()->quoteContact()->setItems($quoteId, [
            new ContactConnection($contactIdOne, 100, true),
            new ContactConnection($contactIdTwo, 100, false),
        ]);

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->quoteContact()->get($quoteId)->getContactConnections();

        foreach ($connectedContacts as $connectedContact) {
            $this->assertContains($connectedContact->CONTACT_ID, $connectedId);
        }
    }

    public function testAdd(): void
    {
        $quoteId = $this->sb->getCRMScope()->quote()->add(['TITLE' => 'test quote'])->getId();
        $this->createdQuotes[] = $quoteId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;

        $this->assertTrue(
            $this->sb->getCRMScope()->quoteContact()->add(
                $quoteId,
                new ContactConnection($contactIdOne, 100, true)
            )->isSuccess()
        );

        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->quoteContact()->add(
                $quoteId,
                new ContactConnection($contactIdTwo, 100, true)
            )->isSuccess()
        );

        $connectedId = [$contactIdOne, $contactIdTwo];
        $connectedContacts = $this->sb->getCRMScope()->quoteContact()->get($quoteId)->getContactConnections();

        foreach ($connectedContacts as $connectedContact) {
            $this->assertContains($connectedContact->CONTACT_ID, $connectedId);
        }
    }

    public function testDeleteItems(): void
    {
        $quoteId = $this->sb->getCRMScope()->quote()->add(['TITLE' => 'test quote'])->getId();
        $this->createdQuotes[] = $quoteId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->quoteContact()->setItems($quoteId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->quoteContact()->deleteItems($quoteId)->isSuccess());

        $this->assertCount(0, $this->sb->getCRMScope()->quoteContact()->get($quoteId)->getContactConnections());
    }

    public function testDelete(): void
    {
        $quoteId = $this->sb->getCRMScope()->quote()->add(['TITLE' => 'test quote'])->getId();
        $this->createdQuotes[] = $quoteId;

        $contactIdOne = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdOne;
        $contactIdTwo = $this->sb->getCRMScope()->contact()->add((new ContactBuilder())->build())->getId();
        $this->createdContacts[] = $contactIdTwo;

        $this->assertTrue(
            $this->sb->getCRMScope()->quoteContact()->setItems($quoteId, [
                new ContactConnection($contactIdOne, 100, true),
                new ContactConnection($contactIdTwo, 100, false),
            ])->isSuccess()
        );

        $this->assertTrue($this->sb->getCRMScope()->quoteContact()->delete($quoteId, $contactIdTwo)->isSuccess());

        $this->assertCount(1, $this->sb->getCRMScope()->quoteContact()->get($quoteId)->getContactConnections());
    }

    public function testSetWithEmptyConnections(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        $this->sb->getCRMScope()->quoteContact()->setItems(1, []);
    }

    public function testSetWithWrongType(): void
    {
        $this->expectException(Core\Exceptions\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $this->sb->getCRMScope()->quoteContact()->setItems(1, [new \DateTime()]);
    }
}