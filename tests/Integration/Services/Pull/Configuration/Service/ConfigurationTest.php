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

namespace Bitrix24\SDK\Tests\Integration\Services\Pull\Configuration\Service;

use Bitrix24\SDK\Services\Pull\Configuration\Service\Configuration;
use Bitrix24\SDK\Tests\Integration\Services\Pull\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function testGetPreservesConfigurationShape(): void
    {
        // Cached config only; do not reopen the channel during read-only verification.
        $configuration = Factory::getServiceBuilder()->getPullScope()->configuration()->get(true, false)->getConfiguration();
        foreach (['server','api','channels'] as $key) {
            self::assertTrue(isset($configuration[$key]) && is_array($configuration[$key]), 'Expected configuration section: '.$key);
        }
    }
}
