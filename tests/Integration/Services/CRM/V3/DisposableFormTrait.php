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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\V3;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\Form;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormItemBuilder;
use Bitrix24\SDK\Services\CRM\V3\Form\Service\FormSelectBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;

/**
 * Creates inactive CRM forms for a test and removes them afterwards; system forms are never touched.
 */
trait DisposableFormTrait
{
    /** @var int[] */
    private array $disposableFormIds = [];

    protected function createDisposableForm(): int
    {
        $id = $this->formService()->add(
            (new FormItemBuilder())
                ->name('sdk v3 form')
                ->active(false)
                ->crm(['entityScheme' => 1])
                ->appearance(['title' => 'Leave a request'])
                ->fields([['name' => 'LEAD_NAME', 'type' => 'string', 'label' => 'Name', 'required' => true]])
        )->getId();
        $this->disposableFormIds[] = $id;

        return $id;
    }

    protected function formVersion(int $id): CarbonImmutable
    {
        $version = $this->formService()->get($id, (new FormSelectBuilder())->updatedTime())->form()->updatedTime;
        self::assertInstanceOf(CarbonImmutable::class, $version);

        return $version;
    }

    protected function deleteDisposableForms(): void
    {
        foreach ($this->disposableFormIds as $id) {
            try {
                $this->formService()->delete($id, null, true);
            } catch (BaseException) {
                // already deleted by the test
            }
        }
    }

    protected function formService(): Form
    {
        return Factory::getServiceBuilder()->getCRMScope()->v3()->form();
    }
}
