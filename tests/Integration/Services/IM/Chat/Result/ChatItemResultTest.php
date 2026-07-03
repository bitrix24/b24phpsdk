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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Chat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatEntityType;
use Bitrix24\SDK\Services\IM\Chat\Result\ChatItemResult;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatItemResult::class)]
class ChatItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Chat $chatService;

    private int $createdChatId = 0;

    private string $entityId = '';

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->chatService = Factory::getServiceBuilder()->getIMScope()->chat();

        $currentUserId = (int)$this->chatService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->entityId = sprintf('ANNOTATION_%s', uniqid('', true));
        $this->createdChatId = $this->chatService->add(
            users: [$currentUserId],
            title: sprintf('Annotation test chat %s', uniqid('', true)),
            chatEntityType: ChatEntityType::Calendar,
            entityId: $this->entityId,
        )->getId();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function tearDown(): void
    {
        if ($this->createdChatId > 0) {
            try {
                $this->chatService->leave($this->createdChatId);
            } catch (BaseException) {
                // ignore: best-effort cleanup
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ChatItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->chatService->get(ChatEntityType::Calendar, $this->entityId)
            ->getCoreResponse()->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ChatItemResult::class,
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in ChatItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $chatItem = $this->chatService->get(ChatEntityType::Calendar, $this->entityId)->chat();

        $this->assertNotNull($chatItem);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $chatItem,
            ChatItemResult::class,
        );
    }
}
