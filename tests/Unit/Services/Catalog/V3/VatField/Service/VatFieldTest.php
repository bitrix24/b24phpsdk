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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\V3\VatField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\Catalog\V3\VatField\Service\VatField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(VatField::class)]
class VatFieldTest extends TestCase
{
    private const array RATE_FIELD = [
        'name' => 'rate',
        'type' => 'float',
        'title' => 'rate',
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
        $field = $this->service('catalog.vat.field.get', ['name' => 'rate', 'select' => ['name', 'type']], ['item' => self::RATE_FIELD])
            ->get('rate', ['name', 'type'])
            ->field();

        $this->assertSame('rate', $field->name);
        $this->assertSame('float', $field->type);
        $this->assertSame(['add', 'update'], $field->editableGroups);
        $this->assertTrue($field->editable);
    }

    #[Test]
    public function testListMapsDescriptorsAndIndexesThemByName(): void
    {
        $result = $this->service('catalog.vat.field.list', [], ['items' => [self::RATE_FIELD]])->list();

        $this->assertCount(1, $result->getFields());
        $this->assertSame('rate', $result->getFields()[0]->name);
        $this->assertSame(['rate' => self::RATE_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        $this->service('catalog.vat.field.list', ['select' => ['type']], ['items' => [['type' => 'int']]])
            ->list(['type'])
            ->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new VatField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(VatField::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['catalog'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list'] as $method) {
            $endpoint = (new \ReflectionMethod(VatField::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('catalog.vat.field.' . $method, $endpoint->name);
            $this->assertSame(
                'https://apidocs.bitrix24.com/api-reference/catalog/vat/catalog-vat-field-' . $method . '.html',
                $endpoint->documentationUrl
            );
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): VatField
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new VatField($core, new NullLogger());
    }
}
