<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Result;

use Bitrix24\SDK\Services\CRM\CallList\Result\CallListItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\CallListTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/** No field metadata endpoint exists for call lists; validate raw live fields and casting. */
#[CoversClass(CallListItemResult::class)]
class CallListItemResultAnnotationsTest extends CallListTestCase
{
    use CustomBitrix24Assertions;

    public function testAllFieldsAreAnnotated(): void
    {
        $raw = $this->callList->get(self::$listId)->getCoreResponse()->getResponseData()->getResult();
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw), CallListItemResult::class);
    }

    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $item = $this->callList->get(self::$listId)->callList();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, CallListItemResult::class);
    }
}
