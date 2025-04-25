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

namespace Bitrix24\SDK\Services\CRM\Activity\Result\WebForm;

class WebFormMetadata
{
    public function __construct(private readonly bool $isUsedUserConsent, private readonly array $agreements, private readonly string $ip, private readonly string $link)
    {
    }

    public function isUsedUserConsent(): bool
    {
        return $this->isUsedUserConsent;
    }

    public function getAgreements(): array
    {
        return $this->agreements;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
