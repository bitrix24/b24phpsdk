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

use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistorySelectBuilder;
use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\SelectBuilderAssertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistorySelectBuilder::class)]
class UserHistorySelectBuilderTest extends TestCase
{
    public function testCoversSchema(): void
    {
        SelectBuilderAssertions::assertCoversOpenApiSchema(new UserHistorySelectBuilder(), UserHistoryItemResult::class);
    }

    public function testSelectsExplicitFields(): void
    {
        self::assertSame(['id', 'field'], (new UserHistorySelectBuilder())->field()->buildSelect());
    }
}
