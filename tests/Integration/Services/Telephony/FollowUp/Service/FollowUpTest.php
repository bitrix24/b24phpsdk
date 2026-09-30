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

namespace Bitrix24\SDK\Tests\Integration\Services\Telephony\FollowUp\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Telephony\FollowUp\Result\FollowUpItemResult;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUp;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListCursor;
use Bitrix24\SDK\Services\Telephony\FollowUp\Service\FollowUpListPagination;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FollowUp::class)]
class FollowUpTest extends TestCase
{
    private FollowUp $followUpService;

    /**
     * @return array{startDate: array{from: string, to: string}}
     */
    private function lastYearFilter(): array
    {
        return [
            'startDate' => [
                'from' => CarbonImmutable::now()->subYear()->toIso8601ZuluString(),
                'to'   => CarbonImmutable::now()->toIso8601ZuluString(),
            ],
        ];
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->followUpService = Factory::getServiceBuilder()->getTelephonyScope()->followUp();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('call.followup.list returns a cursor-paginated list of Follow-ups for a period')]
    public function testListWithCursor(): void
    {
        $result = $this->followUpService->list($this->lastYearFilter(), [], ['startDate' => 'desc'], new FollowUpListPagination(1));

        $followUps = $result->getFollowUps();
        $this->assertIsArray($followUps);

        $nextCursor = $result->getNextCursor();
        if ($nextCursor instanceof FollowUpListCursor) {
            $secondPage = $this->followUpService->list($this->lastYearFilter(), [], ['startDate' => 'desc'], new FollowUpListPagination(1, $nextCursor));
            $this->assertIsArray($secondPage->getFollowUps());
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('call.followup.get returns a single Follow-up by callId')]
    public function testGet(): void
    {
        $followUps = $this->followUpService->list($this->lastYearFilter(), [], ['startDate' => 'desc'], new FollowUpListPagination(1))
            ->getFollowUps();

        if ($followUps === []) {
            $this->markTestSkipped('No Follow-ups available on this portal.');
        }

        $callId = $followUps[0]->callId;

        $followUp = $this->followUpService->get($callId)->followUp();

        $this->assertInstanceOf(FollowUpItemResult::class, $followUp);
        $this->assertSame($callId, $followUp->callId);
    }
}
