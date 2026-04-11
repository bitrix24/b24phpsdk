<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\Fixtures;

use Bitrix24\SDK\Attributes\OpenApiEntity;

#[OpenApiEntity(
    entityKey: 'test.fixture.nonexistentitemdto',
    selectBuilder: ValidSelectBuilder::class,
    itemBuilder: 'Bitrix24\\SDK\\Tests\\Unit\\OpenApi\\Domain\\Fixtures\\DoesNotExistItemBuilder',
)]
class NonExistentItemBuilderResult
{
}
