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

namespace Bitrix24\SDK\Services\Lists\Element\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Class FileUrlsResult
 *
 * @package Bitrix24\SDK\Services\Lists\Element\Result
 */
class FileUrlsResult extends AbstractResult
{
    /**
     * Get array of file download URLs
     *
     * @return string[]
     * @throws BaseException
     */
    public function getFileUrls(): array
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!is_array($result)) {
            return [];
        }

        return array_filter($result, fn ($url): bool => is_string($url) && ($url !== '' && $url !== '0'));
    }

    /**
     * Get first file URL
     *
     * @throws BaseException
     */
    public function getFirstFileUrl(): ?string
    {
        $urls = $this->getFileUrls();
        return $urls[0] ?? null;
    }
}
