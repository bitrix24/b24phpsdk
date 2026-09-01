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

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Vat\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Vat\Service\Vat;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vat::class)]
class VatTest extends TestCase
{
    private Vat $vatService;

    #[\Override]
    protected function setUp(): void
    {
        $this->vatService = Fabric::getServiceBuilder()->getCatalogScope()->vat();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Vat::add, Vat::get, Vat::delete')]
    public function testAddGetDelete(): void
    {
        $name = sprintf('test vat %s', time());
        $addResult = $this->vatService->add([
            'name' => $name,
            'rate' => 13,
            'sort' => 50,
            'active' => 'Y',
        ]);
        $vatId = $addResult->vat()->id;
        $this->assertSame($name, $addResult->vat()->name);
        $this->assertSame(13.0, $addResult->vat()->rate);

        $getResult = $this->vatService->get($vatId);
        $this->assertSame($vatId, $getResult->vat()->id);

        $this->assertTrue($this->vatService->delete($vatId)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Vat::update')]
    public function testUpdate(): void
    {
        $vatId = $this->vatService->add([
            'name' => sprintf('test vat %s', time()),
            'rate' => 13,
            'sort' => 50,
        ])->vat()->id;

        $updatedName = sprintf('updated vat %s', time());
        $updateResult = $this->vatService->update($vatId, ['name' => $updatedName, 'rate' => 20]);
        $this->assertSame($updatedName, $updateResult->vat()->name);
        $this->assertSame(20.0, $updateResult->vat()->rate);

        $this->vatService->delete($vatId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Vat::list')]
    public function testList(): void
    {
        $vatId = $this->vatService->add([
            'name' => sprintf('test vat %s', time()),
            'rate' => 13,
            'sort' => 50,
        ])->vat()->id;

        $listResult = $this->vatService->list([], ['id' => $vatId]);
        $this->assertCount(1, $listResult->getVats());

        $this->vatService->delete($vatId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Vat::getFields')]
    public function testGetFields(): void
    {
        $this->assertIsArray($this->vatService->getFields()->getFieldsDescription());
    }
}
