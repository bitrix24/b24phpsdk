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

namespace Bitrix24\SDK\Tests\Unit\Services\Pull\Application\Service;

use Bitrix24\SDK\Services\Pull\Application\Service\Application;
use Bitrix24\SDK\Tests\Unit\Services\Pull\AbstractServiceContractTestCase;

class ApplicationTest extends AbstractServiceContractTestCase
{
    public static function calls(): array
    {
        return [
            'app config defaults' => [Application::class, 'config', [], 'pull.application.config.get', []],
            'app config flags' => [Application::class, 'config', [true,false], 'pull.application.config.get', ['CACHE' => 'Y','REOPEN' => 'N']],
            'shared event' => [Application::class, 'event', ['refresh'], 'pull.application.event.add', ['COMMAND' => 'refresh','PARAMS' => []]],
            'event string user' => [Application::class, 'event', ['refresh',['enabled' => false,'count' => 0],'module','12'], 'pull.application.event.add', ['COMMAND' => 'refresh','PARAMS' => ['enabled' => false,'count' => 0],'MODULE_ID' => 'module','USER_ID' => '12']],
            'event int user' => [Application::class, 'event', ['refresh',[],null,12], 'pull.application.event.add', ['COMMAND' => 'refresh','PARAMS' => [],'USER_ID' => 12]],
            'event users' => [Application::class, 'event', ['refresh',[],null,[12,'13']], 'pull.application.event.add', ['COMMAND' => 'refresh','PARAMS' => [],'USER_ID' => [12,'13']]],
            'push string user' => [Application::class, 'push', ['12','hello'], 'pull.application.push.add', ['USER_ID' => '12','TEXT' => 'hello']],
            'push int user' => [Application::class, 'push', [12,'hello'], 'pull.application.push.add', ['USER_ID' => 12,'TEXT' => 'hello']],
            'push users avatar' => [Application::class, 'push', [[12,'13'],'hello','avatar'], 'pull.application.push.add', ['USER_ID' => [12,'13'],'TEXT' => 'hello','AVATAR' => 'avatar']],
        ];
    }
}
