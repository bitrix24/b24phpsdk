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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\Result;

use Bitrix24\SDK\Services\Main\UserHistory\Result\UserHistoryItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryItemResult::class)]
class UserHistoryItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;
    use HistoryFixture;

    public function testAllFieldsAreAnnotated(): void
    {
        $item = $this->historyItem();
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys(iterator_to_array($item)), UserHistoryItemResult::class);
    }

    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($this->historyItem(), UserHistoryItemResult::class);
    }
}
