<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Pull\Channel\Result;

use Bitrix24\SDK\Services\Pull\Channel\Result\PublicChannelItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\Pull\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Pull has no fields endpoint. Validate both live get/list raw contracts instead.
 * Never include channel IDs or signatures in assertion output.
 */
#[CoversClass(PublicChannelItemResult::class)]
class PublicChannelItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        foreach ($this->rawChannels() as $raw) {
            $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw), PublicChannelItemResult::class);
        }
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        foreach ($this->rawChannels() as $raw) {
            self::assertIsInt($raw['user_id']);
            foreach (['public_id','signature','start','end'] as $field) {
                self::assertTrue(is_string($raw[$field]), 'Expected a string field: '.$field);
            }

            $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(new PublicChannelItemResult($raw), PublicChannelItemResult::class);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function rawChannels(): array
    {
        $channel = Factory::getServiceBuilder()->getPullScope()->channel();
        $raw = $channel->get()->getCoreResponse()->getResponseData()->getResult();
        $list = $channel->list([(int)$raw['user_id']])->getCoreResponse()->getResponseData()->getResult();
        self::assertCount(1, $list);
        return [$raw, ...array_values($list)];
    }
}
