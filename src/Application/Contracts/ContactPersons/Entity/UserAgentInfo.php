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

namespace Bitrix24\SDK\Application\Contracts\ContactPersons\Entity;

use Darsyn\IP\Version\Multi as IP;

readonly class UserAgentInfo
{
    public function __construct(
        public ?IP $ip,
        public ?string $userAgent = null,
        public ?string $referrer = null,
        public ?string $fingerprint = null,
    ) {
    }

    public function getUTMs(): UTMs
    {
        if ($this->referrer === null) {
            return new UTMs();
        }

        return UTMs::fromUrl($this->referrer);
    }
}
