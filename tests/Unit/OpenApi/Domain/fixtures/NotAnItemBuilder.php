<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

/**
 * Does NOT extend AbstractItemBuilder — used to test wrong base type detection
 */
class NotAnItemBuilder
{
    public function build(): array
    {
        return [];
    }
}
