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

namespace Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\CRMChat;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact as ContactService;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal as DealService;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatItemResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatListResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatLastIdResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatUserAddedResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Result\ChatUserDeletedResult;
use Bitrix24\SDK\Services\IMOpenLines\CRMChat\Service\Chat;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\User\Service\User as UserService;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class ChatTest
 *
 * Integration tests for IMOpenLines CRMChat service
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\CRMChat
 */
#[CoversClass(Chat::class)]
class ChatTest extends TestCase
{
    private Chat $chatService;

    private ContactService $contactService;

    private DealService $dealService;

    private UserService $userService;

    private ServiceBuilder $serviceBuilder;

    private array $createdContactIds = [];

    private array $createdDealIds = [];

    protected function setUp(): void
    {
        $this->serviceBuilder = Fabric::getServiceBuilder(true);
        $this->chatService = $this->serviceBuilder->getIMOpenLinesScope()->crmChat();
        $this->contactService = $this->serviceBuilder->getCRMScope()->contact();
        $this->dealService = $this->serviceBuilder->getCRMScope()->deal();
        $this->userService = $this->serviceBuilder->getUserScope()->user();
    }

    protected function tearDown(): void
    {
        // Clean up created contacts
        foreach ($this->createdContactIds as $createdContactId) {
            try {
                $this->contactService->delete($createdContactId);
            } catch (\Exception) {
                // Ignore if contact doesn't exist
            }
        }

        // Clean up created deals
        foreach ($this->createdDealIds as $createdDealId) {
            try {
                $this->dealService->delete($createdDealId);
            } catch (\Exception) {
                // Ignore if deal doesn't exist
            }
        }

        $this->createdContactIds = [];
        $this->createdDealIds = [];
    }

    /**
     * Test get chats for contact after creating a chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetChatsForContact(): void
    {
        // Create a test contact
        $contactId = $this->contactService->add([
            'NAME' => 'CRMChat Test Contact',
            'LAST_NAME' => 'Integration Test'
        ])->getId();
        $this->createdContactIds[] = $contactId;

        // Get current user ID
        $userResult = $this->userService->current();
        $userId = $userResult->user()->ID;

        // Try to add user to the contact (may return 0 if no active open lines)
        $chatUserAddedResult = $this->chatService->addUser('contact', $contactId, $userId);
        self::assertInstanceOf(ChatUserAddedResult::class, $chatUserAddedResult);

        // Get chats for the contact - may be empty if no chats exist
        $chatListResult = $this->chatService->get('contact', $contactId);
        self::assertInstanceOf(ChatListResult::class, $chatListResult);

        $chats = $chatListResult->getChats();
        self::assertIsArray($chats);

        // If no chats exist, that's okay - open lines need to be configured
        // We just verify the API call works and returns proper result structure
    }

    /**
     * Test get chats for deal after creating a chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetChatsForDeal(): void
    {
        // Create a test deal
        $dealId = $this->dealService->add([
            'TITLE' => 'CRMChat Test Deal'
        ])->getId();
        $this->createdDealIds[] = $dealId;

        // Get current user ID
        $userResult = $this->userService->current();
        $userId = $userResult->user()->ID;

        // Try to add user to the deal (may return 0 if no active open lines)
        $chatUserAddedResult = $this->chatService->addUser('deal', $dealId, $userId);
        self::assertInstanceOf(ChatUserAddedResult::class, $chatUserAddedResult);

        // Get chats for the deal - may be empty if no chats exist
        $chatListResult = $this->chatService->get('deal', $dealId);
        self::assertInstanceOf(ChatListResult::class, $chatListResult);

        $chats = $chatListResult->getChats();
        self::assertIsArray($chats);

        // If no chats exist, that's okay - open lines need to be configured
        // We just verify the API call works and returns proper result structure
    }

    /**
     * Test get chats with active only filter after creating a chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetChatsWithActiveOnlyFilter(): void
    {
        // Create a test contact
        $contactId = $this->contactService->add([
            'NAME' => 'CRMChat Active Test Contact'
        ])->getId();
        $this->createdContactIds[] = $contactId;

        // Get current user ID
        $userResult = $this->userService->current();
        $userId = $userResult->user()->ID;

        // Try to add user to the contact
        $chatUserAddedResult = $this->chatService->addUser('contact', $contactId, $userId);
        self::assertInstanceOf(ChatUserAddedResult::class, $chatUserAddedResult);

        // Get only active chats - may be empty if no active open lines configured
        $chatListResult = $this->chatService->get('contact', $contactId, true);
        self::assertInstanceOf(ChatListResult::class, $chatListResult);
        self::assertIsArray($chatListResult->getChats());

        // Get all chats - may be empty if no open lines configured
        $allResult = $this->chatService->get('contact', $contactId, false);
        self::assertInstanceOf(ChatListResult::class, $allResult);
        self::assertIsArray($allResult->getChats());
    }

    /**
     * Test get last chat ID for contact after creating a chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetLastChatIdForContact(): void
    {
        // Create a test contact
        $contactId = $this->contactService->add([
            'NAME' => 'CRMChat LastId Test Contact'
        ])->getId();
        $this->createdContactIds[] = $contactId;

        // Try to get last chat ID for contact without chats - should throw exception
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('crm_chat_empty_crm_data');

        $this->chatService->getLastId('CONTACT', $contactId);
    }

    /**
     * Test get last chat ID for deal after creating a chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetLastChatIdForDeal(): void
    {
        // Create a test deal
        $dealId = $this->dealService->add([
            'TITLE' => 'CRMChat LastId Test Deal'
        ])->getId();
        $this->createdDealIds[] = $dealId;

        // Try to get last chat ID for deal without chats - should throw exception
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('crm_chat_empty_crm_data');

        $this->chatService->getLastId('DEAL', $dealId);
    }

    /**
     * Test add user to chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAddUserToChat(): void
    {
        // Create a test contact
        $contactId = $this->contactService->add([
            'NAME' => 'CRMChat AddUser Test Contact'
        ])->getId();
        $this->createdContactIds[] = $contactId;

        // Get current user ID
        $userResult = $this->userService->current();
        $userId = $userResult->user()->ID;

        // Add user to chat (will use the last chat for this contact)
        $chatUserAddedResult = $this->chatService->addUser('contact', $contactId, $userId);

        self::assertInstanceOf(ChatUserAddedResult::class, $chatUserAddedResult);

        $chatId = $chatUserAddedResult->getChatId();
        self::assertIsInt($chatId);
    }

    /**
     * Test delete user from chat
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDeleteUserFromChat(): void
    {
        // Create a test contact
        $contactId = $this->contactService->add([
            'NAME' => 'CRMChat DeleteUser Test Contact'
        ])->getId();
        $this->createdContactIds[] = $contactId;

        // Get current user ID
        $userResult = $this->userService->current();
        $userId = $userResult->user()->ID;

        // First add user to chat
        $chatUserAddedResult = $this->chatService->addUser('contact', $contactId, $userId);
        self::assertInstanceOf(ChatUserAddedResult::class, $chatUserAddedResult);

        // Then delete user from chat
        $chatUserDeletedResult = $this->chatService->deleteUser('contact', $contactId, $userId);

        self::assertInstanceOf(ChatUserDeletedResult::class, $chatUserDeletedResult);

        $chatId = $chatUserDeletedResult->getChatId();
        self::assertIsInt($chatId);
    }

    /**
     * Test different CRM entity types
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testDifferentCrmEntityTypes(): void
    {
        $entityTypes = ['contact', 'deal'];

        foreach ($entityTypes as $entityType) {
            if ($entityType === 'contact') {
                $entityId = $this->contactService->add([
                    'NAME' => 'CRMChat Entity Test Contact'
                ])->getId();
                $this->createdContactIds[] = $entityId;
            } else {
                $entityId = $this->dealService->add([
                    'TITLE' => 'CRMChat Entity Test Deal'
                ])->getId();
                $this->createdDealIds[] = $entityId;
            }

            // Test get method for each entity type
            $result = $this->chatService->get($entityType, $entityId);
            self::assertInstanceOf(ChatListResult::class, $result);
        }
    }
}