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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Placements;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangItem;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlacementLangMap::class)]
#[CoversClass(PlacementLangItem::class)]
#[CoversClass(LangCodes::class)]
class PlacementLangMapTest extends TestCase
{
    #[Test]
    public function testToArrayBuildsExpectedPayload(): void
    {
        $placementLangMap = PlacementLangMap::empty()
            ->with(
                LangCodes::EN,
                new PlacementLangItem(
                    title: 'title',
                    description: 'description',
                    groupName: 'group',
                ),
            )
            ->with(
                LangCodes::RU,
                new PlacementLangItem(
                    title: 'заголовок',
                    description: 'описание',
                    groupName: 'группа',
                ),
            );

        $this->assertSame(
            [
                'en' => [
                    'TITLE' => 'title',
                    'DESCRIPTION' => 'description',
                    'GROUP_NAME' => 'group',
                ],
                'ru' => [
                    'TITLE' => 'заголовок',
                    'DESCRIPTION' => 'описание',
                    'GROUP_NAME' => 'группа',
                ],
            ],
            $placementLangMap->toArray(),
        );
    }

    #[Test]
    public function testToArrayOmitsOptionalFieldsWhenNotProvided(): void
    {
        $placementLangMap = PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('title'));

        $this->assertSame(
            [
                'en' => [
                    'TITLE' => 'title',
                ],
            ],
            $placementLangMap->toArray(),
        );
    }
}
