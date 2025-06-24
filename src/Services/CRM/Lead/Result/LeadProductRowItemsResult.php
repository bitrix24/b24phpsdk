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

namespace Bitrix24\SDK\Services\CRM\Lead\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\AbstractResult;
use Money\Currency;

/**
 * Class LeadProductRowItemsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Lead\Result
 */
class LeadProductRowItemsResult extends AbstractResult
{
    public function __construct(Response $coreResponse, private readonly Currency $currency)
    {
        parent::__construct($coreResponse);
    }

    /**
     * @return LeadProductRowItemResult[]
     * @throws BaseException
     */
    public function getProductRows(): array
    {
        $res = [];
        if (!empty($this->getCoreResponse()->getResponseData()->getResult()['result']['rows'])) {
            foreach ($this->getCoreResponse()->getResponseData()->getResult()['result']['rows'] as $productRow) {
                $res[] = new LeadProductRowItemResult($productRow, $this->currency);
            }
        } else {
            foreach ($this->getCoreResponse()->getResponseData()->getResult() as $productRow) {
                $res[] = new LeadProductRowItemResult($productRow, $this->currency);
            }
        }

        return $res;
    }
}
