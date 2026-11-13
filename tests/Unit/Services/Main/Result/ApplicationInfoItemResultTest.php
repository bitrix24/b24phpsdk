<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Main\Result;

use Bitrix24\SDK\Services\Main\Result\ApplicationInfoItemResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApplicationInfoItemResult::class)]
class ApplicationInfoItemResultTest extends TestCase
{
    #[DataProvider('licenseFamilies')]
    public function testLicenseFamilyToleratesNewCodesAndPreservesRawMetadata(array $data, ?string $expectedFamily, ?string $expectedCode): void
    {
        $applicationInfoItemResult = new ApplicationInfoItemResult($data);

        self::assertSame($expectedFamily, $applicationInfoItemResult->LICENSE_FAMILY?->value);
        self::assertSame($expectedCode, $applicationInfoItemResult->getLicenseFamilyCode());
    }

    public static function licenseFamilies(): iterable
    {
        foreach (['free', 'basic', 'demo', 'std', 'pro', 'project', 'ent', 'nfr'] as $known) {
            yield $known => [['LICENSE_FAMILY' => $known], $known, $known];
        }

        yield 'future code' => [['LICENSE_FAMILY' => 'future_plan_2027'], 'unknown', 'future_plan_2027'];
        yield 'empty' => [['LICENSE_FAMILY' => ''], null, ''];
        yield 'null' => [['LICENSE_FAMILY' => null], null, null];
        yield 'missing' => [[], null, null];
    }

    public function testDoesNotTreatMalformedLicenseMetadataAsAnUnknownCode(): void
    {
        $applicationInfoItemResult = new ApplicationInfoItemResult(['LICENSE_FAMILY' => []]);
        $this->expectException(\TypeError::class);

        self::assertNotNull($applicationInfoItemResult->LICENSE_FAMILY);
    }
}
