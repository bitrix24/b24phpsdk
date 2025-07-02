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

namespace Bitrix24\SDK\Services\CRM\Timeline\Comment\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CommentsResult
 *
 * @package Bitrix24\SDK\Services\CRM\Timeline\Comment\Result
 */
class CommentsResult extends AbstractResult
{
    /**
     * @return CommentItemResult[]
     * @throws BaseException
     */
    public function getComments(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new CommentItemResult($item);
        }

        return $items;
    }
}
