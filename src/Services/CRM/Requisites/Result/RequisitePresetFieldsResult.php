<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Bitrix24\SDK\Services\CRM\Requisites\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class RequisitePresetFieldsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Requisites\Result
 */
class RequisitePresetFieldsResult extends AbstractResult
{
    /**
     * @return RequisitePresetFieldItemResult[]
     * @throws BaseException
     */
    public function getPresetFields(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new RequisitePresetFieldItemResult($item);
        }

        return $items;
    }
}
