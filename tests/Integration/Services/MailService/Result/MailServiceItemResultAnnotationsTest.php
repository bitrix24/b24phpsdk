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

namespace Bitrix24\SDK\Tests\Integration\Services\MailService\Result;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\MailService\Result\MailServiceItemResult;
use Bitrix24\SDK\Services\MailService\Service\MailService;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailServiceItemResult::class)]
class MailServiceItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private MailService $mailService;

    private int $testMailServiceId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->mailService = Fabric::getServiceBuilder()->getMailServiceScope()->mailService();
        $this->testMailServiceId = $this->mailService->add(
            'SDK Annotations Test MailService',
            'Y',
            'imap.example.com',
            993,
            'Y',
            'https://mail.example.com'
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->testMailServiceId > 0) {
            $this->mailService->delete($this->testMailServiceId);
        }
    }

    #[Test]
    #[TestDox('all system fields are annotated in MailServiceItemResult phpdoc')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->mailService->get($this->testMailServiceId)
            ->getCoreResponse()
            ->getResponseData()
            ->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            MailServiceItemResult::class
        );
    }

    #[Test]
    #[TestDox('all system fields in MailServiceItemResult have valid type annotation')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $allFields = $this->mailService->fields()->getFieldsDescription();
        $systemFieldsCodes = (new FieldsFilter())->filterSystemFields(array_keys($allFields));
        $systemFields = array_filter($allFields, static fn($code): bool => in_array($code, $systemFieldsCodes, true), ARRAY_FILTER_USE_KEY);

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation(
            $systemFields,
            MailServiceItemResult::class
        );
    }
}
