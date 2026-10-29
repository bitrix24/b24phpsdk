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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\File\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldItemResult;
use Bitrix24\SDK\Services\Note\File\Result\FileFieldsResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(FileFieldsResult::class)]
class FileFieldsResultTest extends TestCase
{
    #[Test]
    public function testDescriptorsAreIndexedWithoutChangingMetadata(): void
    {
        $fields = [['name' => 'id', 'type' => 'integer', 'isReadOnly' => true, 'future' => ['value' => null]], ['name' => 'createdAt', 'type' => 'datetime']];
        $result = $this->createResult($fields);

        $this->assertSame(['id' => $fields[0], 'createdAt' => $fields[1]], $result->getFieldsDescription());
        $this->assertCount(2, $result->getFields());
        $this->assertInstanceOf(FileFieldItemResult::class, $result->getFields()[0]);
        $this->assertSame('id', $result->getFields()[0]->name);
        $this->assertSame([], $this->createResult([])->getFieldsDescription());
    }

    #[Test]
    #[DataProvider('invalidFieldNames')]
    public function testDescriptorsRejectMissingOrInvalidNames(array $descriptor): void
    {
        $this->expectException(LogicException::class);
        $this->createResult([$descriptor])->getFieldsDescription();
    }

    public static function invalidFieldNames(): array
    {
        return [
            'missing' => [[]],
            'null' => [['name' => null]],
            'empty string' => [['name' => '']],
            'integer' => [['name' => 0]],
            'array' => [['name' => []]],
        ];
    }

    private function createResult(array $items): FileFieldsResult
    {
        return new FileFieldsResult(new Response((new MockHttpClient(new MockResponse(json_encode(['result' => ['items' => $items]], JSON_THROW_ON_ERROR))))->request('POST', 'https://example.test'), new Command('note.file.field.list', []), new ApiLevelErrorHandler(new NullLogger()), new NullLogger()));
    }
}
