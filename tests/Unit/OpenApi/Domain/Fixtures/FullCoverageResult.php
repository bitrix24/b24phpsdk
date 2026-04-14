<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

use Bitrix24\SDK\Attributes\OpenApiEntity;

#[OpenApiEntity(
    entityKey: 'test.fixture.fulldto',
    selectBuilder: ValidSelectBuilder::class,
    itemBuilder: ValidItemBuilder::class,
)]
class FullCoverageResult
{
}
