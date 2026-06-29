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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\Document\Service;

use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentResult;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    #[\Override]
    protected function setUp() : void
    {
    }

    #[Test]
    #[TestDox('sign.b2e.document.get returns DocumentResult for a known document UID')]
    public function testGetReturnsDocumentResult(): void
    {
        $this->markTestSkipped(
            'sign.b2e.document.get requires application context and a valid document UID from a real КЭДО environment.'
        );
    }

    #[Test]
    #[TestDox('sign.b2e.document.send returns DocumentResult')]
    public function testSendReturnsDocumentResult(): void
    {
        $this->markTestSkipped(
            'sign.b2e.document.send requires application context, sign.b2e scope and a real КЭДО environment.'
        );
    }
}

