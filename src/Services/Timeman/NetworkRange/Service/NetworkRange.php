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

namespace Bitrix24\SDK\Services\Timeman\NetworkRange\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeCheckResult;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangeSetResult;
use Bitrix24\SDK\Services\Timeman\NetworkRange\Result\NetworkRangesResult;

#[ApiServiceMetadata(new Scope(['timeman']))]
class NetworkRange extends AbstractService
{
    /**
     * Checks whether an IP belongs to an office network range.
     */
    #[ApiEndpointMetadata('timeman.networkrange.check', 'https://apidocs.bitrix24.com/api-reference/timeman/networkrange/timeman-networkrange-check.html', 'Checks whether an IP belongs to an office network range.')]
    public function check(?string $ip = null): NetworkRangeCheckResult
    {
        $params = $ip === null ? [] : ['IP' => $ip];

        return new NetworkRangeCheckResult($this->core->call('timeman.networkrange.check', $params));
    }

    /**
     * Returns the configured office network ranges.
     */
    #[ApiEndpointMetadata('timeman.networkrange.get', 'https://apidocs.bitrix24.com/api-reference/timeman/networkrange/timeman-networkrange-get.html', 'Returns the configured office network ranges.')]
    public function get(): NetworkRangesResult
    {
        $params = [];

        return new NetworkRangesResult($this->core->call('timeman.networkrange.get', $params));
    }

    /**
     * Replaces all office network ranges.
     * @param list<array{ip_range: string, name: string}> $ranges Replaces the complete portal-wide list.
     */
    #[ApiEndpointMetadata('timeman.networkrange.set', 'https://apidocs.bitrix24.com/api-reference/timeman/networkrange/timeman-networkrange-set.html', 'Replaces all office network ranges.')]
    public function set(array $ranges): NetworkRangeSetResult
    {
        $params = ['RANGES' => $ranges];

        return new NetworkRangeSetResult($this->core->call('timeman.networkrange.set', $params));
    }

}
