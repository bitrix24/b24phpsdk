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

use Bitrix24\SDK\Services\AbstractItemBuilder;

/**
 * Fields for crm.form.add, crm.form.update and crm.form.validate.
 *
 * Written manually: the OpenAPI snapshot describes `fields` as a free-form object, so the item builder
 * generator has no properties to generate. Nested blocks use the same shape as crm.form.get returns.
 */
class FormItemBuilder extends AbstractItemBuilder
{
    public function name(string $name): self
    {
        $this->fields['name'] = $name;
        return $this;
    }

    public function active(bool $active): self
    {
        $this->fields['active'] = $active;
        return $this;
    }

    public function languageId(string $languageId): self
    {
        $this->fields['languageId'] = $languageId;
        return $this;
    }

    public function templateId(string $templateId): self
    {
        $this->fields['templateId'] = $templateId;
        return $this;
    }

    /**
     * @param array<mixed> $crm CRM scheme, duplicate mode and categories, e.g. ['entityScheme' => 1]
     */
    public function crm(array $crm): self
    {
        $this->fields['crm'] = $crm;
        return $this;
    }

    /**
     * @param array<mixed> $appearance title, description and button
     */
    public function appearance(array $appearance): self
    {
        $this->fields['appearance'] = $appearance;
        return $this;
    }

    /**
     * @param array<mixed> $responsible responsible users queue
     */
    public function responsible(array $responsible): self
    {
        $this->fields['responsible'] = $responsible;
        return $this;
    }

    /**
     * @param array<mixed> $agreements consents shown in the form
     */
    public function agreements(array $agreements): self
    {
        $this->fields['agreements'] = $agreements;
        return $this;
    }

    /**
     * @param array<mixed> $submission messages and redirect after submission
     */
    public function submission(array $submission): self
    {
        $this->fields['submission'] = $submission;
        return $this;
    }

    /**
     * @param array<mixed> $payment payment settings
     */
    public function payment(array $payment): self
    {
        $this->fields['payment'] = $payment;
        return $this;
    }

    /**
     * @param array<mixed> $callback callback settings
     */
    public function callback(array $callback): self
    {
        $this->fields['callback'] = $callback;
        return $this;
    }

    /**
     * @param array<mixed> $booking booking settings
     */
    public function booking(array $booking): self
    {
        $this->fields['booking'] = $booking;
        return $this;
    }

    /**
     * @param array<mixed> $analytics analytics counters
     */
    public function analytics(array $analytics): self
    {
        $this->fields['analytics'] = $analytics;
        return $this;
    }

    /**
     * @param array<mixed> $spamProtection spam protection
     */
    public function spamProtection(array $spamProtection): self
    {
        $this->fields['spamProtection'] = $spamProtection;
        return $this;
    }

    /**
     * @param array<mixed> $integrations messenger and ads integrations
     */
    public function integrations(array $integrations): self
    {
        $this->fields['integrations'] = $integrations;
        return $this;
    }

    /**
     * @param array<mixed> $dependencies field dependency groups
     */
    public function dependencies(array $dependencies): self
    {
        $this->fields['dependencies'] = $dependencies;
        return $this;
    }

    /**
     * @param array<mixed> $presetFields hidden preset field values
     */
    public function presetFields(array $presetFields): self
    {
        $this->fields['presetFields'] = $presetFields;
        return $this;
    }

    /**
     * @param array<mixed> $fields form fields, e.g. [['name' => 'LEAD_NAME', 'type' => 'string', 'required' => true]]
     */
    public function fields(array $fields): self
    {
        $this->fields['fields'] = $fields;
        return $this;
    }
}
