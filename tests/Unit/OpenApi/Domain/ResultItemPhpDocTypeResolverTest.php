<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultFieldDescriptor;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemPhpDocTypeResolver;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemPhpDocTypeResolverTest extends TestCase
{
    #[Test]
    public function itMapsDateAndDateTimeStringsToCarbonImmutable(): void
    {
        $resolver = new ResultItemPhpDocTypeResolver();

        $this->assertSame(
            CarbonImmutable::class . '|null',
            $resolver->resolve(new ResultFieldDescriptor('date_create', 'string', 'date-time', true))
        );
        $this->assertSame(
            CarbonImmutable::class . '|null',
            $resolver->resolve(new ResultFieldDescriptor('birthday', 'string', 'date', true))
        );
    }

    #[Test]
    public function itKeepsPrimitiveAndContainerTypesUntouched(): void
    {
        $resolver = new ResultItemPhpDocTypeResolver();

        $this->assertSame('int', $resolver->resolve(new ResultFieldDescriptor('id', 'integer', null, false)));
        $this->assertSame('string|null', $resolver->resolve(new ResultFieldDescriptor('name', 'string', null, true)));
        $this->assertSame('array', $resolver->resolve(new ResultFieldDescriptor('items', 'array', null, false)));
        $this->assertSame('array|null', $resolver->resolve(new ResultFieldDescriptor('permissions', 'object', null, true)));
    }
}
