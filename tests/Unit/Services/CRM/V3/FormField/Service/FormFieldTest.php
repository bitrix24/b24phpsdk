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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\FormField\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\LogicException;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\CRM\V3\FormField\Service\FormField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(FormField::class)]
class FormFieldTest extends TestCase
{
    // crm.form.field.* uses the CRM descriptor: no editableGroups, dates reported as date-time
    private const array UPDATED_TIME_FIELD = [
        'name' => 'updatedTime',
        'type' => 'date-time',
        'elementType' => null,
        'multiple' => false,
        'title' => 'updatedTime',
        'description' => null,
        'validationRules' => [],
        'requiredGroups' => null,
        'filterable' => true,
        'sortable' => true,
        'editable' => false,
    ];

    #[Test]
    public function testGetSendsNameAndSelectAndMapsDescriptor(): void
    {
        $field = $this->service('crm.form.field.get', ['name' => 'updatedTime', 'select' => ['name', 'type']], ['item' => self::UPDATED_TIME_FIELD])
            ->get('updatedTime', ['name', 'type'])
            ->field();

        $this->assertSame('updatedTime', $field->name);
        $this->assertSame('date-time', $field->type);
        $this->assertTrue($field->filterable);
        $this->assertFalse($field->editable);
    }

    #[Test]
    public function testListMapsDescriptorsAndIndexesThemByName(): void
    {
        $result = $this->service('crm.form.field.list', [], ['items' => [self::UPDATED_TIME_FIELD]])->list();

        $this->assertSame('updatedTime', $result->getFields()[0]->name);
        $this->assertSame(['updatedTime' => self::UPDATED_TIME_FIELD], $result->getFieldsDescription());
    }

    #[Test]
    public function testFieldsDescriptionRequiresName(): void
    {
        $this->expectException(LogicException::class);
        $this->service('crm.form.field.list', ['select' => ['type']], ['items' => [['type' => 'int']]])->list(['type'])->getFieldsDescription();
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        (new FormField($this->createStub(CoreInterface::class), new NullLogger()))->get('');
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(FormField::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['crm'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list'] as $method) {
            $endpoint = (new \ReflectionMethod(FormField::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('crm.form.field.' . $method, $endpoint->name);
            $this->assertSame('https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-field-' . $method . '.html', $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): FormField
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new FormField($core, new NullLogger());
    }
}
