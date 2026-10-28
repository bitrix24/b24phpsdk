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

namespace Bitrix24\SDK\Tests\Unit\Services\Note\Document\Service;

use Bitrix24\SDK\Services\Note\Document\Result\DocumentState;
use Bitrix24\SDK\Services\Note\Document\Service\DocumentFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentFilter::class)]
class DocumentFilterTest extends TestCase
{
    #[Test]
    public function testBuildsRestV3ConditionList(): void
    {
        $filter = new DocumentFilter();
        $filter->collectionId()->eq(46);
        $filter->parentId()->eq(90);
        $filter->id()->in([1, 2]);
        $filter->state()->in([DocumentState::archived->value, DocumentState::trashed->value]);

        $this->assertSame(
            [
                ['collectionId', '=', 46],
                ['parentId', '=', 90],
                ['id', 'in', [1, 2]],
                ['state', 'in', ['archived', 'trashed']],
            ],
            $filter->toArray()
        );
    }
}
