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

namespace Bitrix24\SDK\Services\Documentgenerator\Numerator\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class NumeratorResult
 *
 * @package Bitrix24\SDK\Services\Documentgenerator\Numerator\Result
 */
class NumeratorResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function numerator(): NumeratorItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!empty($result['numerator']) && is_array($result['numerator'])) {
            $result = $result['numerator'];
        }

        return new NumeratorItemResult($result);
    }
}

