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

namespace Bitrix24\SDK\Application\Local\Repository;

use Bitrix24\SDK\Application\Local\Entity\LocalAppAuth;
use Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken;

interface LocalAppAuthRepositoryInterface
{
    public function get(): LocalAppAuth;

    public function saveRenewedToken(RenewedAuthToken $renewedAuthToken): void;

    public function save(LocalAppAuth $localAppAuth): void;
}