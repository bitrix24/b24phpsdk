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

namespace Bitrix24\SDK\Tests\Integration\Services\Workflows;

use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Workflows\Activity\Service\Activity;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use Bitrix24\SDK\Services\Workflows\Robot\Service\Robot;
use Bitrix24\SDK\Services\Workflows\ValueObjects\ActivityCode;
use Bitrix24\SDK\Services\Workflows\ValueObjects\RobotCode;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Activity::class)]
#[CoversClass(Robot::class)]
class ValueObjectMigrationTest extends TestCase
{
    public function testActivityLifecycleWithValueObjects(): void
    {
        $activity = Factory::getServiceBuilder(true)->getWorkflowsScope()->activity();
        $code = new ActivityCode('sdk533_activity_' . bin2hex(random_bytes(6)));
        $handler = new Url(rtrim((string) $_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/') . '/sdk533-inert');
        $added = $activity->add($code, $handler, 1, new LocalizedString(LangCodes::EN, 'SDK 533 activity'), new LocalizedString(LangCodes::EN, 'Test'), false, [], false, [], WorkflowDocumentType::buildForLead(), []);
        try {
            self::assertTrue($added->isSuccess());
            self::assertTrue($activity->update($code, null, null, new LocalizedString(LangCodes::EN, 'Updated'), new LocalizedString(LangCodes::EN, 'Updated description'), null, null, null, null, null, null)->isSuccess());
        } finally {
            self::assertTrue($activity->delete($code)->isSuccess());
        }
    }

    public function testRobotLifecycleWithValueObjects(): void
    {
        $robot = Factory::getServiceBuilder(true)->getWorkflowsScope()->robot();
        $code = new RobotCode('sdk533_robot_' . bin2hex(random_bytes(6)));
        $handler = new Url(rtrim((string) $_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/') . '/sdk533-inert');
        $added = $robot->add($code, $handler, 1, new LocalizedString(LangCodes::EN, 'SDK 533 robot'), false, [], false, [], new LocalizedString(LangCodes::EN, 'Test'));
        try {
            self::assertTrue($added->isSuccess());
            self::assertTrue($robot->update($code, localizedRobotName: new LocalizedString(LangCodes::EN, 'Updated'))->isSuccess());
        } finally {
            self::assertTrue($robot->delete($code)->isSuccess());
        }
    }
}
