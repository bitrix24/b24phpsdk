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

namespace Bitrix24\SDK\Tests\Unit\Services\Landing\RepoWidget\Service;

use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetDebugResult;
use Bitrix24\SDK\Services\Landing\RepoWidget\Result\RepoWidgetGetListResult;
use Bitrix24\SDK\Services\Landing\RepoWidget\Service\RepoWidget;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RepoWidget::class)]
class RepoWidgetTest extends TestCase
{
    private RepoWidget $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new RepoWidget(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testRegisterReturnsAddedItemResult(): void
    {
        $this->assertInstanceOf(
            AddedItemResult::class,
            $this->service->register('test_widget', ['NAME' => 'Test Widget'])
        );
    }

    #[Test]
    public function testUnregisterReturnsDeletedItemResult(): void
    {
        $this->assertInstanceOf(
            DeletedItemResult::class,
            $this->service->unregister('test_widget')
        );
    }

    #[Test]
    public function testGetListReturnsRepoWidgetGetListResult(): void
    {
        $this->assertInstanceOf(
            RepoWidgetGetListResult::class,
            $this->service->getList()
        );
    }

    #[Test]
    public function testGetListWithFiltersReturnsRepoWidgetGetListResult(): void
    {
        $this->assertInstanceOf(
            RepoWidgetGetListResult::class,
            $this->service->getList(['ID', 'NAME'], ['ACTIVE' => 'Y'])
        );
    }

    #[Test]
    public function testDebugReturnsRepoWidgetDebugResult(): void
    {
        $this->assertInstanceOf(
            RepoWidgetDebugResult::class,
            $this->service->debug(true)
        );
    }
}
