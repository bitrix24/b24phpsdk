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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TradePlatform::class)]
class TradePlatformTest extends TestCase
{
    protected TradePlatform $tradePlatformService;

    public function testList(): void
    {
        $tradePlatformsResult = $this->tradePlatformService->list();
        self::assertGreaterThanOrEqual(0, $tradePlatformsResult->getTotal());
        
        // If we have trade platforms, test the structure
        if ($tradePlatformsResult->getTotal() > 0) {
            foreach ($tradePlatformsResult->getTradePlatforms() as $tradePlatformItemResult) {
                self::assertNotNull($tradePlatformItemResult->id);
                self::assertNotNull($tradePlatformItemResult->code);
            }
        }
    }

    public function testGetFields(): void
    {
        $fieldsResult = $this->tradePlatformService->getFields();
        self::assertNotEmpty($fieldsResult->getFieldsDescription());
        
        // Check that at least some expected fields are present
        $fields = $fieldsResult->getFieldsDescription();
        self::assertArrayHasKey('tradePlatform', $fields);
        
        $tradePlatformFields = $fields['tradePlatform'];
        self::assertIsArray($tradePlatformFields);
        
        // Check presence of typical field properties
        foreach ($tradePlatformFields as $tradePlatformField) {
            self::assertArrayHasKey('isImmutable', $tradePlatformField);
            self::assertArrayHasKey('isReadOnly', $tradePlatformField);
            self::assertArrayHasKey('isRequired', $tradePlatformField);
            self::assertArrayHasKey('type', $tradePlatformField);
        }
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->tradePlatformService = Factory::getServiceBuilder()->getSaleScope()->tradePlatform();
    }
}
