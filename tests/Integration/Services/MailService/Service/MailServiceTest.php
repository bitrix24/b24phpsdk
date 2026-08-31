<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\MailService\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\MailService\Service\MailService;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailService::class)]
class MailServiceTest extends TestCase
{
    private MailService $mailService;

    private int $testMailServiceId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->mailService = Fabric::getServiceBuilder()->getMailServiceScope()->mailService();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->testMailServiceId > 0) {
            $this->mailService->delete($this->testMailServiceId);
            $this->testMailServiceId = 0;
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('add creates a new mail service and returns its ID')]
    public function testAdd(): void
    {
        $addedItemResult = $this->mailService->add(
            'SDK Test MailService',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        );
        $this->testMailServiceId = $addedItemResult->getId();

        self::assertGreaterThan(0, $this->testMailServiceId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('update modifies an existing mail service')]
    public function testUpdate(): void
    {
        $this->testMailServiceId = $this->mailService->add(
            'SDK Test MailService Update',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();

        $updatedName = 'SDK Test MailService Updated';
        $updatedItemResult = $this->mailService->update($this->testMailServiceId, ['NAME' => $updatedName]);

        self::assertTrue($updatedItemResult->isSuccess());

        $mailServiceItemResult = $this->mailService->get($this->testMailServiceId)->mailService();
        self::assertSame($updatedName, $mailServiceItemResult->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('get returns a mail service by its ID')]
    public function testGet(): void
    {
        $this->testMailServiceId = $this->mailService->add(
            'SDK Test MailService Get',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();

        $mailServiceItemResult = $this->mailService->get($this->testMailServiceId)->mailService();

        self::assertSame($this->testMailServiceId, $mailServiceItemResult->ID);
        self::assertSame('SDK Test MailService Get', $mailServiceItemResult->NAME);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('list returns active mail services')]
    public function testList(): void
    {
        $this->testMailServiceId = $this->mailService->add(
            'SDK Test MailService List',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();

        $items = $this->mailService->list()->getMailServices();

        self::assertIsArray($items);
        self::assertGreaterThanOrEqual(1, count($items));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('delete removes a mail service')]
    public function testDelete(): void
    {
        $id = $this->mailService->add(
            'SDK Test MailService Delete',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();

        $deletedItemResult = $this->mailService->delete($id);

        self::assertTrue($deletedItemResult->isSuccess());
        // prevent double-delete in tearDown
        $this->testMailServiceId = 0;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('fields returns localized field labels')]
    public function testFields(): void
    {
        $fields = $this->mailService->fields()->getFieldsDescription();

        self::assertIsArray($fields);
        self::assertArrayHasKey('ID', $fields);
        self::assertArrayHasKey('NAME', $fields);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('count returns number of active mail services')]
    public function testCount(): void
    {
        $before = $this->mailService->count();

        $this->testMailServiceId = $this->mailService->add(
            'SDK Test MailService Count',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();

        $after = $this->mailService->count();

        self::assertSame($before + 1, $after);
    }
}
