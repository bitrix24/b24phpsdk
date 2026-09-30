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

namespace Bitrix24\SDK\Tests\Integration\Services\Telephony\FollowUpField\Result;

use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldItemResult;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Service\FollowUpField;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(FollowUpFieldItemResult::class)]
class FollowUpFieldItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private FollowUpField $followUpFieldService;

    #[\Override]
    protected function setUp(): void
    {
        $this->followUpFieldService = Factory::getServiceBuilder()->getTelephonyScope()->followUpField();
    }

    #[Test]
    #[TestDox('all fields in FollowUpFieldItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $fieldNameForTest = 'callId';
        $allFields = $this->followUpFieldService->get($fieldNameForTest)->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($allFields), FollowUpFieldItemResult::class);
    }

    #[Test]
    #[TestDox('all fields in FollowUpFieldItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $followUpFieldItemResult = $this->followUpFieldService->get('callId')->field();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($followUpFieldItemResult, FollowUpFieldItemResult::class);
    }
}
