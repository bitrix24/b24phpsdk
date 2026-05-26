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

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Document\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Document\Service\Document;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
use Faker;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Document\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Documentgenerator\Document\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Document $documentService;

    private Faker\Generator $faker;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->documentService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->document();
        $this->faker = Faker\Factory::create();
    }

    /**
     * Helper: get first available non-CRM documentgenerator template.
     * Returns array with keys: id, providerClassName
     *
     * @return array{id: int, providerClassName: string}
     *
     * @throws BaseException
     * @throws TransportException
     */
    private function getFirstTemplate(): array
    {
        $core = Factory::getCore();
        $response = $core->call('documentgenerator.template.list', ['select' => ['id', 'providers']]);
        $result = $response->getResponseData()->getResult();
        $templates = $result['templates'] ?? [];

        self::assertNotEmpty($templates, 'At least one documentgenerator template must exist to run this test');

        $template = array_values($templates)[0];
        $providers = $template['providers'] ?? [];
        $providerClassName = empty($providers) ? 'Bitrix\DocumentGenerator\DataProvider\Rest' : (string)$providers[0];

        return [
            'id' => (int)$template['id'],
            'providerClassName' => $providerClassName,
        ];
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list documents')]
    public function testBatchList(): void
    {
        $templateInfo = $this->getFirstTemplate();
        $value = 'SDK_BATCH_TEST_' . $this->faker->uuid();

        $id = $this->documentService->add(
            $templateInfo['id'],
            $templateInfo['providerClassName'],
            $value
        )->getId();

        $cnt = 0;
        foreach ($this->documentService->batch->list(1) as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(1, $cnt);

        // Cleanup
        $this->documentService->delete($id);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add documents')]
    public function testBatchAdd(): void
    {
        $templateInfo = $this->getFirstTemplate();
        $items = [];

        for ($i = 1; $i <= 3; $i++) {
            $items[] = [
                'templateId' => $templateInfo['id'],
                'providerClassName' => $templateInfo['providerClassName'],
                'value' => 'SDK_BATCH_ADD_' . $this->faker->uuid(),
            ];
        }

        $ids = [];
        $cnt = 0;
        foreach ($this->documentService->batch->add($items) as $added) {
            $cnt++;
            $ids[] = $added->getId();
        }

        self::assertEquals(count($items), $cnt);

        // Cleanup
        $delCnt = 0;
        foreach ($this->documentService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($items), $delCnt);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch update documents')]
    public function testBatchUpdate(): void
    {
        $templateInfo = $this->getFirstTemplate();
        $docIds = [];

        for ($i = 1; $i <= 3; $i++) {
            $value = 'SDK_BATCH_UPD_' . $this->faker->uuid();
            $docIds[] = $this->documentService->add(
                $templateInfo['id'],
                $templateInfo['providerClassName'],
                $value
            )->getId();
        }

        $updatePayload = [];
        foreach ($docIds as $docId) {
            $updatePayload[$docId] = [
                'values' => [],
                'stampsEnabled' => 1,
            ];
        }

        foreach ($this->documentService->batch->update($updatePayload) as $updated) {
            $this->assertTrue($updated->isSuccess());
        }

        // Cleanup
        foreach ($this->documentService->batch->delete($docIds) as $deleted) {
            // consume generator to execute batch deletion
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete documents')]
    public function testBatchDelete(): void
    {
        $templateInfo = $this->getFirstTemplate();
        $ids = [];

        for ($i = 1; $i <= 3; $i++) {
            $value = 'SDK_BATCH_DEL_' . $this->faker->uuid();
            $ids[] = $this->documentService->add(
                $templateInfo['id'],
                $templateInfo['providerClassName'],
                $value
            )->getId();
        }

        $delCnt = 0;
        foreach ($this->documentService->batch->delete($ids) as $deleted) {
            $delCnt++;
        }

        self::assertEquals(count($ids), $delCnt);
    }
}



