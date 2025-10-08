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
 * File versions result
 */
class FileVersionsResult extends AbstractResult
{
    /**
     * Get file versions
     *
     * @return FileItemResult[]
     * @throws BaseException
     */
    public function getVersions(): array
    {
        $versions = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $version) {
            $versions[] = new FileItemResult($version);
        }

        return $versions;
    }
}
