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

namespace Bitrix24\SDK\Services\Task\Commentitem\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CommentitemResult
 *
 * @package Bitrix24\SDK\Services\Task\Commentitem\Result
 */
class CommentitemResult extends AbstractResult
{
    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function commentitem(): CommentitemItemResult
    {
        return new CommentitemItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
