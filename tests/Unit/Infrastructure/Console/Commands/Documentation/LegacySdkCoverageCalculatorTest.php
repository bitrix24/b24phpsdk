<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Documentation;

use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Infrastructure\Console\Commands\Documentation\LegacySdkCoverageCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacySdkCoverageCalculator::class)]
class LegacySdkCoverageCalculatorTest extends TestCase
{
    #[DataProvider('coverageCases')]
    public function testCoverageUsesUniqueLegacyMethodIdentities(array $portal, array $metadata, int $total, array $covered, array $uncovered, array $sdkOnly, float $percentage): void
    {
        $methods = [];
        foreach ($metadata as [$name, $version]) {
            $methods[] = new SupportedInSdkApiMethod('task', $name, null, null, false, null, 'method', 'test.php', 1, 2, 'Example', $version, null, null, null);
        }

        $result = new LegacySdkCoverageCalculator()->calculate($portal, $methods);

        self::assertSame($total, $result->totalPortalMethods);
        self::assertSame($covered, $result->coveredMethods);
        self::assertSame($uncovered, $result->uncoveredMethods);
        self::assertSame($sdkOnly, $result->sdkOnlyMethods);
        self::assertSame($percentage, $result->coveragePercentage);
        self::assertSame($total, count($result->coveredMethods) + count($result->uncoveredMethods));
    }

    public static function coverageCases(): iterable
    {
        yield 'mixed versions' => [['a.get', 'b.get'], [['a.get', ApiVersion::v1], ['b.get', ApiVersion::v3], ['c.get', ApiVersion::v1]], 2, ['a.get'], ['b.get'], ['c.get'], 50.0];
        yield 'duplicates' => [['A.GET', 'a.get'], [['a.get', ApiVersion::v1], ['A.GET', ApiVersion::v1]], 1, ['a.get'], [], [], 100.0];
        yield 'case normalization' => [['im.chat.setowner'], [['im.chat.setOwner', ApiVersion::v1]], 1, ['im.chat.setowner'], [], [], 100.0];
        yield 'same name both versions' => [['tasks.task.get'], [['tasks.task.get', ApiVersion::v1], ['tasks.task.get', ApiVersion::v3]], 1, ['tasks.task.get'], [], [], 100.0];
        yield 'only v3' => [['tasks.task.get'], [['tasks.task.get', ApiVersion::v3]], 1, [], ['tasks.task.get'], [], 0.0];
        yield 'empty portal' => [[], [['a.get', ApiVersion::v1]], 0, [], [], ['a.get'], 0.0];
        yield 'empty sdk' => [['a.get'], [], 1, [], ['a.get'], [], 0.0];
        yield 'both empty' => [[], [], 0, [], [], [], 0.0];
        yield 'rounding and sorting' => [['c.get', 'a.get', 'b.get'], [['c.get', ApiVersion::v1], ['z.get', ApiVersion::v1], ['y.get', ApiVersion::v1]], 3, ['c.get'], ['a.get', 'b.get'], ['y.get', 'z.get'], 33.33];
    }
}
