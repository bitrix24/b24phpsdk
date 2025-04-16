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

namespace Bitrix24\SDK\Services\CRM\Activity\Result\OpenLine;

class OpenLineProviderParams
{
    public function __construct(private readonly string $userCode)
    {
    }

    public function getUserCode(): string
    {
        return $this->userCode;
    }

    public function getBitrix24UserId(): int
    {
        return (int)explode('|', $this->getUserCode())[3];
    }

    public function getExternalSystemUserId(): string
    {
        return explode('|', $this->getUserCode())[2];
    }
}
