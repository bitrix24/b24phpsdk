<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

use Bitrix24\SDK\Services\AbstractItemBuilder;

class ValidItemBuilder extends AbstractItemBuilder
{
    public function withTitle(string $title): self
    {
        $this->fields['title'] = $title;
        return $this;
    }
}
