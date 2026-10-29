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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormEmbeddingField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\CRM\V3\FormEmbeddingField\Service\FormEmbeddingField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(FormEmbeddingField::class)]
class FormEmbeddingFieldTest extends TestCase
{
    private const array CLICK_FIELD = [
        'name' => 'click',
        'type' => 'object',
        'title' => 'click',
        'description' => null,
        'validationRules' => [],
        'requiredGroups' => null,
        'filterable' => false,
        'sortable' => false,
        'editable' => true,
        'editableGroups' => ['update'],
        'multiple' => false,
        'elementType' => null,
    ];

    #[Test]
    public function testGetSendsNameAndSelectAndMapsDescriptor(): void
    {
        $field = $this->service('crm.form.embedding.field.get', ['name' => 'click', 'select' => ['name', 'editableGroups']], ['item' => self::CLICK_FIELD])
            ->get('click', ['name', 'editableGroups'])
            ->field();

        $this->assertSame('click', $field->name);
        $this->assertSame('object', $field->type);
        $this->assertSame(['update'], $field->editableGroups);
    }

    #[Test]
    public function testListMapsDescriptorsAndIndexesThemByName(): void
    {
        $result = $this->service('crm.form.embedding.field.list', [], ['items' => [self::CLICK_FIELD]])->list();

        $this->assertSame('click', $result->getFields()[0]->name);
        $this->assertSame(['click' => self::CLICK_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        $this->service('crm.form.embedding.field.list', ['select' => ['type']], ['items' => [['type' => 'bool']]])->list(['type'])->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new FormEmbeddingField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(FormEmbeddingField::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['crm'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list'] as $method) {
            $endpoint = (new \ReflectionMethod(FormEmbeddingField::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('crm.form.embedding.field.' . $method, $endpoint->name);
            $this->assertSame('https://apidocs.bitrix24.com/api-reference/crm/form/placement/crm-form-embedding-field-' . $method . '.html', $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): FormEmbeddingField
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new FormEmbeddingField($core, new NullLogger());
    }
}
