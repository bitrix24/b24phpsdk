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

namespace Bitrix24\SDK\Services\Disk\File\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * File get result
 */
class FileResult extends AbstractResult
{
    /**
     * Get file item
     *
     * @throws BaseException
     */
    public function file(): FileItemResult
    {
        return new FileItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
