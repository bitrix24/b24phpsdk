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

namespace Bitrix24\SDK\Tests\Builders\Services\CRM;


use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\EmailValueType;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\PhoneValueType;
use Faker;
use Symfony\Component\Uid\Uuid;

readonly class RequisiteBuilder
{
    public function __construct(
        private int $entityTypeId,
        private int $entityId,
        private int $requisitePresetId
    ) {
    }

    public function build(): array
    {
        return [
            "ENTITY_TYPE_ID" => $this->entityTypeId,
            "ENTITY_ID" => $this->entityId,
            "PRESET_ID" => $this->requisitePresetId,
            "NAME" => sprintf("test organization %s", time()),
            "ACTIVE" => "Y",
            "ADDRESS_ONLY" => "N",
            "SORT" => 500,
            "RQ_COMPANY_NAME" => "ACME INC",
            "RQ_COMPANY_FULL_NAME" => "ACME INCORPORATED",
            "RQ_COMPANY_REG_DATE" => "06.04.2007",
            "RQ_DIRECTOR" => Faker\Factory::create()->name(),
            "RQ_INN" => "7717586110",
            "RQ_KPP" => "770501001",
            "RQ_OGRN" => "5077746476209",
            "XML_ID" => Uuid::v7()->toRfc4122()

        ];
    }
}