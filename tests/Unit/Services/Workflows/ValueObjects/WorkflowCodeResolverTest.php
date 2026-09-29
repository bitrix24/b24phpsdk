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

namespace Bitrix24\SDK\Tests\Unit\Services\Workflows\ValueObjects;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Workflows\ValueObjects\{RobotCode, ActivityCode, WorkflowCodeResolver};
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
#[CoversClass(WorkflowCodeResolver::class)]
class WorkflowCodeResolverTest extends TestCase
{
    public function testBothCodeFormsSerializeToStrings(): void
    {
        self::assertSame('robot.a-1', WorkflowCodeResolver::resolveRobotCode('robot.a-1'));
        self::assertSame('robot.a-1', WorkflowCodeResolver::resolveRobotCode(new RobotCode('robot.a-1')));
        self::assertSame('activity_a', WorkflowCodeResolver::resolveActivityCode('activity_a'));
        self::assertSame('activity_a', WorkflowCodeResolver::resolveActivityCode(new ActivityCode('activity_a')));
    }
    public function testInvalidRobotCodeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WorkflowCodeResolver::resolveRobotCode('invalid code');
    }
    public function testInvalidActivityCodeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WorkflowCodeResolver::resolveActivityCode('invalid code');
    }
}
