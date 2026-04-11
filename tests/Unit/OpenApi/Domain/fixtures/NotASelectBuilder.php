<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

/**
 * Does NOT extend AbstractSelectBuilder — used to test wrong base type detection
 */
class NotASelectBuilder
{
    public function buildSelect(): array
    {
        return ['id'];
    }
}
