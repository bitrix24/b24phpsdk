<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\PhpDoc;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\PhpDoc\ResultItemPhpDocTypeResolver;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemPhpDocTypeResolverTest extends TestCase
{
    #[Test]
    public function itMapsDateAndDateTimeStringsToCarbonImmutable(): void
    {
        $resultItemPhpDocTypeResolver = new ResultItemPhpDocTypeResolver();

        $this->assertSame(
            CarbonImmutable::class . '|null',
            $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('date_create', 'string', 'date-time', true))
        );
        $this->assertSame(
            CarbonImmutable::class . '|null',
            $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('birthday', 'string', 'date', true))
        );
    }

    #[Test]
    public function itKeepsPrimitiveAndContainerTypesUntouched(): void
    {
        $resultItemPhpDocTypeResolver = new ResultItemPhpDocTypeResolver();

        $this->assertSame('int', $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('id', 'integer', null, false)));
        $this->assertSame('string|null', $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('name', 'string', null, true)));
        $this->assertSame('array', $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('items', 'array', null, false)));
        $this->assertSame('array|null', $resultItemPhpDocTypeResolver->resolve(new ResultFieldDescriptor('permissions', 'object', null, true)));
    }
}
