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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Template\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Template\Result\TemplateItemResult;
use Bitrix24\SDK\Services\Landing\Template\Service\Batch;
use Bitrix24\SDK\Services\Landing\Template\Service\Template;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
class BatchTest extends TestCase
{
    private Template $templateService;

    #[\Override]
    protected function setUp(): void
    {
        $this->templateService = Fabric::getServiceBuilder(true)->getLandingScope()->template();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns the same templates as a single call')]
    public function testBatchList(): void
    {
        $expectedIds = array_map(
            static fn(TemplateItemResult $templateItemResult): int => (int)$templateItemResult->ID,
            $this->templateService->getList(['ID'], [], ['ID' => 'ASC'])->getTemplates()
        );

        $foundIds = [];
        foreach ($this->templateService->batch->list(['ID', 'TITLE']) as $templateItemResult) {
            self::assertInstanceOf(TemplateItemResult::class, $templateItemResult);
            $foundIds[] = (int)$templateItemResult->ID;
        }

        self::assertNotEmpty($foundIds);
        self::assertSame($expectedIds, $foundIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list respects filter, order and limit')]
    public function testBatchListWithFilterOrderAndLimit(): void
    {
        $foundIds = [];
        foreach ($this->templateService->batch->list(['ID'], ['=ACTIVE' => 'Y'], ['ID' => 'DESC'], 2) as $templateItemResult) {
            $foundIds[] = (int)$templateItemResult->ID;
        }

        self::assertLessThanOrEqual(2, count($foundIds));
        $sortedIds = $foundIds;
        rsort($sortedIds);
        self::assertSame($sortedIds, $foundIds);
    }
}
