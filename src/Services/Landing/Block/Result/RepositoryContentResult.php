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

class RepositoryContentResult extends AbstractResult
{
    /**
     * @return string HTML content from repository block
     * @throws BaseException
     */
    public function getContent(): string
    {
        return $this->getCoreResponse()->getResponseData()->getResult()[0];
    }
}
