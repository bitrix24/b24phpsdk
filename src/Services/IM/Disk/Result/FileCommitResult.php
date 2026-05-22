<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Disk\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

final class FileCommitResult extends AbstractResult
{
    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function files(): array
    {
        $files = $this->payload()['FILES'] ?? [];

        return is_array($files) ? array_filter($files, 'is_array') : [];
    }

    /**
     * @return list<int>
     * @throws BaseException
     */
    public function diskIds(): array
    {
        $diskIds = $this->payload()['DISK_ID'] ?? [];

        if (!is_array($diskIds)) {
            return [];
        }

        return array_values(array_map('intval', $diskIds));
    }

    /**
     * @return array<string, array<string, mixed>>
     * @throws BaseException
     */
    public function fileModels(): array
    {
        $fileModels = $this->payload()['FILE_MODELS'] ?? [];

        return is_array($fileModels) ? array_filter($fileModels, 'is_array') : [];
    }

    /**
     * @throws BaseException
     */
    public function messageId(): ?int
    {
        $payload = $this->payload();
        if (isset($payload['MESSAGE_ID'])) {
            return (int)$payload['MESSAGE_ID'];
        }

        foreach ($this->fileModels() as $fileModel) {
            if (isset($fileModel['MESSAGE_ID'])) {
                return (int)$fileModel['MESSAGE_ID'];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     */
    private function payload(): array
    {
        $payload = $this->getCoreResponse()->getResponseData()->getResult();

        return is_array($payload) ? $payload : [];
    }
}
