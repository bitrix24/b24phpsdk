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

namespace Bitrix24\SDK\Services\Landing\Block\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RepositoryBlocksResult extends AbstractResult
{
    /**
     * @return RepositoryBlockItemResult[]
     * @throws BaseException
     */
    public function getRepositoryBlocks(): array
    {
        echo "\n getRepositoryBlocks \n";
        print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n";

        $result = [];
        $rawData = $this->getCoreResponse()->getResponseData()->getResult();

        foreach ($rawData as $sectionData) {
            $result[] = new RepositoryBlockItemResult($sectionData);
        }

        return $result;
    }
}
