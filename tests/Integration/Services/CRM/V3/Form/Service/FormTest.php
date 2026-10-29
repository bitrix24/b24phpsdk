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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3\Form\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\VersionMismatchException;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormAppearanceItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormFieldItemResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\Form;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormFilter;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormItemBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormListPagination;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Works only with disposable forms created by the test; system forms are never modified.
 */
#[CoversClass(Form::class)]
class FormTest extends TestCase
{
    private Form $formService;

    /** @var int[] */
    private array $createdIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->formService = Factory::getServiceBuilder()->getCRMScope()->v3()->form();
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            try {
                $this->formService->delete($id, null, true);
            } catch (BaseException) {
                // already deleted by the test
            }
        }
    }

    #[Test]
    public function testAddAndGetReturnNestedBlocks(): void
    {
        $id = $this->createForm();

        $form = $this->formService->get(
            $id,
            (new FormSelectBuilder())->name()->active()->system()->updatedTime()->appearance()->crm()->fields()
        )->form();

        $this->assertSame($id, $form->id);
        $this->assertSame('sdk v3 form', $form->name);
        $this->assertFalse($form->active);
        $this->assertFalse($form->system);
        $this->assertInstanceOf(CarbonImmutable::class, $form->updatedTime);
        $this->assertInstanceOf(FormAppearanceItemResult::class, $form->appearance);
        $this->assertSame('Leave a request', $form->appearance->title);
        $this->assertSame(1, $form->crm?->entityScheme);
        $this->assertInstanceOf(FormFieldItemResult::class, $form->fields[0]);
        $this->assertSame('LEAD_NAME', $form->fields[0]->name);
    }

    #[Test]
    public function testValidateReportsViolationsWithoutSaving(): void
    {
        $invalid = $this->formService->validate((new FormItemBuilder())->name(''));
        $this->assertFalse($invalid->isValid());
        $this->assertContains('name', array_map(static fn ($violation): string => $violation->field, $invalid->getViolations()));

        $id = $this->createForm();
        $this->assertTrue($this->formService->validate((new FormItemBuilder())->appearance(['title' => 'Changed']), $id)->isValid());
        $this->assertSame('Leave a request', $this->formService->get($id, (new FormSelectBuilder())->appearance())->form()->appearance?->title);
    }

    #[Test]
    public function testUpdateUsesOptimisticConcurrency(): void
    {
        $id = $this->createForm();
        $version = $this->currentVersion($id);
        // updatedTime has a one-second resolution
        sleep(1);

        $this->assertTrue($this->formService->update($id, $version, (new FormItemBuilder())->appearance(['title' => 'Updated title']))->isSuccess());
        $newVersion = $this->currentVersion($id);
        $this->assertTrue($newVersion->greaterThan($version));

        try {
            $this->formService->update($id, $version, (new FormItemBuilder())->appearance(['title' => 'Stale title']));
            $this->fail('A stale version must be rejected');
        } catch (VersionMismatchException $versionMismatchException) {
            $this->assertTrue($newVersion->equalTo($versionMismatchException->getCurrentVersion()));
        }

        $this->assertSame('Updated title', $this->formService->get($id, (new FormSelectBuilder())->appearance())->form()->appearance?->title);
    }

    #[Test]
    public function testActivateDeactivateAndDeleteConfirmation(): void
    {
        $id = $this->createForm();

        try {
            $activated = $this->formService->activate($id);
        } catch (BaseException $baseException) {
            if (str_contains($baseException->getMessage(), 'crm_form_activation_limit_reached')) {
                $this->markTestSkipped('the test portal reached its active CRM form limit');
            }

            throw $baseException;
        }

        $this->assertTrue($activated->isActive());

        try {
            $this->formService->delete($id);
            $this->fail('An active form must not be deleted without confirmation');
        } catch (BaseException $baseException) {
            $this->assertStringContainsString('crm_form_delete_confirmation_required', $baseException->getMessage());
        }

        $deactivated = $this->formService->deactivate($id, $activated->getUpdatedTime());
        $this->assertFalse($deactivated->isActive());

        $this->assertTrue($this->formService->delete($id, $deactivated->getUpdatedTime())->isSuccess());

        $filter = new FormFilter();
        $filter->id()->eq($id);
        $this->assertSame([], $this->formService->list($filter)->getForms());
    }

    #[Test]
    public function testListFiltersOrdersAndPaginates(): void
    {
        $firstId = $this->createForm();
        $secondId = $this->createForm();

        $filter = new FormFilter();
        $filter->id()->in([$firstId, $secondId]);
        $filter->active()->eq(false);

        $forms = $this->formService->list($filter, (new FormSelectBuilder())->name(), ['id' => 'DESC'], new FormListPagination(limit: 1))->getForms();

        $this->assertCount(1, $forms);
        $this->assertSame($secondId, $forms[0]->id);
    }

    private function createForm(): int
    {
        $id = $this->formService->add(
            (new FormItemBuilder())
                ->name('sdk v3 form')
                ->active(false)
                ->crm(['entityScheme' => 1])
                ->appearance(['title' => 'Leave a request'])
                ->fields([['name' => 'LEAD_NAME', 'type' => 'string', 'label' => 'Name', 'required' => true]])
        )->getId();
        $this->createdIds[] = $id;

        return $id;
    }

    private function currentVersion(int $id): CarbonImmutable
    {
        $version = $this->formService->get($id, (new FormSelectBuilder())->updatedTime())->form()->updatedTime;
        $this->assertInstanceOf(CarbonImmutable::class, $version);

        return $version;
    }
}
