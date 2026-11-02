<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Category\Service;

use Bitrix24\SDK\Core\Batch;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SmartProcessReadsTest extends TestCase
{
    public function testCategoryListPreservesEnvelopeOffsetAndTypedFields(): void
    {
        $crm = $this->crm('crm.category.list', ['entityTypeId' => 1038, 'start' => 50], [
            'result' => ['categories' => [['id' => 15, 'entityTypeId' => 1038, 'name' => 'Contracts', 'sort' => 500, 'isDefault' => 'Y']]],
            'total' => 51,
        ]);
        $categories = $crm->category()->list(1038, 50);
        self::assertSame($crm->category(), $crm->category());
        self::assertSame(15, $categories->getCategories()[0]->id);
        self::assertTrue($categories->getCategories()[0]->isDefault);
        self::assertSame(51, $categories->getCoreResponse()->getResponseData()->getPagination()->getTotal());
    }

    public function testEmptyCategoryListIsSuccessful(): void
    {
        self::assertSame([], $this->crm('crm.category.list', ['entityTypeId' => 1038, 'start' => 0], ['result' => ['categories' => []]])->category()->list(1038)->getCategories());
    }

    public function testCategoryGetUsesNestedCategory(): void
    {
        $category = $this->crm('crm.category.get', ['entityTypeId' => 1038, 'id' => 15], ['result' => ['category' => ['id' => 15, 'name' => 'Contracts', 'isDefault' => 'N']]])->category()->get(1038, 15)->category();
        self::assertSame('Contracts', $category->name);
        self::assertFalse($category->isDefault);
    }

    public function testCategoryFieldsPreservesMetadataEnvelope(): void
    {
        $fields = $this->crm('crm.category.fields', ['entityTypeId' => 1038], ['result' => ['fields' => ['id' => ['type' => 'integer']]]])->category()->fields(1038)->getFieldsDescription();
        self::assertSame(['fields' => ['id' => ['type' => 'integer']]], $fields);
    }

    #[DataProvider('originalNamesProvider')]
    public function testItemReadsSupportOriginalNamesWithoutChangingDefaults(bool $original): void
    {
        $extra = $original ? ['useOriginalUfNames' => 'Y'] : [];
        $field = $original ? 'UF_CRM_7_TEST' : 'ufCrm7Test';
        $item = ['id' => 1, $field => 'Agreement'];
        $crm = $this->crm('crm.item.list', ['entityTypeId' => 1038, 'order' => ['id' => 'ASC'], 'filter' => ['=' . $field => 'Agreement'], 'select' => ['id', $field], 'start' => 50] + $extra, ['result' => ['items' => [$item]]]);
        self::assertSame('Agreement', $crm->item()->list(1038, ['id' => 'ASC'], ['=' . $field => 'Agreement'], ['id', $field], 50, $original)->getItems()[0]->__get($field));
    }

    public function testGetSupportsOriginalNames(): void
    {
        $crm = $this->crm('crm.item.get', ['entityTypeId' => 1038, 'id' => 1, 'useOriginalUfNames' => 'Y'], ['result' => ['item' => ['id' => 1, 'UF_CRM_7_TEST' => 'Agreement']]]);
        self::assertSame('Agreement', $crm->item()->get(1038, 1, true)->item()->__get('UF_CRM_7_TEST'));
    }

    public function testFieldsSupportsOriginalNames(): void
    {
        $crm = $this->crm('crm.item.fields', ['entityTypeId' => 1038, 'useOriginalUfNames' => 'Y'], ['result' => ['fields' => ['UF_CRM_7_TEST' => ['type' => 'string']]]]);
        self::assertArrayHasKey('UF_CRM_7_TEST', $crm->item()->fields(1038, true)->getFieldsDescription()['fields']);
    }

    public function testCountSupportsOriginalNameFilters(): void
    {
        $crm = $this->crm('crm.item.list', ['entityTypeId' => 1038, 'order' => [], 'filter' => ['=UF_CRM_7_TEST' => 'Agreement'], 'select' => ['id'], 'start' => 1, 'useOriginalUfNames' => 'Y'], ['result' => ['items' => []], 'total' => 6]);
        self::assertSame(6, $crm->item()->countByFilter(1038, ['=UF_CRM_7_TEST' => 'Agreement'], true));
    }

    public static function originalNamesProvider(): array
    {
        return [[false], [true]];
    }

    public function testLegacyItemCallOmitsOptionAndKeepsWildcard(): void
    {
        $crm = $this->crm('crm.item.list', ['entityTypeId' => 1038, 'order' => [], 'filter' => [], 'select' => ['*'], 'start' => 0], ['result' => ['items' => []]]);
        self::assertSame([], $crm->item()->list(1038, [], [], [])->getItems());
    }

    #[DataProvider('originalNamesProvider')]
    public function testBatchReadPassesOriginalNamesToPublicTraversal(bool $original): void
    {
        $field = $original ? 'UF_CRM_7_TEST' : 'ufCrm7Test';
        $crm = $this->crm('crm.item.list', ['entityTypeId' => 1038, 'order' => ['id' => 'ASC'], 'filter' => [], 'select' => ($original ? ['*'] : ['id', $field]), 'start' => 0] + ($original ? ['useOriginalUfNames' => 'Y'] : []), ['result' => ['items' => [['id' => 1, $field => 'Agreement'], ['id' => 3, $field => 'Other']]], 'total' => 2]);
        $items = iterator_to_array($crm->item()->batch->list(1038, ['id' => 'ASC'], [], ($original ? ['*'] : ['id', $field]), 1, $original));
        self::assertCount(1, $items);
        self::assertSame('Agreement', $items[0]->__get($field));
    }

    public function testTypeDeletionUsesInternalIdAndVerifiesEmptyResult(): void
    {
        $crm = $this->crm('crm.type.delete', ['id' => 7], ['result' => []]);
        self::assertTrue($crm->type()->delete(7)->isSuccess());
    }

    public function testMissingTypeDeletionIsNotReportedAsSuccessful(): void
    {
        $crm = $this->crm('crm.type.delete', ['id' => 7], ['result' => null]);
        self::assertFalse($crm->type()->delete(7)->isSuccess());
    }

    public function testTypeDeletionSurfacesLazyApiErrors(): void
    {
        $crm = $this->crm('crm.type.delete', ['id' => 7], ['error' => 'BX_ERROR', 'error_description' => 'Type contains items']);
        $this->expectException(\Bitrix24\SDK\Core\Exceptions\BaseException::class);
        $crm->type()->delete(7)->isSuccess();
    }

    public function testOriginalNameBatchRejectsExplicitSelectWithoutPaginationId(): void
    {
        $crm = $this->crm('crm.item.list', ['entityTypeId' => 1038, 'order' => ['id' => 'ASC'], 'filter' => [], 'select' => ['id', 'UF_CRM_7_TEST'], 'start' => 0, 'useOriginalUfNames' => 'Y'], ['result' => ['items' => []], 'total' => 0]);
        $this->expectException(\Bitrix24\SDK\Core\Exceptions\InvalidArgumentException::class);
        iterator_to_array($crm->item()->batch->list(1038, ['id' => 'ASC'], [], ['id', 'UF_CRM_7_TEST'], null, true));
    }

    private function crm(string $apiMethod, array $parameters, array $response): CRMServiceBuilder
    {
        $http = new MockHttpClient(static function (string $method, string $url, array $options) use ($apiMethod, $parameters, $response): MockResponse {
            self::assertSame('POST', $method);
            self::assertStringEndsWith('/' . $apiMethod, rtrim((string) parse_url($url, PHP_URL_PATH), '/'));
            self::assertEquals($parameters, json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR));
            return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR));
        });
        $core = (new CoreBuilder())->withCredentials(new Credentials(new WebhookUrl('https://example.com/rest/1/test/'), null, null, null))->withHttpClient($http)->build();
        return new CRMServiceBuilder($core, new Batch($core, new NullLogger()), new NullBulkItemsReader(), new NullLogger());
    }
}
