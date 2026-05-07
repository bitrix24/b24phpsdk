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

namespace Bitrix24\SDK\Tests\Integration\Core;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Tests\Integration\Factory;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;


class CoreStrictParamsOrderTest extends TestCase
{
    protected CoreInterface $core;

    public function testCallMethodWithStrictParamsOrder(): void
    {
        $response = $this->core->call(
            'task.commentitem.getlist',
            [
                2,
                [
                    "ID" => "desc"
                ],
                [
                    "AUTHOR_ID" => 1
                ]
            ]
        );
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getHttpResponse()->getStatusCode());
    }

    public function setUp(): void
    {
        $this->core = Factory::getCore(false, true);
    }
}