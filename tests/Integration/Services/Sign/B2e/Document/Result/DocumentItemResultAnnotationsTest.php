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

namespace Bitrix24\SDK\Tests\Integration\Services\Sign\B2e\Document\Result;

use Bitrix24\SDK\Services\Sign\B2e\Document\Result\DocumentItemResult;
use Bitrix24\SDK\Services\Sign\B2e\Document\Service\Document;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentItemResult::class)]
class DocumentItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Document $documentService;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder()->getSignScope()->document();
    }

    #[Test]
    #[TestDox('testAllSystemFieldsAnnotated: all fields in DocumentItemResult are annotated in phpdoc')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $this->markTestSkipped(
            'sign.b2e.document.get requires application context and a valid КЭДО document UID. ' .
            'Run manually with a real document UID to verify annotation completeness.'
        );
    }

    #[Test]
    #[TestDox('testAllSystemFieldsHasValidTypeAnnotation: all fields in DocumentItemResult have valid type annotations')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $this->markTestSkipped(
            'sign.b2e.document.get requires application context and a valid КЭДО document UID. ' .
            'Run manually with a real document UID to verify annotation types.'
        );
    }
}
