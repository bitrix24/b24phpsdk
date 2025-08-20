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

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class CommentitemsResult
 *
 * @package Bitrix24\SDK\Services\Task\Commentitem\Result
 */
class CommentitemsResult extends AbstractResult
{
    /**
     * @return CommentitemItemResult[]
     * @throws BaseException
     */
    public function getCommentitems(): array
    {
        $items = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
            $items[] = new CommentitemItemResult($item);
        }

        return $items;
    }
}
