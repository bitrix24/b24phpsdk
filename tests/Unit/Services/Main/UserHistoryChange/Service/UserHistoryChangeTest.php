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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryChange\Service;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Filters\AbstractFilterBuilder;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChange;
use Bitrix24\SDK\Services\Main\UserHistoryChange\Service\UserHistoryChangeSelectBuilder;
use Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryResponseFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(UserHistoryChange::class)]
class UserHistoryChangeTest extends TestCase
{
    use UserHistoryResponseFixture;

    public function testListSendsRequiredIdentifierAndParsesItems(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with(
            'main.user.history.fields.list',
            ['filter' => [['historyId', '=', 7]]],
            ApiVersion::v3
        )->willReturn(self::response(['items' => [['id' => '42']]]));
        $items = (new UserHistoryChange($core, new NullLogger()))->list(7)->getUserHistoryChanges();
        self::assertCount(1, $items);
        self::assertSame(42, $items[0]->id);
    }

    public function testListMapsBuildersOrderAndPagination(): void
    {
        $filter = (new class () extends AbstractFilterBuilder {})->setRaw([['id', '>', 10]]);
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.fields.list', [
            'filter' => [['historyId', '=', 7], ['id', '>', 10]],
            'select' => ['id'],
            'order' => ['id' => 'DESC'],
            'pagination' => ['page' => 2, 'limit' => 3, 'offset' => 1],
        ], ApiVersion::v3)->willReturn(self::response(['items' => []]));
        $items = (new UserHistoryChange($core, new NullLogger()))->list(
            7,
            (new UserHistoryChangeSelectBuilder()),
            $filter,
            ['id' => SortOrder::Descending],
            ['page' => 2, 'limit' => 3, 'offset' => 1]
        )->getUserHistoryChanges();
        self::assertSame([], $items);
    }

    #[DataProvider('nestedFilters')]
    public function testAdditionalOrCannotReplaceRequiredIdentifier(array $input, array $expected): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.fields.list', [
            'filter' => [['historyId', '=', 7], ...$expected], 'select' => ['id'],
        ], ApiVersion::v3)->willReturn(self::response(['items' => []]));
        self::assertSame([], (new UserHistoryChange($core, new NullLogger()))->list(7, ['id'], $input)->getUserHistoryChanges());
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
        (new UserHistoryChange($core, new NullLogger()))->list($id);
    }

    public static function invalidIds(): array
    {
        return [[0], [-1]];
    }

    public function testFieldsReturnsUnmodifiedMetadata(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.fields.field.list', [], ApiVersion::v3)
            ->willReturn(self::response(['items' => [['name' => 'id', 'type' => 'int']]]));
        self::assertSame(
            ['id' => ['name' => 'id', 'type' => 'int']],
            (new UserHistoryChange($core, new NullLogger()))->fields()->getFieldsDescription()
        );
    }
    public function testAllSystemFieldsAreSentAsAJsonArray(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('main.user.history.fields.list', self::callback(
            static fn (array $parameters): bool => array_is_list($parameters['select'])
                && in_array('field', $parameters['select'], true)
                && $parameters['filter'] === [['historyId', '=', 7]]
        ), ApiVersion::v3)->willReturn(self::response(['items' => []]));
        self::assertSame([], (new UserHistoryChange($core, new NullLogger()))->list(
            7,
            (new UserHistoryChangeSelectBuilder())->allSystemFields()
        )->getUserHistoryChanges());
    }
}
