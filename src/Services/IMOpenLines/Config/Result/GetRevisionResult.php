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
 * Class GetRevisionResult
 *
 * Result class for imopenlines.revision.get
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Config\Result
 */
class GetRevisionResult extends AbstractResult
{
    /**
     * Return a list of revisions for rest, web, mobile
     *
     * @throws BaseException
     */
    public function revision(): RevisionItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        
        echo "\n\n GetRevisionResult \n";
            print_r($this->getCoreResponse()->getResponseData()->getResult());
        echo "\n\n";

        return new RevisionItemResult($result);
    }
}
