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

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\Result\DiscountType;
use Bitrix24\SDK\Services\CRM\Lead\Result\LeadProductRowItemResult;
use Bitrix24\SDK\Services\CRM\Lead\Service\Lead;
use Bitrix24\SDK\Services\CRM\Lead\Service\LeadProductRows;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Fabric;
use MoneyPHP\Percentage\Percentage;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Lead\Service\LeadProductRows::class)]
class LeadProductRowsTest extends TestCase
{
    private Lead $leadService;

    private LeadProductRows $leadProductRowsService;

    private DecimalMoneyFormatter $decimalMoneyFormatter;

    private TyphoonReflector $typhoonReflector;

    public function testAllSystemPropertiesAnnotated(): void
    {
        $leadId = $this->leadService->add(['TITLE' => 'test lead'])->getId();
        $this->leadProductRowsService->set(
            $leadId,
            [
                [
                    'PRODUCT_NAME' => sprintf('product name %s', time()),
                    'PRICE' => $this->decimalMoneyFormatter->format(new Money(100000, DemoDataGenerator::getCurrency())),
                ],
            ]
        );
        // get response from server with actual keys
        $propListFromApi = array_keys($this->leadProductRowsService->get($leadId)->getCoreResponse()->getResponseData()->getResult()['result']['rows'][0]);
        // parse keys from phpdoc annotation
        $collection = $this->typhoonReflector->reflectClass(LeadProductRowItemResult::class)->properties();
        $propsFromAnnotations = [];
        foreach ($collection as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[] = $meta->id->name;
            }
        }

        $this->assertEquals($propListFromApi, $propsFromAnnotations,
            sprintf('in phpdocs annotations for class %s cant find fields from actual api response: %s',
                LeadProductRowItemResult::class,
                implode(', ', array_values(array_diff($propListFromApi, $propsFromAnnotations)))
            ));
        
        $this->leadService->delete($leadId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSet(): void
    {
        $leadId = $this->leadService->add(['TITLE' => sprintf('test lead %s', time())])->getId();
        $lead = $this->leadService->get($leadId)->lead();
        $price = new Money(100000, $lead->CURRENCY_ID);
        $discount = new Money(50012, $lead->CURRENCY_ID);
        $this::assertTrue(
            $this->leadProductRowsService->set(
                $leadId,
                [
                    [
                        'PRODUCT_NAME' => sprintf('product name %s', time()),
                        'PRICE' => $this->decimalMoneyFormatter->format($price),
                        'DISCOUNT_TYPE_ID' => 1,
                        'DISCOUNT_SUM' => $this->decimalMoneyFormatter->format($discount)
                    ],
                ]
            )->isSuccess()
        );
        $leadProductRowItemsResult = $this->leadProductRowsService->get($leadId);
        $this->assertCount(1, $leadProductRowItemsResult->getProductRows());
        $productRow = $leadProductRowItemsResult->getProductRows()[0];
        $this->assertEquals($price, $productRow->PRICE);
        $this->assertEquals(DiscountType::monetary, $productRow->DISCOUNT_TYPE_ID);
        $this->assertEquals($discount, $productRow->DISCOUNT_SUM);
        $discount = $discount->multiply(100)->divide($this->decimalMoneyFormatter->format($price->add($discount)));
        $calculatedPercentage = new Percentage((string)((int)$discount->getAmount() / 100));
        $this->assertEquals($calculatedPercentage, $productRow->DISCOUNT_RATE);
        
        $this->leadService->delete($leadId);
    }

    public function testGet(): void
    {
        $leadId = $this->leadService->add(['TITLE' => sprintf('test lead %s', time())])->getId();
        $lead = $this->leadService->get($leadId)->lead();
        $price = new Money(100000, $lead->CURRENCY_ID);
        $discount = new Money(0, $lead->CURRENCY_ID);
        $this::assertTrue(
            $this->leadProductRowsService->set(
                $leadId,
                [
                    [
                        'PRODUCT_NAME' => sprintf('product name %s', time()),
                        'PRICE' => $this->decimalMoneyFormatter->format($price),
                    ],
                ]
            )->isSuccess()
        );
        $leadProductRowItemsResult = $this->leadProductRowsService->get($leadId);
        $this->assertCount(1, $leadProductRowItemsResult->getProductRows());
        $productRow = $leadProductRowItemsResult->getProductRows()[0];
        $this->assertEquals($price, $productRow->PRICE);
        $this->assertEquals(DiscountType::percentage, $productRow->DISCOUNT_TYPE_ID);
        $this->assertEquals($discount, $productRow->DISCOUNT_SUM);
        $this->assertEquals(Percentage::zero(), $productRow->DISCOUNT_RATE);
        
        $this->leadService->delete($leadId);
    }

    protected function setUp(): void
    {
        $this->leadService = Fabric::getServiceBuilder()->getCRMScope()->lead();
        $this->leadProductRowsService = Fabric::getServiceBuilder()->getCRMScope()->leadProductRows();
        $this->decimalMoneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        $this->typhoonReflector = TyphoonReflector::build();
    }
}