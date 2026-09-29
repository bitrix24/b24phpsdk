<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Catalog\RoundingRule\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RoundingRulesResult extends AbstractResult
{
    /**
     * @return RoundingRuleItemResult[]
     * @throws BaseException
     */
    public function getRoundingRules(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        return array_map(
            static fn (array $item): RoundingRuleItemResult => new RoundingRuleItemResult($item),
            $result['roundingRules'] ?? []
        );
    }
}
