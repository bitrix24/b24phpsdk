<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IMOpenLines\Config\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class OptionsResult
 *
 * Result class for imopenlines.config.list.get
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Config\Result
 */
class OptionsResult extends AbstractResult
{
    /**
     * Get a list of open lines
     *
     * @return OptionItemResult[]
     * @throws BaseException
     */
    public function getOptions(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        $options = [];
        foreach ($result as $data) {
            $options[] = new OptionItemResult($data);
        }
        
        return $options;
    }
}
