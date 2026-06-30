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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\RecordField\Result;

use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldItemResult;
use Bitrix24\SDK\Services\Timeman\RecordField\Service\RecordField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordFieldItemResult::class)]
class RecordFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private RecordField $recordFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->recordFieldService = Factory::getServiceBuilder()->getTimemanScope()->recordField();
    }

    #[Test]
    #[TestDox('all fields in RecordFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $fieldNameForTest = 'startTime';
        $allFields = $this->recordFieldService->get($fieldNameForTest)->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), RecordFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in RecordFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $recordFieldItemResult = $this->recordFieldService->get('startTime')->recordField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($recordFieldItemResult, RecordFieldItemResult::class);
    }
}
