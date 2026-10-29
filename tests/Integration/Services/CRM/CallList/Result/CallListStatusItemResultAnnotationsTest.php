<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Result;

use Bitrix24\SDK\Services\CRM\CallList\Result\CallListStatusItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** No field metadata endpoint exists for call-list statuses. */
#[CoversClass(CallListStatusItemResult::class)]
class CallListStatusItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CallList $callList;

    #[\Override]
    protected function setUp(): void
    {
        $this->callList = Factory::getServiceBuilder()->getCRMScope()->callList();
    }

    public function testAllSystemFieldsAnnotated(): void
    {
        $raw = $this->callList->statusList()->getCoreResponse()->getResponseData()->getResult();
        self::assertNotEmpty($raw);
        foreach ($raw as $item) {
            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($item), CallListStatusItemResult::class);
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $items = $this->callList->statusList()->getStatuses();
        self::assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, CallListStatusItemResult::class);
        }
    }
}
