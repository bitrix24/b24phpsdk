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

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\CRM\Common\Result\DiscountType;
use Bitrix24\SDK\Services\CRM\Quote\Result\QuoteProductRowItemResult;
use Bitrix24\SDK\Services\CRM\Quote\Service\Quote;
use Bitrix24\SDK\Services\CRM\Quote\Service\QuoteProductRows;
use Bitrix24\SDK\Tests\Builders\DemoDataGenerator;
use Bitrix24\SDK\Tests\Integration\Factory;
use MoneyPHP\Percentage\Percentage;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Quote\Service\QuoteProductRows::class)]
class QuoteProductRowsTest extends TestCase
{
    private Quote $quoteService;

    private QuoteProductRows $quoteProductRowsService;

    private DecimalMoneyFormatter $decimalMoneyFormatter;

    private TyphoonReflector $typhoonReflector;
    
    protected function setUp(): void
    {
        $this->quoteService = Factory::getServiceBuilder()->getCRMScope()->quote();
        $this->quoteProductRowsService = Factory::getServiceBuilder()->getCRMScope()->quoteProductRows();
        $this->decimalMoneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        $this->typhoonReflector = TyphoonReflector::build();
    }

    public function testAllSystemPropertiesAnnotated(): void
    {
        $quoteId = $this->quoteService->add(['TITLE' => 'test quote'])->getId();
        $this->quoteProductRowsService->set(
            $quoteId,
            [
                [
                    'PRODUCT_NAME' => sprintf('product name %s', time()),
                    'PRICE' => $this->decimalMoneyFormatter->format(new Money(100000, DemoDataGenerator::getCurrency())),
                ],
            ]
        );
        // get response from server with actual keys
        $propListFromApi = array_keys($this->quoteProductRowsService->get($quoteId)->getCoreResponse()->getResponseData()->getResult()['result']['rows'][0]);
        // parse keys from phpdoc annotation
        $collection = $this->typhoonReflector->reflectClass(QuoteProductRowItemResult::class)->properties();
        $propsFromAnnotations = [];
        foreach ($collection as $meta) {
            if ($meta->isAnnotated() && !$meta->isNative()) {
                $propsFromAnnotations[] = $meta->id->name;
            }
        }

        $this->assertEquals($propListFromApi, $propsFromAnnotations,
            sprintf('in phpdocs annotations for class %s cant find fields from actual api response: %s',
                QuoteProductRowItemResult::class,
                implode(', ', array_values(array_diff($propListFromApi, $propsFromAnnotations)))
            ));
        
        $this->quoteService->delete($quoteId);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testSet(): void
    {
        $quoteId = $this->quoteService->add(['TITLE' => sprintf('test quote %s', time())])->getId();
        $quote = $this->quoteService->get($quoteId)->quote();
        $price = new Money(100000, $quote->CURRENCY_ID);
        $discount = new Money(50012, $quote->CURRENCY_ID);
        $this::assertTrue(
            $this->quoteProductRowsService->set(
                $quoteId,
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
        $quoteProductRowItemsResult = $this->quoteProductRowsService->get($quoteId);
        $this->assertCount(1, $quoteProductRowItemsResult->getProductRows());
        $productRow = $quoteProductRowItemsResult->getProductRows()[0];
        $this->assertEquals($price, $productRow->PRICE);
        $this->assertEquals(DiscountType::monetary, $productRow->DISCOUNT_TYPE_ID);
        $this->assertEquals($discount, $productRow->DISCOUNT_SUM);
        $discount = $discount->multiply(100)->divide($this->decimalMoneyFormatter->format($price->add($discount)));
        $calculatedPercentage = new Percentage((string)((int)$discount->getAmount() / 100));
        $this->assertEquals($calculatedPercentage, $productRow->DISCOUNT_RATE);
        
        $this->quoteService->delete($quoteId);
    }

    public function testGet(): void
    {
        $quoteId = $this->quoteService->add(['TITLE' => sprintf('test quote %s', time())])->getId();
        $quote = $this->quoteService->get($quoteId)->quote();
        $price = new Money(100000, $quote->CURRENCY_ID);
        $discount = new Money(0, $quote->CURRENCY_ID);
        $this::assertTrue(
            $this->quoteProductRowsService->set(
                $quoteId,
                [
                    [
                        'PRODUCT_NAME' => sprintf('product name %s', time()),
                        'PRICE' => $this->decimalMoneyFormatter->format($price),
                    ],
                ]
            )->isSuccess()
        );
        $quoteProductRowItemsResult = $this->quoteProductRowsService->get($quoteId);
        $this->assertCount(1, $quoteProductRowItemsResult->getProductRows());
        $productRow = $quoteProductRowItemsResult->getProductRows()[0];
        $this->assertEquals($price, $productRow->PRICE);
        $this->assertEquals(DiscountType::percentage, $productRow->DISCOUNT_TYPE_ID);
        $this->assertEquals($discount, $productRow->DISCOUNT_SUM);
        $this->assertEquals(Percentage::zero(), $productRow->DISCOUNT_RATE);
        
        $this->quoteService->delete($quoteId);
    }

}
