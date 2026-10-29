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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Document\Result;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Note\Document\Result\UnarchivedDocumentResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(UnarchivedDocumentResult::class)]
class UnarchivedDocumentResultTest extends TestCase
{
    #[Test]
    public function testReadsNestedBooleanResult(): void
    {
        $this->assertTrue((new UnarchivedDocumentResult($this->response(['result' => true])))->isSuccess());
        $this->assertFalse((new UnarchivedDocumentResult($this->response(['result' => false])))->isSuccess());
    }

    private function response(array $result): Response
    {
        return new Response((new MockHttpClient(new MockResponse(json_encode(['result' => $result], JSON_THROW_ON_ERROR))))->request('POST', 'https://example.test'), new Command('note.document.unarchive', []), new ApiLevelErrorHandler(new NullLogger()), new NullLogger());
    }
}
