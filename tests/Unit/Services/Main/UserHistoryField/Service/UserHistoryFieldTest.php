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

namespace Bitrix24\SDK\Tests\Unit\Services\Main\UserHistoryField\Service;

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
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldItemResult;
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldResult;
use Bitrix24\SDK\Services\Main\UserHistoryField\Result\UserHistoryFieldsResult;
use Bitrix24\SDK\Services\Main\UserHistoryField\Service\UserHistoryField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(UserHistoryField::class)]
#[CoversClass(UserHistoryFieldResult::class)]
#[CoversClass(UserHistoryFieldsResult::class)]
#[CoversClass(UserHistoryFieldItemResult::class)]
class UserHistoryFieldTest extends TestCase
{
    #[DataProvider('selectProvider')]
    public function testGetMapsNameAndSelectToV3Call(array $select): void
    {
        $parameters = ['name' => 'dateInsert'];
        if ($select !== []) {
            $parameters['select'] = $select;
        }

        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('main.user.history.field.get', $parameters, ApiVersion::v3)
            ->willReturn($this->response(['item' => ['name' => 'dateInsert']]));

        $result = (new UserHistoryField($core, new NullLogger()))->get('dateInsert', $select);

        self::assertSame('dateInsert', $result->getUserHistoryField()->name);
    }

    #[DataProvider('selectProvider')]
    public function testListMapsSelectToV3Call(array $select): void
    {
        $parameters = $select === [] ? [] : ['select' => $select];
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('main.user.history.field.list', $parameters, ApiVersion::v3)
            ->willReturn($this->response(['items' => []]));

        $result = (new UserHistoryField($core, new NullLogger()))->list($select);

        self::assertSame([], $result->getUserHistoryFields());
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
        $userHistoryField = new UserHistoryField($core, new NullLogger());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('field name must not be empty');
        $userHistoryField->get($name);
    }

    public static function emptyNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['   '];
        yield 'whitespace' => ["\t\n"];
    }

    public function testServiceAndEndpointMetadataDeclareMainScopeAndV3(): void
    {
        $reflectionClass = new ReflectionClass(UserHistoryField::class);
        $serviceMetadata = $reflectionClass->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        self::assertSame(['main'], $serviceMetadata->scope->getScopeCodes());

        foreach (['get', 'list'] as $method) {
            $metadata = $reflectionClass->getMethod($method)->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            self::assertSame('main.user.history.field.' . $method, $metadata->name);
            self::assertSame(ApiVersion::v3, $metadata->apiVersion);
            self::assertSame('https://apidocs.bitrix24.com/api-reference/rest-v3.html', $metadata->documentationUrl);
        }
    }

    public function testSingleResultReadsItemEnvelopeAndPreservesMetadata(): void
    {
        $raw = $this->descriptor();
        $userHistoryFieldResult = new UserHistoryFieldResult($this->response(['item' => $raw]));
        $item = $userHistoryFieldResult->getUserHistoryField();

        self::assertInstanceOf(AbstractAnnotatedItem::class, $item);
        self::assertSame('dateInsert', $item->name);
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
        $userHistoryFieldsResult = new UserHistoryFieldsResult($this->response(['items' => [
            ['name' => 'dateInsert', 'type' => 'object'],
            ['name' => 'id', 'type' => 'integer'],
        ]]));

        $items = $userHistoryFieldsResult->getUserHistoryFields();

        self::assertCount(2, $items);
        self::assertInstanceOf(UserHistoryFieldItemResult::class, $items[0]);
        self::assertSame('dateInsert', $items[0]->name);
        self::assertSame('object', $items[0]->type);
        self::assertSame('id', $items[1]->name);
        self::assertSame('integer', $items[1]->type);
    }

    public function testEmptyListReturnsEmptyItemsAndDescriptions(): void
    {
        $userHistoryFieldsResult = new UserHistoryFieldsResult($this->response(['items' => []]));

        self::assertSame([], $userHistoryFieldsResult->getUserHistoryFields());
        self::assertSame([], $userHistoryFieldsResult->getFieldsDescription());
    }

    public function testPartialSelectionLeavesOmittedPropertiesNull(): void
    {
        $userHistoryFieldResult = new UserHistoryFieldResult($this->response(['item' => ['name' => 'dateInsert']]));
        $item = $userHistoryFieldResult->getUserHistoryField();

        self::assertSame('dateInsert', $item->name);
        foreach (array_keys($this->descriptor()) as $property) {
            if ($property !== 'name') {
                self::assertNull($item->$property, $property);
            }
        }
    }

    public function testExplicitNullPropertiesRemainNull(): void
    {
        $raw = array_fill_keys(array_keys($this->descriptor()), null);
        $userHistoryFieldResult = new UserHistoryFieldResult($this->response(['item' => $raw]));
        $item = $userHistoryFieldResult->getUserHistoryField();

        foreach (array_keys($raw) as $property) {
            self::assertNull($item->$property, $property);
        }
    }

    #[DataProvider('booleanProvider')]
    public function testBooleanMetadataUsesAnnotatedCasting(bool|int|string $raw, bool $expected): void
    {
        $properties = ['filterable', 'sortable', 'editable', 'multiple'];
        $userHistoryFieldItemResult = new UserHistoryFieldItemResult(array_fill_keys($properties, $raw));

        foreach ($properties as $property) {
            self::assertSame($expected, $userHistoryFieldItemResult->$property, $property);
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
        $userHistoryFieldsResult = new UserHistoryFieldsResult($this->response(['items' => [$raw, $partial]]));

        self::assertSame(['dateInsert' => $raw, 'id' => $partial], $userHistoryFieldsResult->getFieldsDescription());
    }

    public function testFieldsDescriptionRequiresSelectedName(): void
    {
        $userHistoryFieldsResult = new UserHistoryFieldsResult($this->response(['items' => [['type' => 'object']]]));

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('name must be selected');
        $userHistoryFieldsResult->getFieldsDescription();
    }

    public function testResultItemDeclaresMetadataSchema(): void
    {
        $reflectionClass = new ReflectionClass(UserHistoryFieldItemResult::class);
        $metadata = $reflectionClass->getAttributes(OpenApiEntity::class)[0]->newInstance();

        self::assertSame('bitrix.rest.dtofielddto', $metadata->entityKey);
    }

    private function descriptor(): array
    {
        return [
            'name' => 'dateInsert',
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
            new Command('main.user.history.field.list', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
    }
}
