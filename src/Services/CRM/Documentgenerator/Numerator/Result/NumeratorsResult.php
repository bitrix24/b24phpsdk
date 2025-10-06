<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class NumeratorsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result
 */
class NumeratorsResult extends AbstractResult
{
    /**
     * @return NumeratorItemResult[]
     * @throws BaseException
     */
    public function getNumerators(): array
    {
        $items = [];
        $source = [];

        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!empty($result['numerators']) && is_array($result['numerators'])) {
            $source = $result['numerators'];
        } elseif (!empty($result['items']) && is_array($result['items'])) {
            $source = $result['items'];
        }

        foreach ($source as $item) {
            $items[] = new NumeratorItemResult($item);
        }

        return $items;
    }
}
