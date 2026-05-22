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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Revision\Service;

use Bitrix24\SDK\Services\IM\Revision\Service\Revision;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Revision::class)]
class RevisionTest extends TestCase
{
    private Revision $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Revision(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testServiceInstantiates(): void
    {
        $this->assertInstanceOf(Revision::class, $this->service);
    }
}
