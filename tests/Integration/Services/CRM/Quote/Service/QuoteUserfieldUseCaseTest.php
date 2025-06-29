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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Quote\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Quote\Service\Quote;
use Bitrix24\SDK\Services\CRM\Quote\Service\QuoteUserfield;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Quote\Service\QuoteUserfield::class)]
class QuoteUserfieldUseCaseTest extends TestCase
{
    protected Quote $quoteService;

    protected QuoteUserfield $quoteUserfieldService;

    protected int $quoteUserfieldId;
    
    /**
     * @throws \Bitrix24\SDK\Services\CRM\Userfield\Exceptions\UserfieldNameIsTooLongException
     * @throws \Bitrix24\SDK\Core\Exceptions\TransportException
     * @throws \Bitrix24\SDK\Core\Exceptions\InvalidArgumentException
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    protected function setUp(): void
    {
        $this->quoteService = Fabric::getServiceBuilder()->getCRMScope()->quote();
        $this->quoteUserfieldService = Fabric::getServiceBuilder()->getCRMScope()->quoteUserfield();

        $this->quoteUserfieldId = $this->quoteUserfieldService->add(
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

    protected function tearDown(): void
    {
        $this->quoteUserfieldService->delete($this->quoteUserfieldId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testOperationsWithUserfieldFromQuoteItem(): void
    {
        // get userfield metadata
        $quoteUserfieldItemResult = $this->quoteUserfieldService->get($this->quoteUserfieldId)->userfieldItem();
        $ufOriginalFieldName = $quoteUserfieldItemResult->getOriginalFieldName();
        $ufFieldName = $quoteUserfieldItemResult->FIELD_NAME;

        // add quote with uf value
        $fieldNameValue = 'test field value';
        $newQuoteId = $this->quoteService->add(
            [
                'TITLE'      => 'test quote',
                $ufFieldName => $fieldNameValue,
            ]
        )->getId();
        $quote = $this->quoteService->get($newQuoteId)->quote();
        $this->assertEquals($fieldNameValue, $quote->getUserfieldByFieldName($ufOriginalFieldName));

        // update quote userfield value
        $newUfValue = 'test 2';
        $this->assertTrue(
            $this->quoteService->update(
                $quote->ID,
                [
                    $ufFieldName => $newUfValue,
                ]
            )->isSuccess()
        );
        $quoteItemResult = $this->quoteService->get($quote->ID)->quote();
        $this->assertEquals($newUfValue, $quoteItemResult->getUserfieldByFieldName($ufOriginalFieldName));
    }
    
}
