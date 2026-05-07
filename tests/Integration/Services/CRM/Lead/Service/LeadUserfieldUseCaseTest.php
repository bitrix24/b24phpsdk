<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Lead\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Services\CRM\Lead\Service\LeadUserfield;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Lead\Service\LeadUserfield::class)]
class LeadUserfieldUseCaseTest extends TestCase
{
    protected Lead $leadService;

    protected LeadUserfield $leadUserfieldService;

    protected int $leadUserfieldId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testOperationsWithUserfieldFromLeadItem(): void
    {
        // get userfield metadata
        $leadUserfieldItemResult = $this->leadUserfieldService->get($this->leadUserfieldId)->userfieldItem();
        $ufOriginalFieldName = $leadUserfieldItemResult->getOriginalFieldName();
        $ufFieldName = $leadUserfieldItemResult->FIELD_NAME;

        // add lead with uf value
        $fieldNameValue = 'test field value';
        $newLeadId = $this->leadService->add(
            [
                'TITLE'      => 'test lead',
                $ufFieldName => $fieldNameValue,
            ]
        )->getId();
        $lead = $this->leadService->get($newLeadId)->lead();
        $this->assertEquals($fieldNameValue, $lead->getUserfieldByFieldName($ufOriginalFieldName));

        // update lead userfield value
        $newUfValue = 'test 2';
        $this->assertTrue(
            $this->leadService->update(
                $lead->ID,
                [
                    $ufFieldName => $newUfValue,
                ]
            )->isSuccess()
        );
        $leadItemResult = $this->leadService->get($lead->ID)->lead();
        $this->assertEquals($newUfValue, $leadItemResult->getUserfieldByFieldName($ufOriginalFieldName));
    }

    /**
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->leadService = Factory::getServiceBuilder()->getCRMScope()->lead();
        $this->leadUserfieldService = Factory::getServiceBuilder()->getCRMScope()->leadUserfield();

        $this->leadUserfieldId = $this->leadUserfieldService->add(
            [
                'FIELD_NAME'        => sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time()),
                'EDIT_FORM_LABEL'   => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'тест uf тип string',
                    'en' => 'test uf type string',
                ],
                'USER_TYPE_ID'      => 'string',
                'XML_ID'            => 'b24phpsdk_type_string',
                'SETTINGS'          => [
                    'DEFAULT_VALUE' => 'hello world',
                ],
            ]
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->leadUserfieldService->delete($this->leadUserfieldId);
    }
}