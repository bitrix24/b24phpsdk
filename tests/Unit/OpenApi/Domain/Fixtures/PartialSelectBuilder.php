<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

use Bitrix24\SDK\Services\AbstractSelectBuilder;

/**
 * Only covers field: id (misses "title")
 */
class PartialSelectBuilder extends AbstractSelectBuilder
{
    public function __construct()
    {
        $this->select[] = 'id';
    }
}
