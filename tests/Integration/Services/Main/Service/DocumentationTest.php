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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\Service;

use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\UnknownScopeCodeException;
use Bitrix24\SDK\Services\Main\Service\Documentation;
use Bitrix24\SDK\Services\Main\Service\Main;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Documentation::class)]
class DocumentationTest extends TestCase
{
    private Documentation $documentation;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetSchema(): void
    {
        $schema = $this->documentation->getSchema();

        $this->assertTrue(json_validate($schema->getPayload()));
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->documentation = Factory::getServiceBuilder()->getMainScope()->documentation();
    }
}