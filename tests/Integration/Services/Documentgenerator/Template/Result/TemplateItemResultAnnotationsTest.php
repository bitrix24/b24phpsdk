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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Template\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplateItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Service\Template;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateItemResult::class)]
class TemplateItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Template $templateService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->templateService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->template();
    }

    /**
     * Helper: get raw data for the first template with all fields selected.
     *
     * @return array<string, mixed>
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstTemplateRawItem(): array
    {
        $result = $this->templateService->list(
            [],
            [],
            ['*', 'users', 'providers']
        )->getCoreResponse()->getResponseData()->getResult();

        $templates = $result['templates'] ?? [];
        self::assertNotEmpty($templates, 'At least one documentgenerator template must exist to run this test');

        return array_values($templates)[0];
    }

    #[Test]
    #[TestDox('all fields in TemplateItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllSystemFieldsAnnotated(): void
    {
        $rawItem = $this->getFirstTemplateRawItem();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            TemplateItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in TemplateItemResult have valid type casting in magic getters')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $rawItem = $this->getFirstTemplateRawItem();
        $templateItemResult = new TemplateItemResult($rawItem);

        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $templateItemResult,
            TemplateItemResult::class
        );
    }
}

