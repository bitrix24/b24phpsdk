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

namespace Bitrix24\SDK\Tests\Integration\Services\SonetGroup\Result;

use Bitrix24\SDK\Services\SonetGroup\Result\SonetGroupUserItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\SonetGroup\SonetGroupFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SonetGroupUserItemResult::class)]
final class SonetGroupUserItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;
    use SonetGroupFixture;

    public function testAllSystemFieldsAnnotated(): void
    {
        $groupId = $this->createTestGroup()->getId();
        $raw = $this->sonetGroupService->getUsers($groupId)->getCoreResponse()->getResponseData()->getResult();
        self::assertNotEmpty($raw);
        // This API has no fields endpoint; use the actual system keys from its live response.
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw[0]), SonetGroupUserItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $groupId = $this->createTestGroup()->getId();
        $member = $this->sonetGroupService->getUsers($groupId)->getUsers()[0];
        // Documented Bitrix24 field types; there is no live fields metadata endpoint.
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation([
            'USER_ID' => ['type' => 'integer'],
            'ROLE' => ['type' => 'string'],
        ], SonetGroupUserItemResult::class);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($member, SonetGroupUserItemResult::class);
        self::assertIsInt($member->USER_ID);
        self::assertIsString($member->ROLE);
        self::assertSame('A', $member->ROLE);
    }
}
