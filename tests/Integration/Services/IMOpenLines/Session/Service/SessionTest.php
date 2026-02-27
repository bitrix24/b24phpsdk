<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Session\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMOpenLines\Session\Service\Session;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class SessionTest
 *
 * Integration tests for IMOpenLines Session service
 * These tests require a working Bitrix24 portal with IMOpenLines configured and at least one active chat session.
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Session\Service
 */
#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
    private Session $sessionService;

    #[\Override]
    protected function setUp(): void
    {
        $this->sessionService = Factory::getServiceBuilder()->getIMOpenLinesScope()->session();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetDialogWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->getDialog(999999);
    }

    /**
     * Test pinAll method - should return array of session IDs or empty array
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testPinAll(): void
    {
        $pinAllResult = $this->sessionService->pinAll();

        $this->assertIsArray($pinAllResult->getPinnedSessionIds());
        // Result can be empty array if no sessions available, which is normal for test environment
    }

    /**
     * Test unpinAll method - should return array of session IDs or empty array
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testUnpinAll(): void
    {
        $unpinAllResult = $this->sessionService->unpinAll();

        $this->assertIsArray($unpinAllResult->getUnpinnedSessionIds());
        // Result can be empty array if no sessions were pinned, which is normal for test environment
    }

    /**
     * Test open method with invalid user code
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testOpenWithInvalidUserCode(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->open('invalid|user|code');
    }

    /**
     * Test session management methods with invalid chat ID - all should throw exceptions
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSessionMethodsWithInvalidChatId(): void
    {
        $invalidChatId = 999999;

        // Test createCrmLead
        $this->expectException(BaseException::class);
        $this->sessionService->createCrmLead($invalidChatId);
    }

    /**
     * Test startMessageSession with invalid parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testStartMessageSessionWithInvalidParameters(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->startMessageSession(999999, 999999);
    }

    /**
     * Test voteHead method with invalid session ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testVoteHeadWithInvalidSessionId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->voteHead(999999, 5, 'Test comment');
    }

    /**
     * Test getHistory with invalid parameters
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetHistoryWithInvalidParameters(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->getHistory(999999, 999999);
    }

    /**
     * Test pin method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testPinWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->pin(999999, true);
    }

    /**
     * Test setSilent method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSetSilentWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->setSilent(999999, true);
    }

    /**
     * Test intercept method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testInterceptWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->intercept(999999);
    }

    /**
     * Test join method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testJoinWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->join(999999);
    }

    /**
     * Test start method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testStartWithInvalidChatId(): void
    {
        $this->expectException(BaseException::class);
        $this->sessionService->start(999999);
    }
}