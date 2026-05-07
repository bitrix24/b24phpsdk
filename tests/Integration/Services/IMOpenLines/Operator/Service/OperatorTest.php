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

namespace Bitrix24\SDK\Tests\Integration\Services\IMOpenLines\Operator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Result\OperatorActionResult;
use Bitrix24\SDK\Services\IMOpenLines\Operator\Service\Operator;
<<<<<<< HEAD
use Bitrix24\SDK\Tests\Integration\Fabric;
=======
use Bitrix24\SDK\Tests\Integration\Factory;
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for IMOpenLines Operator service
<<<<<<< HEAD
 *
=======
 * 
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
 * Note: These tests have limitations because operator methods require:
 * - Active open line dialogs
 * - Specific dialog states (answered, unanswered)
 * - Operator permissions
 * - Real dialog participants
<<<<<<< HEAD
 *
=======
 * 
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
 * Most tests will be skipped if required conditions are not met.
 */
#[CoversClass(Operator::class)]
class OperatorTest extends TestCase
{
    private Operator $operatorService;

    #[\Override]
    protected function setUp(): void
    {
<<<<<<< HEAD
        $this->operatorService = Fabric::getServiceBuilder()->getIMOpenLinesScope()->operator();
=======
        $this->operatorService = Factory::getServiceBuilder()->getIMOpenLinesScope()->operator();
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
    }

    /**
     * Test answer method with invalid chat ID
     * This is the only reliable test since we can predict the failure
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAnswerWithInvalidChatId(): void
    {
        // Test with an obviously invalid chat ID
        $invalidChatId = 999999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->answer($invalidChatId);
    }

    /**
     * Test finish method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testFinishWithInvalidChatId(): void
    {
        $invalidChatId = 999999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->finish($invalidChatId);
    }

    /**
     * Test anotherFinish method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testAnotherFinishWithInvalidChatId(): void
    {
        $invalidChatId = 999999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->anotherFinish($invalidChatId);
    }

    /**
     * Test skip method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSkipWithInvalidChatId(): void
    {
        $invalidChatId = 999999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->skip($invalidChatId);
    }

    /**
     * Test spam method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testSpamWithInvalidChatId(): void
    {
        $invalidChatId = 999999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('chat_id');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->spam($invalidChatId);
    }

    /**
     * Test transfer method with invalid chat ID
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testTransferWithInvalidChatId(): void
    {
        $invalidChatId = 999999999;
        $invalidOperatorId = 999999;
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('operator_wrong');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('operator_wrong');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->transfer($invalidChatId, $invalidOperatorId);
    }

    /**
     * Test transfer method with queue format
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testTransferWithQueueFormat(): void
    {
        $invalidChatId = 999999999;
        $queueFormat = 'queue#123#';
<<<<<<< HEAD

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('queue_id_empty');

=======
        
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('queue_id_empty');
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        $this->operatorService->transfer($invalidChatId, $queueFormat);
    }

    /**
     * Test with real chat data if available
     * This test will be skipped if no real dialogs exist
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testWithRealChatIfAvailable(): void
    {
        // Try to get some existing open line configs to test with
        try {
<<<<<<< HEAD
            $configService = Fabric::getServiceBuilder()->getIMOpenLinesScope()->config();

            // Attempt to get config list - if this fails, skip real tests
            $optionsResult = $configService->getList(['ID'], ['ID' => 'ASC'], null, ['limit' => 1]);
            $options = $optionsResult->getOptions();

=======
            $configService = Factory::getServiceBuilder()->getIMOpenLinesScope()->config();
            
            // Attempt to get config list - if this fails, skip real tests
            $optionsResult = $configService->getList(['ID'], ['ID' => 'ASC'], null, ['limit' => 1]);
            $options = $optionsResult->getOptions();
            
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
            if ($options === []) {
                $this->markTestSkipped('No open line configurations available for testing. Real chat operations cannot be tested.');
            } else {
                // If we have configs, we could theoretically test, but it's still risky
                // because we might interfere with real dialogs
                $this->markTestSkipped('Open line configurations found, but testing with real dialogs is disabled to avoid disrupting actual conversations.');
            }
<<<<<<< HEAD

=======
            
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        } catch (\Exception) {
            $this->markTestSkipped('Unable to access open line configurations. Testing with real data is not possible.');
        }
    }

    /**
     * Test that all methods return proper result type
     * This tests the method signatures and return types without side effects
     *
     * @throws BaseException
     * @throws TransportException
     */
    public function testMethodReturnTypes(): void
    {
        $invalidChatId = 999999999;
<<<<<<< HEAD

=======
        
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
        // Test all methods throw appropriate exceptions for invalid parameters
        $methods = [
            'answer' => [$invalidChatId, 'chat_id'],
            'finish' => [$invalidChatId, 'chat_id'],
            'anotherFinish' => [$invalidChatId, 'chat_id'],
            'skip' => [$invalidChatId, 'chat_id'],
            'spam' => [$invalidChatId, 'chat_id'],
            'transfer' => [[$invalidChatId, 123], 'operator_wrong']
        ];

        foreach ($methods as $methodName => [$args, $expectedErrorCode]) {
            try {
                if (is_array($args)) {
                    $this->operatorService->$methodName(...$args);
                } else {
                    $this->operatorService->$methodName($args);
                }
<<<<<<< HEAD

=======
                
>>>>>>> 4e6e76c48dee212540ce7f8b740643014af953e6
                $this->fail(sprintf('Method %s should have thrown an exception for invalid parameters', $methodName));
            } catch (BaseException $e) {
                $this->assertStringContainsString(
                    $expectedErrorCode,
                    strtolower($e->getMessage()),
                    sprintf('Method %s should throw exception with error code %s', $methodName, $expectedErrorCode)
                );
            }
        }
    }
}