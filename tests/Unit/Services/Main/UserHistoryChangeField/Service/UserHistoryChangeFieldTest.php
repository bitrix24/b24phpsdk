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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryChangeField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Attributes\OpenApiEntity;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldItemResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Result\UserHistoryChangeFieldsResult;
use Bitrix24\SDK\Services\Main\UserHistoryChangeField\Service\UserHistoryChangeField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(UserHistoryChangeField::class)]
#[CoversClass(UserHistoryChangeFieldResult::class)]
#[CoversClass(UserHistoryChangeFieldsResult::class)]
#[CoversClass(UserHistoryChangeFieldItemResult::class)]
class UserHistoryChangeFieldTest extends TestCase
{
    #[DataProvider('selectProvider')]
    public function testGetMapsNameAndSelectToV3Call(array $select): void
    {
        $parameters = ['name' => 'data'];
        if ($select !== []) {
            $parameters['select'] = $select;
        }

        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('main.user.history.fields.field.get', $parameters, ApiVersion::v3)
            ->willReturn($this->response(['item' => ['name' => 'data']]));

        $result = (new UserHistoryChangeField($core, new NullLogger()))->get('data', $select);

        self::assertSame('data', $result->getUserHistoryChangeField()->name);
    }

    #[DataProvider('selectProvider')]
    public function testListMapsSelectToV3Call(array $select): void
    {
        $parameters = $select === [] ? [] : ['select' => $select];
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('main.user.history.fields.field.list', $parameters, ApiVersion::v3)
            ->willReturn($this->response(['items' => []]));

        $result = (new UserHistoryChangeField($core, new NullLogger()))->list($select);

        self::assertSame([], $result->getUserHistoryChangeFields());
    }

    public static function selectProvider(): iterable
    {
        yield 'default select is omitted' => [[]];
        yield 'explicit select is preserved' => [['name', 'type', 'description']];
    }

    #[DataProvider('emptyNameProvider')]
    public function testGetRejectsEmptyNameWithoutCallingCore(string $name): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $userHistoryChangeField = new UserHistoryChangeField($core, new NullLogger());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('field name must not be empty');
        $userHistoryChangeField->get($name);
    }

    public static function emptyNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['   '];
        yield 'whitespace' => ["\t\n"];
    }

    public function testServiceAndEndpointMetadataDeclareMainScopeAndV3(): void
    {
        $reflectionClass = new ReflectionClass(UserHistoryChangeField::class);
        $serviceMetadata = $reflectionClass->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        self::assertSame(['main'], $serviceMetadata->scope->getScopeCodes());

        foreach (['get', 'list'] as $method) {
            $metadata = $reflectionClass->getMethod($method)->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            self::assertSame('main.user.history.fields.field.' . $method, $metadata->name);
            self::assertSame(ApiVersion::v3, $metadata->apiVersion);
            self::assertSame('https://apidocs.bitrix24.com/api-reference/rest-v3.html', $metadata->documentationUrl);
        }
    }

    public function testSingleResultReadsItemEnvelopeAndPreservesMetadata(): void
    {
        $raw = $this->descriptor();
        $userHistoryChangeFieldResult = new UserHistoryChangeFieldResult($this->response(['item' => $raw]));
        $item = $userHistoryChangeFieldResult->getUserHistoryChangeField();

        self::assertInstanceOf(AbstractAnnotatedItem::class, $item);
        self::assertSame('data', $item->name);
        self::assertSame('object', $item->type);
        self::assertSame('Example field', $item->title);
        self::assertNull($item->description);
        self::assertSame($raw['validationRules'], $item->validationRules);
        self::assertSame(['read'], $item->requiredGroups);
        self::assertSame(['update'], $item->editableGroups);
        self::assertSame('string', $item->elementType);
    }

    public function testListResultReadsItemsEnvelopeInOrder(): void
    {
        $userHistoryChangeFieldsResult = new UserHistoryChangeFieldsResult($this->response(['items' => [
            ['name' => 'data', 'type' => 'object'],
            ['name' => 'id', 'type' => 'integer'],
        ]]));

        $items = $userHistoryChangeFieldsResult->getUserHistoryChangeFields();

        self::assertCount(2, $items);
        self::assertInstanceOf(UserHistoryChangeFieldItemResult::class, $items[0]);
        self::assertSame('data', $items[0]->name);
        self::assertSame('object', $items[0]->type);
        self::assertSame('id', $items[1]->name);
        self::assertSame('integer', $items[1]->type);
    }

    public function testEmptyListReturnsEmptyItemsAndDescriptions(): void
    {
        $userHistoryChangeFieldsResult = new UserHistoryChangeFieldsResult($this->response(['items' => []]));

        self::assertSame([], $userHistoryChangeFieldsResult->getUserHistoryChangeFields());
        self::assertSame([], $userHistoryChangeFieldsResult->getFieldsDescription());
    }

    public function testPartialSelectionLeavesOmittedPropertiesNull(): void
    {
        $userHistoryChangeFieldResult = new UserHistoryChangeFieldResult($this->response(['item' => ['name' => 'data']]));
        $item = $userHistoryChangeFieldResult->getUserHistoryChangeField();

        self::assertSame('data', $item->name);
        foreach (array_keys($this->descriptor()) as $property) {
            if ($property !== 'name') {
                self::assertNull($item->$property, $property);
            }
        }
    }

    public function testExplicitNullPropertiesRemainNull(): void
    {
        $raw = array_fill_keys(array_keys($this->descriptor()), null);
        $userHistoryChangeFieldResult = new UserHistoryChangeFieldResult($this->response(['item' => $raw]));
        $item = $userHistoryChangeFieldResult->getUserHistoryChangeField();

        foreach (array_keys($raw) as $property) {
            self::assertNull($item->$property, $property);
        }
    }

    #[DataProvider('booleanProvider')]
    public function testBooleanMetadataUsesAnnotatedCasting(bool|int|string $raw, bool $expected): void
    {
        $properties = ['filterable', 'sortable', 'editable', 'multiple'];
        $userHistoryChangeFieldItemResult = new UserHistoryChangeFieldItemResult(array_fill_keys($properties, $raw));

        foreach ($properties as $property) {
            self::assertSame($expected, $userHistoryChangeFieldItemResult->$property, $property);
        }
    }

    public static function booleanProvider(): iterable
    {
        yield 'true' => [true, true];
        yield 'false' => [false, false];
        yield 'Y' => ['Y', true];
        yield 'N' => ['N', false];
        yield 'one' => [1, true];
        yield 'zero' => [0, false];
    }

    public function testFieldsDescriptionKeepsRawTypesAndNestedMetadataKeyedByName(): void
    {
        $raw = $this->descriptor();
        $partial = ['name' => 'id', 'type' => 'integer'];
        $userHistoryChangeFieldsResult = new UserHistoryChangeFieldsResult($this->response(['items' => [$raw, $partial]]));

        self::assertSame(['data' => $raw, 'id' => $partial], $userHistoryChangeFieldsResult->getFieldsDescription());
    }

    public function testFieldsDescriptionRequiresSelectedName(): void
    {
        $userHistoryChangeFieldsResult = new UserHistoryChangeFieldsResult($this->response(['items' => [['type' => 'object']]]));

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('name must be selected');
        $userHistoryChangeFieldsResult->getFieldsDescription();
    }

    public function testResultItemDeclaresMetadataSchema(): void
    {
        $reflectionClass = new ReflectionClass(UserHistoryChangeFieldItemResult::class);
        $metadata = $reflectionClass->getAttributes(OpenApiEntity::class)[0]->newInstance();

        self::assertSame('bitrix.rest.dtofielddto', $metadata->entityKey);
    }

    private function descriptor(): array
    {
        return [
            'name' => 'data',
            'type' => 'object',
            'title' => 'Example field',
            'description' => null,
            'validationRules' => [['name' => 'test', 'options' => ['nested' => true]]],
            'requiredGroups' => ['read'],
            'filterable' => 'Y',
            'sortable' => 'N',
            'editable' => false,
            'editableGroups' => ['update'],
            'multiple' => true,
            'elementType' => 'string',
        ];
    }

    private function response(array $result): Response
    {
        $httpResponse = (new MockHttpClient(new MockResponse(json_encode(['result' => $result], JSON_THROW_ON_ERROR))))
            ->request('POST', 'https://example.com/rest/');

        return new Response(
            $httpResponse,
            new Command('main.user.history.fields.field.list', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
    }
}
