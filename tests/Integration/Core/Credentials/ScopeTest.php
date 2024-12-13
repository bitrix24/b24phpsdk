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

namespace Bitrix24\SDK\Tests\Integration\Core\Credentials;

use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private ServiceBuilder $sb;

    public function testScopeCodesIsActual(): void
    {
        $scopeCodes = $this->sb->getMainScope()->main()->getAvailableScope()->getResponseData()->getResult();
        sort($scopeCodes);

        $this->assertEquals(
            $scopeCodes,
            Scope::getAvailableScopeCodes(),
            sprintf(
                'actual scope codes are not equal sdk scope codes, diff «%s»',
                implode(', ', array_diff($scopeCodes, Scope::getAvailableScopeCodes()))
            )
        );
    }

    public function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder();
    }
}