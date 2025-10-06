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

use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Exceptions\BaseException;

/**
 * Class AddedNumeratorResult
 *
 * @package Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result
 */
class AddedNumeratorResult extends AddedItemResult
{
    /**
     * @throws BaseException
     */
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()['numerator']['id'];
    }
}
