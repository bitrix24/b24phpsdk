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

namespace Bitrix24\SDK\Tests\Integration\Services\Pull\Application\Service;

use Bitrix24\SDK\Core\Exceptions\WrongAuthTypeException;
use Bitrix24\SDK\Services\Pull\Application\Service\Application;
use Bitrix24\SDK\Tests\Integration\Services\Pull\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    public function testConfigRequiresApplicationAuthorization(): void
    {
        // Factory uses an incoming webhook. OAuth delivery success is not tested here.
        $this->expectException(WrongAuthTypeException::class);
        Factory::getServiceBuilder()->getPullScope()->application()->config(true, false);
    }
}
