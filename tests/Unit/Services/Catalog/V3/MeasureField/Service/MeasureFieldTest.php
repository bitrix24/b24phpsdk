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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\MeasureField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\MeasureField\Service\MeasureField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(MeasureField::class)]
class MeasureFieldTest extends TestCase
{
    private const array CODE_FIELD = [
        'name' => 'code',
        'type' => 'int',
        'title' => 'code',
        'description' => null,
        'validationRules' => [],
        'requiredGroups' => ['add'],
        'filterable' => true,
        'sortable' => true,
        'editable' => true,
        'editableGroups' => ['add', 'update'],
        'multiple' => false,
        'elementType' => null,
    ];

    #[Test]
    public function testGetSendsNameAndSelectAndMapsDescriptor(): void
    {
        $field = $this->service('catalog.measure.field.get', ['name' => 'code', 'select' => ['name', 'type']], ['item' => self::CODE_FIELD])
            ->get('code', ['name', 'type'])
            ->field();

        $this->assertSame('code', $field->name);
        $this->assertSame('int', $field->type);
        $this->assertSame(['add', 'update'], $field->editableGroups);
        $this->assertTrue($field->editable);
    }

    #[Test]
    public function testListMapsDescriptorsAndIndexesThemByName(): void
    {
        $result = $this->service('catalog.measure.field.list', [], ['items' => [self::CODE_FIELD]])->list();

        $this->assertCount(1, $result->getFields());
        $this->assertSame('code', $result->getFields()[0]->name);
        $this->assertSame(['code' => self::CODE_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        $this->service('catalog.measure.field.list', ['select' => ['type']], ['items' => [['type' => 'string']]])
            ->list(['type'])
            ->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new MeasureField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(MeasureField::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list'] as $method) {
            $endpoint = (new \ReflectionMethod(MeasureField::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('catalog.measure.field.' . $method, $endpoint->name);
            $this->assertSame(
                'https://apidocs.bitrix24.com/api-reference/catalog/measure/catalog-measure-field-' . $method . '.html',
                $endpoint->documentationUrl
            );
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): MeasureField
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new MeasureField($core, new NullLogger());
    }
}
