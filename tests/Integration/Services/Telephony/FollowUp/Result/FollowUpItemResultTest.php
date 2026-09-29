<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Telephony\FollowUp\Result;

use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpItemResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUp;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListPagination;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FollowUpItemResult::class)]
class FollowUpItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private FollowUp $followUpService;

    #[\Override]
    protected function setUp(): void
    {
        $this->followUpService = Factory::getServiceBuilder()->getTelephonyScope()->followUp();
    }

    private function firstCallId(): ?int
    {
        $followUps = $this->followUpService->list(
            [
                'startDate' => [
                    'from' => CarbonImmutable::now()->subYear()->toIso8601ZuluString(),
                    'to'   => CarbonImmutable::now()->toIso8601ZuluString(),
                ],
            ],
            [],
            ['startDate' => 'desc'],
            new FollowUpListPagination(1)
        )->getFollowUps();

        return $followUps === [] ? null : $followUps[0]->callId;
    }

    #[Test]
    #[TestDox('all fields in FollowUpItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $callId = $this->firstCallId();
        if ($callId === null) {
            $this->markTestSkipped('No Follow-ups available on this portal.');
        }

        $allFields = $this->followUpService->get($callId)->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), FollowUpItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in FollowUpItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $callId = $this->firstCallId();
        if ($callId === null) {
            $this->markTestSkipped('No Follow-ups available on this portal.');
        }

        $followUpItemResult = $this->followUpService->get($callId)->followUp();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($followUpItemResult, FollowUpItemResult::class);
    }
}
