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

namespace Bitrix24\SDK\Tests\Integration\Services\Sale\TradePlatform\Service;

use Bitrix24\SDK\Services\Sale\TradePlatform\Service\TradePlatform;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TradePlatform::class)]
class TradePlatformTest extends TestCase
{
    protected TradePlatform $tradePlatformService;

    public function testList(): void
    {
        $result = $this->tradePlatformService->list();
        self::assertGreaterThanOrEqual(0, $result->getTotal());
        
        // If we have trade platforms, test the structure
        if ($result->getTotal() > 0) {
            foreach ($result->getTradePlatforms() as $tradePlatform) {
                self::assertArrayHasKey('id', $tradePlatform->data);
                self::assertArrayHasKey('code', $tradePlatform->data);
            }
        }
    }

    public function testGetFields(): void
    {
        $result = $this->tradePlatformService->getFields();
        self::assertNotEmpty($result->getFieldsDescription());
        
        // Check that at least some expected fields are present
        $fields = $result->getFieldsDescription();
        self::assertArrayHasKey('tradePlatform', $fields);
        
        $tradePlatformFields = $fields['tradePlatform'];
        self::assertIsArray($tradePlatformFields);
        
        // Check presence of typical field properties
        foreach ($tradePlatformFields as $fieldInfo) {
            self::assertArrayHasKey('isImmutable', $fieldInfo);
            self::assertArrayHasKey('isReadOnly', $fieldInfo);
            self::assertArrayHasKey('isRequired', $fieldInfo);
            self::assertArrayHasKey('type', $fieldInfo);
        }
    }

    protected function setUp(): void
    {
        $this->tradePlatformService = Fabric::getServiceBuilder()->getSaleScope()->tradePlatform();
    }
}
