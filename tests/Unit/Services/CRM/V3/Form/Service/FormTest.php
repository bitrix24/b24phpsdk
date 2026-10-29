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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\V3\Form\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Contracts\AddedItemIdResultInterface;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAgreementItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAppearanceItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormCrmSettingsItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormFieldItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormSubmissionMessageItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\Form;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormFilter;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormItemBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormListPagination;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormSelectBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(Form::class)]
class FormTest extends TestCase
{
    private const string UPDATED_TIME = '2026-10-29T14:15:32+03:00';

    private const array FORM_ITEM = [
        'id' => 4,
        'code' => '4_feedback',
        'name' => 'Feedback',
        'active' => true,
        'system' => true,
        'languageId' => '',
        'templateId' => 'feedback',
        'createdTime' => '2022-01-15T23:42:39+03:00',
        'updatedTime' => self::UPDATED_TIME,
        'activeChangedById' => 1,
        'activeChangedTime' => '2022-01-15T23:42:38+03:00',
        'appearance' => ['title' => 'Feedback', 'description' => 'Ask us', 'buttonCaption' => 'Send', 'buttonTextColor' => null, 'buttonBackgroundColor' => null, 'backgroundImageId' => null, 'copyrightRemoved' => false],
        'crm' => ['entityScheme' => 3, 'duplicateMode' => 'MERGE', 'dealCategoryId' => null, 'dealDuplicateControlEnabled' => true, 'smartProcessCategoryId' => null, 'smartProcessDuplicateControlEnabled' => false],
        'dependencies' => [],
        'presetFields' => [['entityName' => 'LEAD', 'fieldName' => 'SOURCE_ID', 'value' => 'WEBFORM']],
        'responsible' => ['userIds' => [1], 'checkWorkTime' => false],
        'agreements' => ['enabled' => true, 'items' => [['agreementId' => 2, 'checked' => true, 'required' => true]]],
        'submission' => ['success' => ['url' => null, 'text' => 'Thanks'], 'failure' => ['url' => null, 'text' => 'Sorry'], 'redirectDelay' => 5, 'refill' => ['enabled' => false, 'caption' => '']],
        'payment' => ['enabled' => false, 'disabledPaySystemsId' => []],
        'callback' => ['enabled' => false, 'from' => null, 'text' => null],
        'booking' => ['enabled' => false],
        'analytics' => ['googleAnalyticsId' => null, 'googleAnalyticsPageViewEnabled' => false, 'yandexMetricaId' => null],
        'spamProtection' => ['enabled' => false],
        'integrations' => ['whatsAppEnabled' => false, 'ads' => []],
        'fields' => [['name' => 'CONTACT_NAME', 'type' => 'string', 'label' => 'Name', 'sort' => 100, 'required' => false, 'multiple' => false, 'placeholder' => '', 'valueType' => null, 'value' => null]],
    ];

    #[Test]
    public function testGetSendsSelectAndMapsScalarAndNestedBlocks(): void
    {
        // nested blocks are selected by dotted paths, which the API accepts (verified live)
        $select = (new FormSelectBuilder())->appearance()->crm()->fields();
        $this->assertContains('appearance.title', $select->buildSelect());
        $this->assertContains('crm.entityScheme', $select->buildSelect());

        $form = $this->service('crm.form.get', ['id' => 4, 'select' => $select->buildSelect()], ['item' => self::FORM_ITEM])
            ->get(4, $select)
            ->form();

        $this->assertSame(4, $form->id);
        $this->assertTrue($form->active);
        $this->assertTrue($form->system);
        $this->assertSame(self::UPDATED_TIME, $form->updatedTime?->toIso8601String());
        $this->assertInstanceOf(CarbonImmutable::class, $form->createdTime);
        $this->assertSame(1, $form->activeChangedById);

        $this->assertInstanceOf(FormAppearanceItemResult::class, $form->appearance);
        $this->assertSame('Send', $form->appearance->buttonCaption);
        $this->assertFalse($form->appearance->copyrightRemoved);
        $this->assertInstanceOf(FormCrmSettingsItemResult::class, $form->crm);
        $this->assertSame(3, $form->crm->entityScheme);
        $this->assertSame([1], $form->responsible?->userIds);
        $this->assertInstanceOf(FormAgreementItemResult::class, $form->agreements?->items[0]);
        $this->assertSame(2, $form->agreements->items[0]->agreementId);
        $this->assertInstanceOf(FormSubmissionMessageItemResult::class, $form->submission?->success);
        $this->assertSame('Thanks', $form->submission->success->text);
        $this->assertSame(5, $form->submission->redirectDelay);
        $this->assertFalse($form->submission->refill?->enabled);
        $this->assertSame('SOURCE_ID', $form->presetFields[0]->fieldName);
        $this->assertFalse($form->integrations?->whatsAppEnabled);
        $this->assertInstanceOf(FormFieldItemResult::class, $form->fields[0]);
        $this->assertSame('CONTACT_NAME', $form->fields[0]->name);
        $this->assertSame(100, $form->fields[0]->sort);
    }

    #[Test]
    public function testGetOmitsEmptySelect(): void
    {
        $this->service('crm.form.get', ['id' => 4], ['item' => ['id' => 4, 'name' => 'Feedback']])->get(4);
    }

    #[Test]
    public function testListSendsFilterSelectOrderAndPagination(): void
    {
        $filter = new FormFilter();
        $filter->active()->eq(true);

        $forms = $this->service(
            'crm.form.list',
            [
                'filter' => [['active', '=', true]],
                'select' => ['id', 'name'],
                'order' => ['updatedTime' => 'DESC'],
                'pagination' => ['page' => 2, 'limit' => 20],
            ],
            ['items' => [self::FORM_ITEM]]
        )->list($filter, (new FormSelectBuilder())->name(), ['updatedTime' => 'DESC'], new FormListPagination(page: 2, limit: 20))->getForms();

        $this->assertCount(1, $forms);
        $this->assertSame(4, $forms[0]->id);
    }

    #[Test]
    public function testListWithoutArgumentsSendsEmptyPayload(): void
    {
        $this->assertSame([], $this->service('crm.form.list', [], ['items' => []])->list()->getForms());
    }

    #[Test]
    public function testAddSendsFieldsAndReturnsId(): void
    {
        $result = $this->service(
            'crm.form.add',
            ['fields' => ['name' => 'Consultation', 'active' => false, 'crm' => ['entityScheme' => 1], 'fields' => [['name' => 'LEAD_NAME', 'type' => 'string', 'label' => 'Name', 'required' => true]]]],
            ['id' => 12]
        )->add(
            (new FormItemBuilder())
                ->name('Consultation')
                ->active(false)
                ->crm(['entityScheme' => 1])
                ->fields([['name' => 'LEAD_NAME', 'type' => 'string', 'label' => 'Name', 'required' => true]])
        );

        $this->assertInstanceOf(AddedItemIdResultInterface::class, $result);
        $this->assertSame(12, $result->getId());
    }

    #[Test]
    public function testUpdateSendsVersionAsIsoDateTime(): void
    {
        $this->assertTrue(
            $this->service(
                'crm.form.update',
                ['id' => 12, 'updatedTime' => self::UPDATED_TIME, 'fields' => ['appearance' => ['title' => 'New title']]],
                ['result' => true]
            )->update(12, CarbonImmutable::parse(self::UPDATED_TIME), (new FormItemBuilder())->appearance(['title' => 'New title']))->isSuccess()
        );
    }

    #[Test]
    public function testDeleteSendsConfirmationAndOptionalVersion(): void
    {
        $this->assertTrue(
            $this->service('crm.form.delete', ['id' => 12, 'confirmed' => false], ['result' => true])->delete(12)->isSuccess()
        );
        $this->assertTrue(
            $this->service('crm.form.delete', ['id' => 12, 'confirmed' => true, 'updatedTime' => self::UPDATED_TIME], ['result' => true])
                ->delete(12, CarbonImmutable::parse(self::UPDATED_TIME), true)
                ->isSuccess()
        );
    }

    #[Test]
    public function testActivateReturnsStateAndNewVersion(): void
    {
        $result = $this->service('crm.form.activate', ['id' => 12], ['active' => true, 'updatedTime' => self::UPDATED_TIME])->activate(12);

        $this->assertTrue($result->isActive());
        $this->assertSame(self::UPDATED_TIME, $result->getUpdatedTime()->toIso8601String());
    }

    #[Test]
    public function testDeactivateSendsOptionalVersion(): void
    {
        $result = $this->service('crm.form.deactivate', ['id' => 12, 'updatedTime' => self::UPDATED_TIME], ['active' => false, 'updatedTime' => self::UPDATED_TIME])
            ->deactivate(12, CarbonImmutable::parse(self::UPDATED_TIME));

        $this->assertFalse($result->isActive());
    }

    #[Test]
    public function testValidateReturnsViolationsWithoutPersisting(): void
    {
        $result = $this->service(
            'crm.form.validate',
            ['fields' => ['name' => '']],
            ['valid' => false, 'violations' => [['field' => 'name', 'message' => 'Name of the CRM form is required']]]
        )->validate((new FormItemBuilder())->name(''));

        $this->assertFalse($result->isValid());
        $this->assertSame('name', $result->getViolations()[0]->field);
        $this->assertSame('Name of the CRM form is required', $result->getViolations()[0]->message);
    }

    #[Test]
    public function testValidateChangeOfExistingFormSendsId(): void
    {
        $result = $this->service('crm.form.validate', ['id' => 12, 'fields' => ['appearance' => ['title' => 'x']]], ['valid' => true, 'violations' => []])
            ->validate(['appearance' => ['title' => 'x']], 12);

        $this->assertTrue($result->isValid());
        $this->assertSame([], $result->getViolations());
    }

    #[Test]
    public function testMetadataTargetsRestV3(): void
    {
        $metadata = (new \ReflectionClass(Form::class))->getAttributes(ApiServiceMetadata::class)[0]->newInstance();
        $this->assertSame(['crm'], $metadata->scope->getScopeCodes());
        foreach (['get', 'list', 'add', 'update', 'delete', 'activate', 'deactivate', 'validate'] as $method) {
            $endpoint = (new \ReflectionMethod(Form::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            $this->assertSame('crm.form.' . $method, $endpoint->name);
            $this->assertSame('https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-' . $method . '.html', $endpoint->documentationUrl);
            $this->assertSame(ApiVersion::v3, $endpoint->apiVersion);
        }
    }

    private function service(string $method, array $parameters, array $result): Form
    {
        $response = new Response(
            (new MockHttpClient(new JsonMockResponse(['result' => $result])))->request('POST', 'https://example.test'),
            new Command($method, $parameters),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with($method, $parameters, ApiVersion::v3)->willReturn($response);

        return new Form($core, new NullLogger());
    }
}
