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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Template\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Template\Result\TemplateItemResult;
use Bitrix24\SDK\Services\Documentgenerator\Template\Service\Template;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Faker;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class TemplateTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Template\Service
 */
#[CoversMethod(Template::class, 'add')]
#[CoversMethod(Template::class, 'update')]
#[CoversMethod(Template::class, 'delete')]
#[CoversMethod(Template::class, 'get')]
#[CoversMethod(Template::class, 'list')]
#[CoversMethod(Template::class, 'getFields')]
#[CoversMethod(Template::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversClass(Template::class)]
class TemplateTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Template $templateService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->templateService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->template();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: creates a minimal HTML template content encoded as base64 for template upload.
     * Using HTML format avoids any dependency on the ext-zip PHP extension.
     */
    private function createMinimalTemplateBase64(): string
    {
        $html = '<html lang="en"><body><p>Test template {Number}</p></body></html>';

        return base64_encode($html);
    }

    /**
     * Helper: get the first available template from the system.
     *
     * @return array{id: int}
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstTemplate(): array
    {
        $result = $this->templateService->list()->getCoreResponse()->getResponseData()->getResult();
        $templates = $result['templates'] ?? [];

        self::assertNotEmpty($templates, 'At least one documentgenerator template must exist to run this test');

        $template = array_values($templates)[0];

        return [
            'id' => (int)$template['id'],
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testList(): void
    {
        $list = $this->templateService->list()->getTemplates();
        self::assertIsArray($list);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $templateInfo = $this->getFirstTemplate();

        $templateItemResult = $this->templateService->get($templateInfo['id'])->template();
        self::assertInstanceOf(TemplateItemResult::class, $templateItemResult);
        self::assertEquals($templateInfo['id'], $templateItemResult->id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testCount(): void
    {
        $count = $this->templateService->count();
        self::assertGreaterThanOrEqual(0, $count);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $name = 'tpl-' . $this->faker->uuid();
        $fileContent = $this->createMinimalTemplateBase64();

        $id = $this->templateService->add([
            'name' => $name,
            'file' => $fileContent,
            'numeratorId' => 1,
            'region' => 'en',
        ])->getId();

        self::assertGreaterThanOrEqual(1, $id);

        // Cleanup
        $this->templateService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testUpdate(): void
    {
        $name = 'tpl-' . $this->faker->uuid();
        $fileContent = $this->createMinimalTemplateBase64();

        $id = $this->templateService->add([
            'name' => $name,
            'file' => $fileContent,
            'numeratorId' => 1,
            'region' => 'en',
        ])->getId();

        $newName = $name . '-updated';
        self::assertTrue(
            $this->templateService->update($id, [
                'name' => $newName,
            ])->isSuccess()
        );

        self::assertEquals($newName, $this->templateService->get($id)->template()->name);

        // Cleanup
        $this->templateService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testDelete(): void
    {
        $fileContent = $this->createMinimalTemplateBase64();

        $id = $this->templateService->add([
            'name' => 'tpl-' . $this->faker->uuid(),
            'file' => $fileContent,
            'numeratorId' => 1,
            'region' => 'en',
        ])->getId();

        self::assertTrue($this->templateService->delete($id)->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGetFields(): void
    {
        $templateInfo = $this->getFirstTemplate();

        $templateFieldsResult = $this->templateService->getFields($templateInfo['id']);
        $fields = $templateFieldsResult->getFieldsDescription();

        self::assertIsArray($fields);
    }
}

