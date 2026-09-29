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

namespace Bitrix24\SDK\Tests\Unit\Services\Pull\Watch\Service;

use Bitrix24\SDK\Services\Pull\Watch\Service\Watch;
use Bitrix24\SDK\Tests\Unit\Services\Pull\AbstractServiceContractTestCase;

class WatchTest extends AbstractServiceContractTestCase
{
    public static function calls(): array
    {
        return [
            'watch tags' => [Watch::class, 'extend', [['tag',12]], 'pull.watch.extend', ['tags' => ['tag',12]]],
        ];
    }
}
