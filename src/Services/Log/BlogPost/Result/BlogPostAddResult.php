<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Log\BlogPost\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class BlogPostAddResult extends AbstractResult
{
    public function getId(): int
    {
        return (int)$this->getCoreResponse()->getResponseData()->getResult()[0];
    }

    /**
     * Check if blog post was added successfully
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    public function isSuccess(): bool
    {
        return (bool)$this->getCoreResponse()->getResponseData()->getResult();
    }
}
