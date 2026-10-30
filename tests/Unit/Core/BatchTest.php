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

namespace Bitrix24\SDK\Tests\Unit\Core;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    #[Test]
    #[TestDox('batch sub-commands keep null parameters as empty values instead of dropping them')]
    public function testBatchSubCommandsKeepNullParameters(): void
    {
        $capturedCommands = [];
        $batch = new Batch($this->makeCoreCapturingBatchCommands($capturedCommands), new NullLogger());

        try {
            iterator_to_array($batch->addEntityItems('crm.item.list', [
                ['entityTypeId' => 2, 'filter' => ['companyId' => null, 'nested' => ['value' => null]], 'select' => ['id']],
            ]));
        } catch (\Throwable) {
            // processing of the stub batch response is irrelevant — assertion is on the captured cmd payload
        }

        $this->assertNotEmpty($capturedCommands);
        $capturedCommand = urldecode((string)array_shift($capturedCommands));
        $this->assertStringContainsString('filter[companyId]=&', $capturedCommand);
        $this->assertStringContainsString('filter[nested][value]=&', $capturedCommand);
    }

    /**
     * Builds a CoreInterface stub that records the cmd payload passed to the «batch» call.
     *
     * @param array<string, string> $capturedCommands captured by reference
     */
    private function makeCoreCapturingBatchCommands(array &$capturedCommands): CoreInterface
    {
        $response = new Response(
            new MockResponse(''),
            new Command('batch', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );

        $core = $this->createStub(CoreInterface::class);
        $core->method('call')->willReturnCallback(
            function (string $apiMethod, array $parameters = []) use (&$capturedCommands, $response): Response {
                if ($apiMethod === 'batch') {
                    $capturedCommands = $parameters['cmd'];
                }

                return $response;
            }
        );

        return $core;
    }
}
