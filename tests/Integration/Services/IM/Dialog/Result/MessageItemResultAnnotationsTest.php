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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Dialog\Result\MessageItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(MessageItemResult::class)]
#[CoversMethod(Dialog::class, 'messagesGet')]
#[CoversMethod(Dialog::class, 'messagesSearch')]
final class MessageItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsAnnotated(): void
    {
        [$messagesGetItem, $messagesSearchItem] = $this->getMessageItems();
        $fieldCodes = array_values(array_unique(array_merge(
            array_keys(iterator_to_array($messagesGetItem)),
            array_keys(iterator_to_array($messagesSearchItem))
        )));

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            $fieldCodes,
            MessageItemResult::class
        );
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        [$messagesGetItem, $messagesSearchItem] = $this->getMessageItems();

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $messagesGetItem,
            MessageItemResult::class
        );
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $messagesSearchItem,
            MessageItemResult::class
        );
    }

    /**
     * @return array{MessageItemResult, MessageItemResult}
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getMessageItems(): array
    {
        $chatId = $this->createChat();
        $dialogId = $this->createDialogId($chatId);
        $needle = sprintf('annotation-%s', uniqid('', true));
        $this->seedMessages($dialogId, [$needle]);

        $messagesGetItem = $this->dialogService->messagesGet($dialogId, null, null, 10)->messages()[0] ?? null;
        $messagesSearchItem = $this->dialogService->messagesSearch($chatId, 'annotation', limit: 20)->messages()[0] ?? null;

        self::assertInstanceOf(MessageItemResult::class, $messagesGetItem);
        self::assertInstanceOf(MessageItemResult::class, $messagesSearchItem);

        return [$messagesGetItem, $messagesSearchItem];
    }
}
