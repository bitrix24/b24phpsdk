<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

use Bitrix24\SDK\Services\AbstractSelectBuilder;

/**
 * Covers fields: id, title
 */
class ValidSelectBuilder extends AbstractSelectBuilder
{
    public function __construct()
    {
        $this->select[] = 'id';
    }

    public function title(): self
    {
        $this->select[] = 'title';
        return $this;
    }
}
