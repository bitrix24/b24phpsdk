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

namespace Bitrix24\SDK\Tests\Unit\Services\Telephony\Voximplant\Callback\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\BatchOperationsInterface;
use Bitrix24\SDK\Core\Contracts\BulkItemsReaderInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Telephony\Voximplant\Callback\Service\Callback;
use Bitrix24\SDK\Services\Telephony\Voximplant\VoximplantServiceBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Callback::class)]
#[CoversClass(VoximplantServiceBuilder::class)]
class CallbackTest extends TestCase
{
    #[DataProvider('voiceProvider')]
    public function testStartMapsParametersAndReturnsCallResult(?string $voice): void
    {
        $parameters = ['FROM_LINE' => 'test-line', 'TO_NUMBER' => 'test-number', 'TEXT_TO_PRONOUNCE' => 'Callback requested'];
        if ($voice !== null) {
            $parameters['VOICE'] = $voice;
        }

        $response = $this->createStub(Response::class);
        $response->method('getResponseData')->willReturn(new ResponseData(['RESULT' => true, 'CALL_ID' => 'callback.test'], Time::initWithZeroValues(), new Pagination(null, null)));
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('voximplant.callback.start', $parameters)->willReturn($response);
        $result = new Callback($core, new NullLogger())->start('test-line', 'test-number', 'Callback requested', $voice);

        self::assertSame($response, $result->getCoreResponse());
        self::assertTrue($result->getCallResult()->RESULT);
        self::assertSame('callback.test', $result->getCallResult()->CALL_ID);
    }

    public static function voiceProvider(): array
    {
        return ['default voice' => [null], 'explicit voice' => ['enfemale']];
    }

    public function testStartPropagatesApiErrors(): void
    {
        $baseException = new BaseException('Could not find line');
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->willThrowException($baseException);
        $this->expectExceptionObject($baseException);

        new Callback($core, new NullLogger())->start('missing-line', '', '');
    }

    public function testBuilderCachesCallbackService(): void
    {
        $voximplantServiceBuilder = new VoximplantServiceBuilder($this->createStub(CoreInterface::class), $this->createStub(BatchOperationsInterface::class), $this->createStub(BulkItemsReaderInterface::class), new NullLogger());

        self::assertInstanceOf(Callback::class, $voximplantServiceBuilder->callback());
        self::assertSame($voximplantServiceBuilder->callback(), $voximplantServiceBuilder->callback());
    }
}
