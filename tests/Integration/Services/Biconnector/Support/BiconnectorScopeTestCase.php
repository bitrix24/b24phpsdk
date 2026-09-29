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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Support;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Biconnector\BiconnectorServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

abstract class BiconnectorScopeTestCase extends TestCase
{
    protected function getBiconnectorScope(): BiconnectorServiceBuilder
    {
        try {
            return Fabric::getServiceBuilder(true)->getBiconnectorScope();
        } catch (InvalidArgumentException $exception) {
            if (str_contains($exception->getMessage(), 'Application credentials for integration tests are not available')) {
                $this->markTestSkipped($exception->getMessage());
            }

            throw $exception;
        }
    }
}
