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

namespace Bitrix24\SDK\Tests\Integration\Services\Placement\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Notify\Service\Notify;
use Bitrix24\SDK\Services\IMOpenLines\Service\Network;
use Bitrix24\SDK\Services\Placement\Result\PlacementLocationItemResult;
use Bitrix24\SDK\Services\Placement\Service\Placement;
use Bitrix24\SDK\Services\Placement\Service\PlacementLocationCode;
use Bitrix24\SDK\Services\Telephony\Call\Service\Call;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Placement::class)]
class PlacementTest extends TestCase
{
    private Placement $placementService;

    #[Test]
    public function testBind(): void
    {
        $this->assertHandlerLifecycle(false);
    }

    #[Test]
    public function testUnbind(): void
    {
        $this->assertHandlerLifecycle(true);
    }

    private function assertHandlerLifecycle(bool $useValueObject): void
    {
        $url = rtrim((string) $_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/') . '/sdk533-' . bin2hex(random_bytes(6));
        $handler = $useValueObject ? new \Bitrix24\SDK\Core\ValueObjects\Url($url) : $url;
        $result = $this->placementService->bind(PlacementLocationCode::CRM_CONTACT_DETAIL_TAB, $handler, ['en' => ['TITLE' => 'SDK 533 test']]);
        try {
            self::assertTrue($result->isSuccess());
            $matching = array_filter($this->placementService->get()->getPlacementsLocationInformation(), static fn ($item): bool => $item->handler === $url);
            self::assertCount(1, $matching);
        } finally {
            self::assertSame(1, $this->placementService->unbind(PlacementLocationCode::CRM_CONTACT_DETAIL_TAB, $handler)->getDeletedPlacementHandlersCount());
        }
    }

    #[Test]
    #[TestDox('Test method get')]
    public function testGet(): void
    {
        $placementsLocationInformationResult = $this->placementService->get();
        $this->assertGreaterThanOrEqual(0, count($placementsLocationInformationResult->getPlacementsLocationInformation()));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('Test method list')]
    public function testList(): void
    {
        $placementLocationCodesResult = $this->placementService->list();
        $this->assertGreaterThanOrEqual(0, count($placementLocationCodesResult->getLocationCodes()));
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->placementService = Factory::getServiceBuilder(true)->getPlacementScope()->placement();
    }
}