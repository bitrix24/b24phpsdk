<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem;

use Bitrix24\SDK\CodeGenerator\ResultItemCodeGenerator;
use Bitrix24\SDK\OpenApi\Domain\ResultItem\Field\ResultFieldDescriptor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemCodeGeneratorTest extends TestCase
{
    #[Test]
    public function itGeneratesPropertyAnnotationsAndCarbonImportForTemporalFields(): void
    {
        $resultItemCodeGenerator = new ResultItemCodeGenerator();
        $code = $resultItemCodeGenerator->generate(
            'Bitrix24\\SDK\\Services\\IM\\Dialog\\Result',
            'DialogItemResult',
            [
                new ResultFieldDescriptor('id', 'integer', null, false),
                new ResultFieldDescriptor('date_create', 'string', 'date-time', true),
                new ResultFieldDescriptor('birthday', 'string', 'date', true),
                new ResultFieldDescriptor('permissions', 'object', null, true),
            ],
            'im.dialog.get'
        );

        $this->assertStringContainsString('use Bitrix24\SDK\Core\Result\AbstractItem;', $code);
        $this->assertStringContainsString('use Carbon\CarbonImmutable;', $code);
        $this->assertStringContainsString('@property-read int $id', $code);
        $this->assertStringContainsString('@property-read CarbonImmutable|null $date_create', $code);
        $this->assertStringContainsString('@property-read CarbonImmutable|null $birthday', $code);
        $this->assertStringContainsString('@property-read array|null $permissions', $code);
        $this->assertStringContainsString('class DialogItemResult extends AbstractItem', $code);
    }
}
