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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\ChatMessageField\Service;

use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldItemResult;
use Bitrix24\SDK\Services\Task\ChatMessageField\Service\ChatMessageField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatMessageField::class)]
class ChatMessageFieldTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ChatMessageField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getTaskScope()->taskChatMessageField();
    }

    #[Test]
    public function testList(): void
    {
        $fields = $this->service->list()->getChatMessageFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    #[Test]
    public function testGet(): void
    {
        $field = $this->service->get('taskId')->chatMessageField();
        $this->assertNotEmpty($field->name);
        $this->assertNotEmpty($field->type);
        $this->assertNotEmpty($field->title);
    }

    #[Test]
    public function testAllFieldsAnnotated(): void
    {
        $rawItems = $this->service->list()->getCoreResponse()->getResponseData()->getResult()['items'];
        $this->assertNotEmpty($rawItems);
        $fieldCodesFromApi = array_keys($rawItems[0]);
        $this->assertBitrix24AllResultItemFieldsAnnotated($fieldCodesFromApi, ChatMessageFieldItemResult::class);
    }
}
