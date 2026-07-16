<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\Robot\Service;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Workflows\Robot\Result\AddedRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Result\UpdateRobotResult;
use Bitrix24\SDK\Services\Workflows\Robot\Service\Robot;
use Bitrix24\SDK\Services\Workflows\Template\Service\Batch;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Robot::class)]
class RobotTest extends TestCase
{
    private Robot $robot;

    #[\Override]
    protected function setUp(): void
    {
        $this->robot = new Robot(
            new Batch(new NullBatch(), new NullLogger()),
            new NullCore(),
            new NullLogger()
        );
    }

    #[Test]
    #[TestDox('add() returns AddedRobotResult')]
    public function testAddReturnsAddedRobotResult(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            true,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() accepts a Url placement handler when placement is enabled')]
    public function testAddAcceptsPlacementHandlerUrl(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            false,
            [],
            true,
            [],
            [],
            [],
            [],
            new Url('https://example.com/placement')
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() throws when placement is enabled but placement handler URL is missing')]
    public function testAddThrowsWhenPlacementHandlerMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            false,
            [],
            true,
            [],
            [],
            [],
            [],
            null
        );
    }

    #[Test]
    #[TestDox('add() accepts a Url value object for the handler URL (Stage 1 migration)')]
    public function testAddAcceptsHandlerUrlAsUrl(): void
    {
        $result = $this->robot->add(
            'test_robot',
            new Url('https://example.com/handler'),
            1,
            ['en' => 'Robot name'],
            false,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('update() accepts a Url value object for the handler URL (Stage 1 migration)')]
    public function testUpdateAcceptsHandlerUrlAsUrl(): void
    {
        $result = $this->robot->update(
            'test_robot',
            new Url('https://example.com/handler')
        );

        $this->assertInstanceOf(UpdateRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() accepts a RobotCode value object for the code (Stage 1 migration)')]
    public function testAddAcceptsRobotCode(): void
    {
        $result = $this->robot->add(
            new RobotCode('test_robot'),
            'https://example.com/handler',
            1,
            ['en' => 'Robot name'],
            false,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('update() accepts a RobotCode value object for the code (Stage 1 migration)')]
    public function testUpdateAcceptsRobotCode(): void
    {
        $result = $this->robot->update(
            new RobotCode('test_robot'),
            new Url('https://example.com/handler')
        );

        $this->assertInstanceOf(UpdateRobotResult::class, $result);
    }

    #[Test]
    #[TestDox('add() accepts a LocalizedString for the NAME (Stage 1 migration)')]
    public function testAddAcceptsLocalizedStringName(): void
    {
        $result = $this->robot->add(
            'test_robot',
            'https://example.com/handler',
            1,
            new LocalizedString(LangCodes::EN, 'My robot'),
            false,
            [],
            false,
            []
        );

        $this->assertInstanceOf(AddedRobotResult::class, $result);
    }
}
