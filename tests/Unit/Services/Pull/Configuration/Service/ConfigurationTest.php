<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Pull\Configuration\Service;

use Bitrix24\SDK\Services\Pull\Configuration\Service\Configuration;
use Bitrix24\SDK\Tests\Unit\Services\Pull\AbstractServiceContractTestCase;

class ConfigurationTest extends AbstractServiceContractTestCase
{
    public static function calls(): array
    {
        return [
            'config defaults' => [Configuration::class, 'get', [], 'pull.config.get', []],
            'config flags' => [Configuration::class, 'get', [false,true], 'pull.config.get', ['CACHE' => 'N','REOPEN' => 'Y']],
            'config reopen only' => [Configuration::class, 'get', [null,false], 'pull.config.get', ['REOPEN' => 'N']],
        ];
    }
}
