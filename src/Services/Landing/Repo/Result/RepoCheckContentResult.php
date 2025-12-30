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

namespace Bitrix24\SDK\Services\Landing\Repo\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class RepoCheckContentResult extends AbstractResult
{
    /**
     * Get the checked content with dangerous parts marked
     *
     * @throws BaseException
     */
    public function getContent(): ?string
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // The API returns an object with the content and is_bad fields.
        return $result['content'] ?? null;
    }

    /**
     * Check if content contains dangerous substrings
     *
     * @throws BaseException
     */
    public function isBad(): bool
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        // The API returns a boolean flag is_bad
        return isset($result['is_bad']) && (bool)$result['is_bad'];
    }
}
