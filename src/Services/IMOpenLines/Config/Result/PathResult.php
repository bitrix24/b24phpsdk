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

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class PathResult
 *
 * Result class for imopenlines.config.delete
 *
 * @package Bitrix24\SDK\Services\IMOpenLines\Config\Result
 */
class PathResult extends AbstractResult
{
    /**
     * Gets a link to the public page of open lines in the account
     */
    public function getPath(): string
    {
        $path = '';
        $result = $this->getCoreResponse()->getResponseData()->getResult();
        if (!empty($result['SERVER_ADDRESS'])) {
            $path .= $result['SERVER_ADDRESS'].$result['PUBLIC_PATH'];
        }

        return $path;
    }
}
