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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistory\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistory;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistorySelectBuilder;
use Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryResponseFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(UserHistory::class)]
class UserHistoryTest extends TestCase
{
    use UserHistoryResponseFixture;

    public function testListSendsRequiredIdentifierAndParsesItems(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with(
            'main.user.history.list',
            ['filter' => [['userId', '=', 7]]],
            ApiVersion::v3
        )->willReturn(self::response(['items' => [['id' => '42']]]));
        $items = (new UserHistory($core, new NullLogger()))->list(7)->getUserHistoryItems();
        self::assertCount(1, $items);
        self::assertSame(42, $items[0]->id);
    }

    public function testListMapsBuildersOrderAndPagination(): void
    {
        $filter = (new class () extends AbstractFilterBuilder {})->setRaw([['id', '>', 10]]);
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.list', [
            'filter' => [['userId', '=', 7], ['id', '>', 10]],
            'select' => ['id'],
            'order' => ['id' => 'DESC'],
            'pagination' => ['page' => 2, 'limit' => 3, 'offset' => 1],
        ], ApiVersion::v3)->willReturn(self::response(['items' => []]));
        $items = (new UserHistory($core, new NullLogger()))->list(
            7,
            (new UserHistorySelectBuilder()),
            $filter,
            ['id' => SortOrder::Descending],
            ['page' => 2, 'limit' => 3, 'offset' => 1]
        )->getUserHistoryItems();
        self::assertSame([], $items);
    }

    #[DataProvider('nestedFilters')]
    public function testAdditionalOrCannotReplaceRequiredIdentifier(array $input, array $expected): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.list', [
            'filter' => [['userId', '=', 7], ...$expected], 'select' => ['id'],
        ], ApiVersion::v3)->willReturn(self::response(['items' => []]));
        self::assertSame([], (new UserHistory($core, new NullLogger()))->list(7, ['id'], $input)->getUserHistoryItems());
    }

    public static function nestedFilters(): array
    {
        $group = ['logic' => 'or', 'conditions' => [['id', '=', 10], ['id', '=', 20]]];
        return ['list group' => [[$group], [$group]], 'root group' => [$group, [$group]]];
    }

    #[DataProvider('invalidIds')]
    public function testInvalidIdentifierDoesNotCallApi(int $id): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        (new UserHistory($core, new NullLogger()))->list($id);
    }

    public static function invalidIds(): array
    {
        return [[0], [-1]];
    }

    public function testFieldsReturnsUnmodifiedMetadata(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.field.list', [], ApiVersion::v3)
            ->willReturn(self::response(['items' => [['name' => 'id', 'type' => 'int']]]));
        self::assertSame(
            ['id' => ['name' => 'id', 'type' => 'int']],
            (new UserHistory($core, new NullLogger()))->fields()->getFieldsDescription()
        );
    }

    public function testTailMapsCursorAndRequiredUserFilter(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $cursor = ['field' => 'id', 'value' => '42'];
        $core->expects(self::once())->method('call')->with('main.user.history.tail', [
            'filter' => [['userId', '=', 7], ['id', '>', 40]],
            'select' => ['id'],
            'cursor' => ['field' => 'id', 'value' => 42, 'order' => 'ASC'],
        ], ApiVersion::v3)->willReturn(self::response(['items' => [['id' => 43]], 'cursor' => $cursor, 'hasMore' => true]));
        $filter = (new class () extends AbstractFilterBuilder {})->setRaw([['id', '>', 40]]);
        $result = (new UserHistory($core, new NullLogger()))->tail(
            7,
            new \Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor('42'),
            (new UserHistorySelectBuilder()),
            $filter
        );
        self::assertSame(43, $result->getUserHistoryItems()[0]->id);
        self::assertSame($cursor, $result->getCursor());
        self::assertTrue($result->hasMore());
    }

    public function testTailRejectsInvalidUserBeforeCallingApi(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $this->expectException(InvalidArgumentException::class);
        (new UserHistory($core, new NullLogger()))->tail(
            0, // @phpstan-ignore argument.type (Exercise the positive-ID guard.)
            new \Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor(0)
        );
    }
    public function testAllSystemFieldsAreSentAsAJsonArray(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.list', self::callback(
            static fn (array $parameters): bool => array_is_list($parameters['select'])
                && in_array('field', $parameters['select'], true)
                && $parameters['filter'] === [['userId', '=', 7]]
        ), ApiVersion::v3)->willReturn(self::response(['items' => []]));
        self::assertSame([], (new UserHistory($core, new NullLogger()))->list(
            7,
            (new UserHistorySelectBuilder())->allSystemFields()
        )->getUserHistoryItems());
    }
    public function testTailRepeatedSelectionsRemainAJsonArray(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.tail', [
            'filter' => [['userId', '=', 7]],
            'select' => ['id', 'field', 'dateInsert'],
            'cursor' => ['field' => 'id', 'value' => 0, 'order' => 'ASC'],
        ], ApiVersion::v3)->willReturn(self::response(['items' => [], 'cursor' => ['field' => 'id', 'value' => null], 'hasMore' => false]));
        $result = (new UserHistory($core, new NullLogger()))->tail(
            7,
            new \Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor(0),
            (new UserHistorySelectBuilder())->field()->field()->dateInsert()
        );
        self::assertSame([], $result->getUserHistoryItems());
        self::assertNull($result->getCursor()['value']);
    }
}
