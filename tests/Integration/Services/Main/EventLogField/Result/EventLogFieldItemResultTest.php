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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\EventLogField\Result;

use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldItemResult;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogFieldItemResult::class)]
class EventLogFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private EventLogField $eventLogFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventLogFieldService = Factory::getServiceBuilder()->getMainScope()->eventLogField();
    }

    #[Test]
    #[TestDox('all fields in EventLogFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $allFields = $this->eventLogFieldService->get('timestampX')
            ->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), EventLogFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in EventLogFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $eventLogFieldItemResult = $this->eventLogFieldService->get('timestampX')->eventLogField();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $eventLogFieldItemResult,
            EventLogFieldItemResult::class
        );
    }
}
