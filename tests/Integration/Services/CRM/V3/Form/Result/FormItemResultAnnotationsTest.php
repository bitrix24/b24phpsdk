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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\Form\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;
use Bitrix24\SDK\Services\CRM\V3\CRMV3ServiceBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAgreementItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAgreementsItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAnalyticsItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAppearanceItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormBookingItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormCallbackItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormCrmSettingsItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormFieldItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormIntegrationsItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormPaymentItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormResponsibleItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormSpamProtectionItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormSubmissionItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormSubmissionMessageItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormSubmissionRefillItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormItemResult::class)]
class FormItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    private CRMV3ServiceBuilder $crm;

    #[\Override]
    protected function setUp(): void
    {
        $this->crm = Factory::getServiceBuilder()->getCRMScope()->v3();
    }

    #[Test]
    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = $this->crm->formField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), FormItemResult::class);
    }

    #[Test]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = $this->crm->formField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);

        $annotations = $this->getPropertyAnnotations(FormItemResult::class);
        foreach ($fields as $name => $field) {
            // the CRM descriptor reports dates as date-time
            if ($field['type'] === 'date-time') {
                $fields[$name]['type'] = 'datetime';
            }

            // nested blocks are typed *ItemResult classes or arrays of them; their keys are checked below
            if ($field['type'] === 'object') {
                $this->assertMatchesRegularExpression('/ItemResult|array/', $annotations[$name], sprintf('nested block «%s»', $name));
                unset($fields[$name]);
            }
        }

        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, FormItemResult::class);
    }

    #[Test]
    public function testNestedBlocksAreAnnotatedAndCast(): void
    {
        $raw = $this->crm->form()->get($this->firstFormId(), $this->fullSelect())->getCoreResponse()->getResponseData()->getResult()['item'];
        $form = $this->crm->form()->get($this->firstFormId(), $this->fullSelect())->form();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($form, FormItemResult::class);

        $blocks = [
            'appearance' => FormAppearanceItemResult::class,
            'crm' => FormCrmSettingsItemResult::class,
            'responsible' => FormResponsibleItemResult::class,
            'agreements' => FormAgreementsItemResult::class,
            'submission' => FormSubmissionItemResult::class,
            'payment' => FormPaymentItemResult::class,
            'callback' => FormCallbackItemResult::class,
            'booking' => FormBookingItemResult::class,
            'analytics' => FormAnalyticsItemResult::class,
            'spamProtection' => FormSpamProtectionItemResult::class,
            'integrations' => FormIntegrationsItemResult::class,
        ];
        foreach ($blocks as $block => $class) {
            $this->assertNestedItem($raw[$block], $form->$block, $class);
        }

        $this->assertNestedItem($raw['submission']['success'], $form->submission?->success, FormSubmissionMessageItemResult::class);
        $this->assertNestedItem($raw['submission']['refill'], $form->submission?->refill, FormSubmissionRefillItemResult::class);
        foreach ($raw['agreements']['items'] as $index => $agreement) {
            $this->assertNestedItem($agreement, $form->agreements?->items[$index], FormAgreementItemResult::class);
        }

        foreach ($raw['fields'] as $index => $field) {
            $this->assertNestedItem($field, $form->fields[$index], FormFieldItemResult::class);
        }
    }

    /**
     * @param array<string, mixed> $raw
     * @param class-string         $class
     */
    private function assertNestedItem(array $raw, mixed $item, string $class): void
    {
        $this->assertInstanceOf($class, $item);
        $this->assertInstanceOf(AbstractItem::class, $item);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($raw), $class);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($item, $class);
    }

    private function firstFormId(): int
    {
        $forms = $this->crm->form()->list([], (new FormSelectBuilder()), ['id' => 'ASC'])->getForms();
        self::assertNotEmpty($forms, 'test portal must have at least one CRM form');

        return $forms[0]->id;
    }

    private function fullSelect(): FormSelectBuilder
    {
        return (new FormSelectBuilder())
            ->code()->name()->active()->system()->languageId()->templateId()->createdTime()->updatedTime()
            ->activeChangedById()->activeChangedTime()->appearance()->crm()->dependencies()->presetFields()
            ->responsible()->agreements()->submission()->payment()->callback()->booking()->analytics()
            ->spamProtection()->integrations()->fields();
    }

    /**
     * @param class-string $class
     * @return array<string, string>
     */
    private function getPropertyAnnotations(string $class): array
    {
        $annotations = [];
        foreach (\Typhoon\Reflection\TyphoonReflector::build()->reflectClass($class)->properties() as $property) {
            if ($property->isAnnotated() && !$property->isNative()) {
                $annotations[$property->id->name] = \Typhoon\Type\stringify($property->type());
            }
        }

        return $annotations;
    }
}
