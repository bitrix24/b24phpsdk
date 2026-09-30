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

namespace Bitrix24\SDK\Tests\Unit\Services\SonetGroup;

use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\SonetGroup\Service\SonetGroup;
use Bitrix24\SDK\Tests\Integration\Services\SonetGroup\SonetGroupFixture;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversNothing]
final class SonetGroupFixtureTest extends TestCase
{
    use SonetGroupFixture {
        tearDown as private cleanupFixtures;
    }

    private SonetGroup&MockObject $sonetGroupMock;

    protected function setUp(): void
    {
        $this->sonetGroupMock = $this->createMock(SonetGroup::class);
        $this->sonetGroupService = $this->sonetGroupMock;
    }

    protected function tearDown(): void
    {
        // Each test explicitly invokes cleanup against mocks, never live credentials.
    }

    public function testCleanupDeletesOnlyCreatedIdsWithoutSearchingPortal(): void
    {
        $added = $this->createStub(AddedItemResult::class);
        $added->method('getId')->willReturn(321);
        $deleted = $this->createStub(DeletedItemResult::class);
        $deleted->method('isSuccess')->willReturn(true);
        $this->sonetGroupMock->expects($this->once())->method('create')
            ->with($this->callback(static fn (array $fields): bool => $fields['VISIBLE'] === 'N'))
            ->willReturn($added);
        $this->sonetGroupMock->expects($this->never())->method('getGroups');
        $this->sonetGroupMock->expects($this->once())->method('delete')->with(321)->willReturn($deleted);
        $this->createTestGroup(['VISIBLE' => 'Y']);
        $this->cleanupFixtures();
        $this->cleanupFixtures();
    }

    public function testRejectsDeletionOfAnUnownedGroup(): void
    {
        $this->sonetGroupMock->expects($this->never())->method('delete');
        $this->expectException(AssertionFailedError::class);
        $this->deleteTestGroup(999);
    }
}
