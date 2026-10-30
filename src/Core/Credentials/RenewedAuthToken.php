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

namespace Bitrix24\SDK\Core\Credentials;

// The alias is registered by the legacy DTO file; loading it defines this name.
\class_exists(\Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken::class);

// Never executed: lets Composer's classmap scanner (including --classmap-authoritative) find this file
// and gives static analysers the legacy DTO's shape. Replaced by a real class in 4.0, see #721.
if (false) { // @phpstan-ignore if.alwaysFalse
    readonly class RenewedAuthToken extends \Bitrix24\SDK\Core\Response\DTO\RenewedAuthToken
    {
    }
}
