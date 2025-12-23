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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Deal\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Deal\Service\Deal;
use Bitrix24\SDK\Services\CRM\Deal\Service\DealUserfield;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Deal\Service\Deal::class)]
class DealUserfieldUseCaseTest extends TestCase
{
    protected Deal $dealService;

    protected DealUserfield $dealUserfieldService;

    protected int $dealUserfieldId;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testOperationsWithUserfieldFromDealItem(): void
    {
        // get userfield metadata
        $dealUserfieldItemResult = $this->dealUserfieldService->get($this->dealUserfieldId)->userfieldItem();
        $ufOriginalFieldName = $dealUserfieldItemResult->getOriginalFieldName();
        $ufFieldName = $dealUserfieldItemResult->FIELD_NAME;

        // add deal with uf value
        $fieldNameValue = 'test field value';
        $newDealId = $this->dealService->add(
            [
                'TITLE'      => 'test deal',
                $ufFieldName => $fieldNameValue,
            ]
        )->getId();
        $deal = $this->dealService->get($newDealId)->deal();
        $this->assertEquals($fieldNameValue, $deal->getUserfieldByFieldName($ufOriginalFieldName));

        // update deal userfield value
        $newUfValue = 'test 2';
        $this->assertTrue(
            $this->dealService->update(
                $deal->ID,
                [
                    $ufFieldName => $newUfValue,
                ]
            )->isSuccess()
        );
        $dealItemResult = $this->dealService->get($deal->ID)->deal();
        $this->assertEquals($newUfValue, $dealItemResult->getUserfieldByFieldName($ufOriginalFieldName));
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
        $this->dealService = Factory::getServiceBuilder()->getCRMScope()->deal();
        $this->dealUserfieldService = Factory::getServiceBuilder()->getCRMScope()->dealUserfield();

        $this->dealUserfieldId = $this->dealUserfieldService->add(
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
        $this->dealUserfieldService->delete($this->dealUserfieldId);
    }
}