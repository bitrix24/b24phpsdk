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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman\NetworkRange\Result;

use Bitrix24\SDK\Services\Timeman\NetworkRange\Service\NetworkRange;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeItemResult;
use Bitrix24\SDK\Tests\Integration\Services\Timeman\ReadOnlyCoreTrait;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/** No fields endpoint exists: verify live keys and annotated getter casts. Empty lists are valid. */
#[CoversClass(NetworkRangeItemResult::class)]
class NetworkRangeItemResultAnnotationsTest extends TestCase
{
    use ReadOnlyCoreTrait;
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $networkRange = new NetworkRange($this->getReadOnlyCore(), new NullLogger());
        $result = $networkRange->get();
        $items = $result->getCoreResponse()->getResponseData()->getResult();
        self::assertIsArray($items);
        foreach ($items as $item) {
            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($item), NetworkRangeItemResult::class);
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $networkRange = new NetworkRange($this->getReadOnlyCore(), new NullLogger());
        $result = $networkRange->get();
        $items = $result->getRanges();
        self::assertIsArray($items);
        foreach ($items as $item) {
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, NetworkRangeItemResult::class);
        }
    }
}
