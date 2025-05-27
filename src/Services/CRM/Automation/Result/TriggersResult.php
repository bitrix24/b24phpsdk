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

namespace Bitrix24\SDK\Services\CRM\Automation\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class TriggersResult
 *
 * @package Bitrix24\SDK\Services\CRM\Automation\Result
 */
class TriggersResult extends AbstractResult
{
    /**
     * @return TriggerItemResult[]
     * @throws BaseException
     */
    public function getTriggers(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new TriggerItemResult($item);
        }

        return $items;
    }

    /**
     * @throws BaseException
     */
    public function getTriggersArray(): array
    {
        return $this->getCoreResponse()->getResponseData()->getResult();
    }
}
