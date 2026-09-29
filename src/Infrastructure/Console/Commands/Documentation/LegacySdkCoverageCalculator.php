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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Documentation;

use Bitrix24\SDK\Attributes\Services\SupportedInSdkApiMethod;
use Bitrix24\SDK\Core\Contracts\ApiVersion;

final readonly class LegacySdkCoverageCalculator
{
    /**
     * @param list<string> $portalMethods
     * @param list<SupportedInSdkApiMethod> $sdkMethods
     */
    public function calculate(array $portalMethods, array $sdkMethods): LegacySdkCoverageResult
    {
        $portalMethods = $this->normalize($portalMethods);
        $legacyNames = [];
        foreach ($sdkMethods as $sdkMethod) {
            if ($sdkMethod->apiVersion === ApiVersion::v1) {
                $legacyNames[] = $sdkMethod->name;
            }
        }
        $legacyNames = $this->normalize($legacyNames);
        $covered = array_values(array_intersect($portalMethods, $legacyNames));

        return new LegacySdkCoverageResult(
            count($portalMethods),
            $covered,
            array_values(array_diff($portalMethods, $legacyNames)),
            array_values(array_diff($legacyNames, $portalMethods)),
            $portalMethods === [] ? 0.0 : round(100 * count($covered) / count($portalMethods), 2),
        );
    }

    /**
     * @param list<string> $methods
     * @return list<string>
     */
    private function normalize(array $methods): array
    {
        $methods = array_values(array_unique(array_map(strtolower(...), $methods)));
        sort($methods);

        return $methods;
    }
}
