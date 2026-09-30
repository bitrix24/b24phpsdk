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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldItemResult;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldsResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(EventLogFieldsResult::class)]
#[CoversClass(EventLogFieldItemResult::class)]
class EventLogFieldsResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testIndexesRawDescriptorsByName(): void
    {
        $fields = [['name' => 'id', 'type' => 'int', 'filterable' => true], ['name' => 'timestampX', 'type' => 'datetime']];
        $result = $this->makeResult($fields);
        self::assertSame(['id' => $fields[0], 'timestampX' => $fields[1]], $result->getFieldsDescription());
        self::assertSame('id', $result->getEventLogFields()[0]->name);
        self::assertSame([], $this->makeResult([])->getFieldsDescription());
    }

    public function testDescriptorCastsFlagsAndCoversEditableGroups(): void
    {
        $data = ['name' => 'id', 'type' => 'int', 'title' => 'ID', 'description' => null, 'validationRules' => null, 'requiredGroups' => [], 'filterable' => 'Y', 'sortable' => '1', 'editable' => 'N', 'editableGroups' => [], 'multiple' => '0', 'elementType' => null];
        $eventLogFieldItemResult = new EventLogFieldItemResult($data);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($data), EventLogFieldItemResult::class);
        self::assertTrue($eventLogFieldItemResult->filterable);
        self::assertTrue($eventLogFieldItemResult->sortable);
        self::assertFalse($eventLogFieldItemResult->editable);
        self::assertFalse($eventLogFieldItemResult->multiple);
        self::assertSame([], $eventLogFieldItemResult->editableGroups);
        self::assertNull((new EventLogFieldItemResult(['name' => 'id']))->editableGroups);
        self::assertSame($data, iterator_to_array($eventLogFieldItemResult));
    }

    #[DataProvider('missingNames')]
    public function testKeyedMetadataRejectsDescriptorsWithoutNames(array $descriptor): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Select the name field to index event log field descriptions.');
        $this->makeResult([$descriptor])->getFieldsDescription();
    }

    public static function missingNames(): iterable
    {
        yield 'unselected name' => [['type' => 'string']];
        yield 'empty name' => [['name' => '', 'type' => 'string']];
        yield 'null name' => [['name' => null, 'type' => 'string']];
    }

    private function makeResult(array $items): EventLogFieldsResult
    {
        return new EventLogFieldsResult(new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => ['items' => $items]])))->request('POST', 'https://example.test'),
            new Command('main.eventlog.field.list', []), new ApiLevelErrorHandler(new NullLogger()), new NullLogger()
        ));
    }
}
