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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\Record\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Timeman\Record\Result\RecordItemResult;
use Bitrix24\SDK\Services\Timeman\Record\Service\Record;
use Bitrix24\SDK\Services\Timeman\Record\Service\RecordFilter;
use Bitrix24\SDK\Services\Timeman\Record\Service\RecordSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordItemResult::class)]
class RecordItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Record $recordService;

    private int $currentUserId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder();
        $this->recordService = $serviceBuilder->getTimemanScope()->record();
        $this->currentUserId = (int)$serviceBuilder->getUserScope()->user()->current()->user()->ID;

        // ensure at least one work-time record exists for the current user
        $timemanService = $serviceBuilder->getTimemanScope()->timeman();
        $timemanService->open();
        $timemanService->close();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RecordItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $items = $this->recordService->list(
            (new RecordSelectBuilder())->allSystemFields(),
            (new RecordFilter())->userId()->eq($this->currentUserId),
            [],
            ['limit' => 1]
        )->getCoreResponse()->getResponseData()->getResult()['items'];

        if ($items === []) {
            $this->markTestSkipped('Portal has no work-time records for the current user; cannot validate annotations.');
        }

        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($items[0]), RecordItemResult::class);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('all fields in RecordItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $records = $this->recordService->list(
            (new RecordSelectBuilder())->allSystemFields(),
            (new RecordFilter())->userId()->eq($this->currentUserId),
            [],
            ['limit' => 1]
        )->getRecords();

        if ($records === []) {
            $this->markTestSkipped('Portal has no work-time records for the current user; cannot validate type casting.');
        }

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($records[0], RecordItemResult::class);
    }
}
