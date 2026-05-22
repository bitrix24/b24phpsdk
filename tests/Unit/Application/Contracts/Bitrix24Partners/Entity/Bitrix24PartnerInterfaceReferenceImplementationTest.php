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

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\Bitrix24Partners\Entity;

use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerStatus;
use Bitrix24\SDK\Tests\Application\Contracts\Bitrix24Partners\Entity\Bitrix24PartnerInterfaceTest;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Bitrix24PartnerInterface::class)]
class Bitrix24PartnerInterfaceReferenceImplementationTest extends Bitrix24PartnerInterfaceTest
{
    #[\Override]
    protected function createBitrix24PartnerImplementation(
        Uuid                  $uuid,
        Bitrix24PartnerStatus $bitrix24PartnerStatus,
        string                $title,
        int                   $bitrix24PartnerId,
        ?string               $site,
        ?PhoneNumber          $phoneNumber,
        ?string               $email,
        ?string               $openLineId,
        ?string               $externalId,
        ?string               $logoUrl = null
    ): Bitrix24PartnerInterface {
        return new Bitrix24PartnerReferenceEntityImplementation(
            $uuid,
            $bitrix24PartnerStatus,
            $title,
            $bitrix24PartnerId,
            $site,
            $phoneNumber,
            $email,
            $openLineId,
            $externalId,
            $logoUrl
        );
    }
}
