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

use Bitrix24\SDK\Services\SonetGroup\Common\MemberRole;
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
        $result = $this->sonetGroupService->getUsers($groupId);
        $rawMembers = $result->getCoreResponse()->getResponseData()->getResult();
        self::assertNotEmpty($rawMembers);
        $member = $result->getUsers()[0];
        // No field metadata endpoint exists: validate the uncast live response and its SDK conversion.
        self::assertIsNumeric($rawMembers[0]['USER_ID']);
        self::assertIsString($rawMembers[0]['ROLE']);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($member, SonetGroupUserItemResult::class);
        self::assertSame((int)$rawMembers[0]['USER_ID'], $member->USER_ID);
        self::assertSame(MemberRole::owner, $member->ROLE);
        self::assertSame($rawMembers[0]['ROLE'], $member->ROLE->value);
    }
}
