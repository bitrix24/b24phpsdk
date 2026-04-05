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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Service;

use Bitrix24\SDK\Services\AbstractSelectBuilder;
use Bitrix24\SDK\Services\Main\Result\EventLogItemResult;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Bitrix24\SDK\Tests\Unit\Services\SelectBuilderOaSchemaCoverageTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogSelectBuilder::class)]
class EventLogSelectBuilderTest extends TestCase
{
    use SelectBuilderOaSchemaCoverageTrait;

    #[\Override]
    protected function getItemResultClass(): string
    {
        return EventLogItemResult::class;
    }

    #[\Override]
    protected function getSelectBuilder(): AbstractSelectBuilder
    {
        return new EventLogSelectBuilder();
    }
}
