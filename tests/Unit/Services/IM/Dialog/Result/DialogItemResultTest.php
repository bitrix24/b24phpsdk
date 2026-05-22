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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Dialog\Result;

use Bitrix24\SDK\Services\IM\Dialog\Result\DialogItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DialogItemResult::class)]
final class DialogItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    #[Test]
    public function testMagicGetterCastsAllAnnotatedFieldsAccordingToPhpDoc(): void
    {
        $dialogItemResult = new DialogItemResult([
            'id'                 => '4304',
            'parent_chat_id'     => '0',
            'parent_message_id'  => '0',
            'name'               => 'Dialog test',
            'description'        => null,
            'owner'              => '1',
            'extranet'           => false,
            'avatar'             => '',
            'color'              => '#64a513',
            'type'               => 'chat',
            'counter'            => '0',
            'user_counter'       => '1',
            'message_count'      => '2',
            'unread_id'          => '0',
            'restrictions'       => ['send' => true],
            'last_message_id'    => '1560108',
            'last_id'            => '1560108',
            'marked_id'          => '0',
            'disk_folder_id'     => '0',
            'entity_type'        => '',
            'entity_id'          => '',
            'entity_data_1'      => '',
            'entity_data_2'      => '',
            'entity_data_3'      => '',
            'mute_list'          => [],
            'date_create'        => '2026-04-29T12:34:56+00:00',
            'message_type'       => 'C',
            'public'             => '',
            'role'               => 'owner',
            'entity_link'        => ['type' => '', 'url' => '', 'id' => ''],
            'text_field_enabled' => true,
            'background_id'      => null,
            'permissions'        => ['can_post' => 'member'],
            'is_new'             => false,
            'readed_list'        => [],
            'manager_list'       => [1],
            'last_message_views' => ['message_id' => 1560108],
            'dialog_id'          => 'chat4304',
        ]);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($dialogItemResult, DialogItemResult::class);
    }
}
