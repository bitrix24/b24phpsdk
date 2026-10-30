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

namespace Bitrix24\SDK\Services\CRM\V3\Form\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\ItemBuilderInterface;
use Bitrix24\SDK\Core\Contracts\SelectBuilderInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Exceptions\VersionMismatchException;
use Bitrix24\SDK\Filters\FilterBuilderInterface;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\AddedFormResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\DeletedFormResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormActivityResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormsResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\FormValidationResult;
use Bitrix24\SDK\Services\CRM\V3\Form\Result\UpdatedFormResult;
use Carbon\CarbonImmutable;

/**
 * CRM forms (REST v3). Changes use optimistic concurrency: pass the `updatedTime` read from get()
 * as the form version; a stale version throws VersionMismatchException with the current version.
 */
#[ApiServiceMetadata(new Scope(['crm']))]
class Form extends AbstractService
{
    /**
     * Returns a CRM form by ID; without select only scalar fields are returned, nested blocks must be selected.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-get.html
     *
     * @param array<int, string>|FormSelectBuilder $select
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.get',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-get.html',
        'Returns a CRM form by ID',
        ApiVersion::v3
    )]
    public function get(int $id, array|FormSelectBuilder $select = []): FormResult
    {
        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new FormResult(
            $this->core->call('crm.form.get', $this->withoutEmpty(['id' => $id, 'select' => $select]), ApiVersion::v3)
        );
    }

    /**
     * Returns a list of CRM forms.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-list.html
     *
     * @param array<int, mixed>|FilterBuilderInterface $filter
     * @param array<int, string>|FormSelectBuilder     $select
     * @param array<string, string>                    $order  by id, name, active, createdTime or updatedTime
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.list',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-list.html',
        'Returns a list of CRM forms',
        ApiVersion::v3
    )]
    public function list(
        array|FilterBuilderInterface $filter = [],
        array|FormSelectBuilder $select = [],
        array $order = [],
        ?FormListPagination $pagination = null,
    ): FormsResult {
        if ($filter instanceof FilterBuilderInterface) {
            $filter = $filter->toArray();
        }

        if ($select instanceof SelectBuilderInterface) {
            $select = $select->buildSelect();
        }

        return new FormsResult(
            $this->core->call(
                'crm.form.list',
                $this->withoutEmpty([
                    'filter'     => $filter,
                    'select'     => $select,
                    'order'      => $order,
                    'pagination' => $pagination?->toArray(),
                ]),
                ApiVersion::v3
            )
        );
    }

    /**
     * Creates a CRM form; name and crm.entityScheme are required.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-add.html
     *
     * @param array<string, mixed>|FormItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.add',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-add.html',
        'Creates a CRM form',
        ApiVersion::v3
    )]
    public function add(array|FormItemBuilder $fields): AddedFormResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new AddedFormResult($this->core->call('crm.form.add', ['fields' => $fields], ApiVersion::v3));
    }

    /**
     * Partially updates a CRM form: only the passed fields change.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-update.html
     *
     * @param CarbonImmutable                      $updatedTime form version read from get()
     * @param array<string, mixed>|FormItemBuilder $fields
     * @throws VersionMismatchException when the form changed after $updatedTime
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.update',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-update.html',
        'Partially updates a CRM form',
        ApiVersion::v3
    )]
    public function update(int $id, CarbonImmutable $updatedTime, array|FormItemBuilder $fields): UpdatedFormResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        return new UpdatedFormResult(
            $this->core->call(
                'crm.form.update',
                ['id' => $id, 'updatedTime' => $updatedTime->toIso8601String(), 'fields' => $fields],
                ApiVersion::v3
            )
        );
    }

    /**
     * Deletes a CRM form permanently. An active form is deleted only with $confirmed = true
     * (otherwise CRM_FORM_DELETE_CONFIRMATION_REQUIRED); system forms cannot be deleted.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-delete.html
     *
     * @param CarbonImmutable|null $updatedTime when passed, the form is deleted only if it is unchanged
     * @throws VersionMismatchException
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.delete',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-delete.html',
        'Deletes a CRM form',
        ApiVersion::v3
    )]
    public function delete(int $id, ?CarbonImmutable $updatedTime = null, bool $confirmed = false): DeletedFormResult
    {
        return new DeletedFormResult(
            $this->core->call(
                'crm.form.delete',
                $this->withoutEmpty(['id' => $id, 'confirmed' => $confirmed, 'updatedTime' => $updatedTime?->toIso8601String()]),
                ApiVersion::v3
            )
        );
    }

    /**
     * Activates a CRM form; may fail with CRM_FORM_ACTIVATION_LIMIT_REACHED on the current plan.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-activate.html
     *
     * @param CarbonImmutable|null $updatedTime when passed, the form is activated only if it is unchanged
     * @throws VersionMismatchException
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.activate',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-activate.html',
        'Activates a CRM form',
        ApiVersion::v3
    )]
    public function activate(int $id, ?CarbonImmutable $updatedTime = null): FormActivityResult
    {
        return $this->changeActivity('crm.form.activate', $id, $updatedTime);
    }

    /**
     * Deactivates a CRM form.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-deactivate.html
     *
     * @param CarbonImmutable|null $updatedTime when passed, the form is deactivated only if it is unchanged
     * @throws VersionMismatchException
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.deactivate',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-deactivate.html',
        'Deactivates a CRM form',
        ApiVersion::v3
    )]
    public function deactivate(int $id, ?CarbonImmutable $updatedTime = null): FormActivityResult
    {
        return $this->changeActivity('crm.form.deactivate', $id, $updatedTime);
    }

    /**
     * Validates settings of a new form, or a change of an existing form when $id is passed; nothing is saved.
     *
     * @link https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-validate.html
     *
     * @param array<string, mixed>|FormItemBuilder $fields
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'crm.form.validate',
        'https://apidocs.bitrix24.com/api-reference/crm/form/crm-form-validate.html',
        'Validates CRM form settings without saving them',
        ApiVersion::v3
    )]
    public function validate(array|FormItemBuilder $fields, ?int $id = null): FormValidationResult
    {
        if ($fields instanceof ItemBuilderInterface) {
            $fields = $fields->build();
        }

        $parameters = $id === null ? ['fields' => $fields] : ['id' => $id, 'fields' => $fields];

        return new FormValidationResult($this->core->call('crm.form.validate', $parameters, ApiVersion::v3));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    private function changeActivity(string $method, int $id, ?CarbonImmutable $updatedTime): FormActivityResult
    {
        return new FormActivityResult(
            $this->core->call(
                $method,
                $this->withoutEmpty(['id' => $id, 'updatedTime' => $updatedTime?->toIso8601String()]),
                ApiVersion::v3
            )
        );
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function withoutEmpty(array $parameters): array
    {
        return array_filter($parameters, static fn (mixed $value): bool => $value !== null && $value !== []);
    }
}
