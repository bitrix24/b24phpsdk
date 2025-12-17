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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Localizations\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Services\CRM\Currency\Service\Currency;
use Bitrix24\SDK\Services\CRM\Currency\Localizations\Service\Localizations;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Currency\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Currency\Service\Batch::class)]
class BatchTest extends TestCase
{
    public const CURRENCY_PREFIX = 'XX';

    public const TEST_LETTERS = [
        'A', 'B', 'C', 'D',
    ];

    protected ServiceBuilder $sb;

    protected Currency $currencyService;

    protected Localizations $localizationsService;

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder();
        $this->currencyService = $this->sb->getCRMScope()->currency();
        $this->localizationsService = $this->sb->getCRMScope()->localizations();
        foreach (self::TEST_LETTERS as $letter) {
            $fields = $this->getCurrencyFields($letter);
            $this->currencyService->add($fields);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach (self::TEST_LETTERS as $letter) {
            $this->currencyService->delete(self::CURRENCY_PREFIX.$letter);
        }
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch add localizations')]
    public function testBatchSet(): void
    {
        // add de localization
        $items = [];
        foreach (self::TEST_LETTERS as $letter) {
            $localizations = [
                'en' => $this->getLocalizationFields('en'),
                'de' => $this->getLocalizationFields('de'),
            ];
            $items[] = [
                'id' => self::CURRENCY_PREFIX.$letter,
                'localizations' => $localizations,
            ];
        }

        $cnt = 0;
        foreach ($this->localizationsService->batch->set($items) as $item) {
            $cnt++;
        }

        self::assertEquals(count($items), $cnt);
    }

    /**
     * @throws \Bitrix24\SDK\Core\Exceptions\BaseException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch delete localizations')]
    public function testBatchDelete(): void
    {
        $items = [];
        foreach (self::TEST_LETTERS as $letter) {
            $localizations = [
                'en' => $this->getLocalizationFields('en'),
                'de' => $this->getLocalizationFields('de'),
            ];
            $items[] = [
                'id' => self::CURRENCY_PREFIX.$letter,
                'localizations' => $localizations,
            ];
        }

        $cntAdd = 0;
        foreach ($this->localizationsService->batch->set($items) as $item) {
            $cntAdd++;
        }

        $cnt = 0;
        $items = [];
        foreach (self::TEST_LETTERS as $letter) {
            $items[] = [
                'id' => self::CURRENCY_PREFIX.$letter,
                'lids' => ['de'],
            ];
        }

        foreach ($this->localizationsService->batch->delete($items) as $deleteResult) {
            $cnt++;
        }

        self::assertEquals(count($items), $cntAdd);
    }

    protected function getCurrencyFields(string $letter = 'A'): array {
        return [
            'CURRENCY' => self::CURRENCY_PREFIX.$letter,
            'BASE' => 'N',
            'AMOUNT_CNT' => 1,
            'AMOUNT' => 100.0,
            'SORT' => 100,
            'LANG' => [
                'en' => [
                    'DECIMALS'=> 2,
                    'DEC_POINT' => '.',
                    'FORMAT_STRING' => '#$',
                    'FULL_NAME' => 'Test currency '.$letter,
                    'HIDE_ZERO' => 'N',
                    'THOUSANDS_SEP' => ' ',
                    'THOUSANDS_VARIANT' => 'S',
                ],
            ],
        ];
    }

    protected function getLocalizationFields(string $lang): array {
        return [
            'DECIMALS'=> 2,
            'DEC_POINT' => '.',
            'FORMAT_STRING' => '$#',
            'FULL_NAME' => 'Test currency '.$lang,
            'HIDE_ZERO' => 'N',
            'THOUSANDS_SEP' => ' ',
            'THOUSANDS_VARIANT' => 'S',
        ];
    }
}