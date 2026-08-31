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

final class FileSaveResult extends AbstractResult
{
    /**
     * @return array<string, mixed>
     * @throws BaseException
     */
    public function folder(): array
    {
        $folder = $this->payload()['folder'] ?? [];

        return is_array($folder) ? $folder : [];
    }

    /**
     * @return array<string, mixed>
     * @throws BaseException
     */
    public function file(): array
    {
        $file = $this->payload()['file'] ?? [];

        return is_array($file) ? $file : [];
    }

    /**
     * @throws BaseException
     */
    public function folderId(): ?int
    {
        $folder = $this->folder();

        return isset($folder['id']) ? (int)$folder['id'] : null;
    }

    /**
     * @throws BaseException
     */
    public function fileId(): ?int
    {
        $file = $this->file();

        return isset($file['id']) ? (int)$file['id'] : null;
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
