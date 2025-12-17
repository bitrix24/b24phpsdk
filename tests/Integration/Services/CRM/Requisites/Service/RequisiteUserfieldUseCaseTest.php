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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Requisites\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Requisites\Service\Requisite;
use Bitrix24\SDK\Services\CRM\Requisites\Service\RequisiteUserfield;
use Bitrix24\SDK\Services\CRM\Company\Service\Company;
use Bitrix24\SDK\Tests\Builders\Services\CRM\CompanyBuilder;
use Bitrix24\SDK\Tests\Builders\Services\CRM\RequisiteBuilder;

use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\ServiceBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Requisites\Service\RequisiteUserfield::class)]
class RequisiteUserfieldUseCaseTest extends TestCase
{
    public const COMPANY_OWNER_TYPE_ID = 4;

    protected ServiceBuilder $sb;
    
    protected Requisite $requisiteService;

    protected RequisiteUserfield $requisiteUserfieldService;

    protected int $requisiteUserfieldId;
    
    protected Company $companyService;

    private int $companyId = 0;
    
    protected int $presetId;

    private int $requisiteId = 0;
    
    /**
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->requisiteService = $this->sb->getCRMScope()->requisite();
        $this->requisiteUserfieldService = $this->sb->getCRMScope()->requisiteUserfield();

        $fieldName = sprintf('%s%s', substr((string)random_int(0, PHP_INT_MAX), 0, 3), time());
        $this->requisiteUserfieldId = $this->requisiteUserfieldService->add(
            [
                'FIELD_NAME'        => $fieldName,
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
        
        $this->companyService = $this->sb->getCRMScope()->company();
        
        $entityTypeRequisiteId = current(
            array_filter(
                $this->sb->getCRMScope()->enum()->ownerType()->getItems(),
                fn($item): bool => $item->SYMBOL_CODE === 'REQUISITE'
            )
        )->ID;
        
        $countryId = current(
            array_column(
                array_filter(
                    $this->sb->getCRMScope()->requisitePreset()->countries()->getCountries(),
                    fn($item): bool => $item->CODE === 'US'
                ),
                'ID'
            )
        );
        
        $name = sprintf('test req tpl %s', time());
        $this->presetId = $this->sb->getCRMScope()->requisitePreset()->add(
            $entityTypeRequisiteId,
            $countryId,
            $name,
            [
                'XML_ID' => Uuid::v4()->toRfc4122(),
                'ACTIVE' => 'Y',
            ]
        )->getId();

        [$this->companyId, $this->requisiteId] = $this->addCompanyAndRequisite($this->presetId);
        
        $this->sb->getCRMScope()->requisitePresetField()->add(
            $this->presetId,
            [
                'FIELD_NAME'    => 'UF_CRM_'.$fieldName,
                'FIELD_TITLE'   => 'TEST USERFIELD',
                'IN_SHORT_LIST' => 'N',
                'SORT'          => 590
            ]
        );
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        $this->requisiteService->delete($this->requisiteId);
        $this->companyService->delete($this->companyId);
        $this->requisiteUserfieldService->delete($this->requisiteUserfieldId);
        $this->sb->getCRMScope()->requisitePreset()->delete($this->presetId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testOperationsWithUserfieldFromRequisitesItem(): void
    {
        // get userfield metadata
        $requisiteUserfieldItemResult = $this->requisiteUserfieldService->get($this->requisiteUserfieldId)->userfieldItem();
        $ufOriginalFieldName = $requisiteUserfieldItemResult->getOriginalFieldName();
        $ufFieldName = $requisiteUserfieldItemResult->FIELD_NAME;

        // update requisite userfield value
        $newUfValue = 'test 2';
        $this->assertTrue(
            $this->requisiteService->update(
                $this->requisiteId,
                [
                    $ufFieldName => $newUfValue,
                ]
            )->isSuccess()
        );
        $requisiteItemResult = $this->requisiteService->list([], ['ID' => $this->requisiteId],['*', $ufFieldName])->getRequisites()[0];
        $this->assertEquals($newUfValue, $requisiteItemResult->getUserfieldByFieldName($ufOriginalFieldName));
    }
    
    protected function addCompanyAndRequisite(int $presetId = 0): array {
        $companyId = $this->companyService->add((new CompanyBuilder())->build())->getId();
        $requisiteId = $this->requisiteService->add(
            $companyId,
            self::COMPANY_OWNER_TYPE_ID,
            $presetId,
            'Test requisite '.$presetId,
            (new RequisiteBuilder(self::COMPANY_OWNER_TYPE_ID, $companyId, $presetId))->build()
        )->getId();

        return [$companyId, $requisiteId];
    }
    
}
