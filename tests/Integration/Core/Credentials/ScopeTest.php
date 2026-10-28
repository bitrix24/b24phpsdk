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
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private ServiceBuilder $sb;

    public function testScopeCodesIsActual(): void
    {
        $scopeCodes = $this->sb->getMainScope()->main()->getAvailableScope()->getResponseData()->getResult();
        sort($scopeCodes);

        // full=true describes this portal; the SDK also knows scopes from other editions/modules.
        self::assertNotEmpty($scopeCodes, 'The portal must return its available scope catalog');
        $unknownScopeCodes = array_values(array_diff($scopeCodes, Scope::getAvailableScopeCodes()));
        self::assertSame([], $unknownScopeCodes, sprintf(
            'Portal scopes missing from the SDK catalog: %s',
            implode(', ', $unknownScopeCodes)
        ));
        self::assertSame($scopeCodes, new Scope($scopeCodes)->getScopeCodes());
    }

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
    }
}