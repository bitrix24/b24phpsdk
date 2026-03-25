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

namespace Bitrix24\SDK\Tests\Integration\Services\Task\ChatMessageField\Result;

use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldItemResult;
use Bitrix24\SDK\Services\Task\ChatMessageField\Service\ChatMessageField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatMessageFieldItemResult::class)]
class ChatMessageFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private ChatMessageField $chatMessageFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->chatMessageFieldService = Factory::getServiceBuilder()->getTaskScope()->taskChatMessageField();
    }

    #[Test]
    #[TestDox('all fields in ChatMessageFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        // get first entity field from documentation
        $fieldNameForTest = 'taskId';
        $allFields = $this->chatMessageFieldService->get($fieldNameForTest)->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), ChatMessageFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in ChatMessageFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $chatMessageFieldItemResult = $this->chatMessageFieldService->get('taskId')->chatMessageField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($chatMessageFieldItemResult, ChatMessageFieldItemResult::class);
    }
}
