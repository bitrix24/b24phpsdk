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

class CompanyBuilder
{
    public function build(): array
    {
        return [
            'TITLE' => sprintf('Acme Inc - %s', time()),
            'COMMENTS' => sprintf('test company from b24-php-sdk integration tests %s', time()),
            'UTM_SOURCE' => 'b24-php-sdk',
            'EMAIL' => [
                [
                    'VALUE' => Faker\Factory::create()->email(),
                    'VALUE_TYPE' => EmailValueType::work->name,
                ]
            ],
            'PHONE' => [
                [
                    'VALUE' => Faker\Factory::create()->e164PhoneNumber(),
                    'VALUE_TYPE' => PhoneValueType::work->name,
                ]
            ],
        ];
    }
}