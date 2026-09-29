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

namespace Bitrix24\SDK\Tests\Integration\Services\Landing\Demos\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemoRegisteredBatchResult;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemosItemResult;
use Bitrix24\SDK\Services\Landing\Demos\Service\Batch;
use Bitrix24\SDK\Services\Landing\Demos\Service\Demos;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Batch::class)]
#[CoversClass(DemoRegisteredBatchResult::class)]
class BatchTest extends TestCase
{
    private Demos $demosService;

    /** @var string[] */
    private array $createdTemplateCodes = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->demosService = Fabric::getServiceBuilder()->getLandingScope()->demos();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdTemplateCodes as $createdTemplateCode) {
            try {
                $this->demosService->unregister($createdTemplateCode);
            } catch (\Exception) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * Minimal template data in the landing.site.fullExport format
     *
     * @return array<string, mixed>
     */
    private function buildTemplateData(string $code, string $name): array
    {
        return [
            'charset' => 'UTF-8',
            'code' => $code,
            'name' => $name,
            'description' => 'SDK batch test template',
            'preview' => '',
            'preview2x' => '',
            'preview3x' => '',
            'preview_url' => '',
            'show_in_list' => 'Y',
            'type' => 'page',
            'version' => 3,
            'fields' => [
                'ADDITIONAL_FIELDS' => [
                    'THEME_CODE' => 'app',
                    'UP_SHOW' => 'Y',
                ],
                'TITLE' => $name,
                'LANDING_ID_INDEX' => $code,
                'LANDING_ID_404' => '0',
            ],
            'layout' => [],
            'folders' => [],
            'syspages' => [],
            'items' => [],
        ];
    }

    /**
     * @return array<string, int[]> registered template ids keyed by template code
     * @throws BaseException
     */
    private function registerTemplates(int $count): array
    {
        $suffix = uniqid();
        $templatesData = [];
        for ($i = 1; $i <= $count; $i++) {
            $templatesData[] = $this->buildTemplateData(
                sprintf('sdk_batch_demo_%d_%s', $i, $suffix),
                sprintf('SDK Batch Demo %d %s', $i, $suffix)
            );
        }

        $registered = [];
        foreach ($this->demosService->batch->register($templatesData) as $cnt => $demoRegisteredBatchResult) {
            self::assertInstanceOf(DemoRegisteredBatchResult::class, $demoRegisteredBatchResult);
            $registered[$templatesData[$cnt]['code']] = $demoRegisteredBatchResult->getIds();
        }

        $this->createdTemplateCodes = array_merge($this->createdTemplateCodes, array_keys($registered));

        return $registered;
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch register creates multiple templates')]
    public function testBatchRegister(): void
    {
        $registered = $this->registerTemplates(2);

        self::assertCount(2, $registered);
        foreach ($registered as $ids) {
            self::assertNotEmpty($ids);
            foreach ($ids as $id) {
                self::assertGreaterThan(0, $id);
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch list returns registered templates')]
    public function testBatchList(): void
    {
        $registered = $this->registerTemplates(2);
        $ids = array_merge(...array_values($registered));
        sort($ids);

        $foundIds = [];
        foreach ($this->demosService->batch->list(['ID', 'TITLE'], ['ID' => $ids]) as $demosItemResult) {
            self::assertInstanceOf(DemosItemResult::class, $demosItemResult);
            $foundIds[] = (int)$demosItemResult->ID;
        }

        sort($foundIds);
        self::assertSame($ids, $foundIds);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('batch unregister removes multiple templates')]
    public function testBatchUnregister(): void
    {
        $registered = $this->registerTemplates(2);
        $codes = array_keys($registered);

        $cnt = 0;
        foreach ($this->demosService->batch->unregister($codes) as $deletedItemBatchResult) {
            $cnt++;
            self::assertTrue($deletedItemBatchResult->isSuccess());
        }

        self::assertSame(count($codes), $cnt);
        self::assertSame([], $this->demosService->getList(['ID'], ['ID' => array_merge(...array_values($registered))])->getDemos());
        $this->createdTemplateCodes = [];
    }
}
