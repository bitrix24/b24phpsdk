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

namespace Bitrix24\SDK\Services;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractService
 *
 * @package Bitrix24\SDK\Services
 */
abstract class AbstractService
{
    /**
     * @property-read CoreInterface $core
     */
    public CoreInterface $core;
    protected LoggerInterface $log;
    protected DecimalMoneyFormatter $decimalMoneyFormatter;

    /**
     * AbstractService constructor.
     *
     * @param CoreInterface $core
     * @param LoggerInterface $log
     */
    public function __construct(CoreInterface $core, LoggerInterface $log)
    {
        $this->core = $core;
        $this->log = $log;
        $this->decimalMoneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function guardPositiveId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(sprintf('id must be positive, current value: %s', $id));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function guardNonEmptyString(string $value, ?string $message = null): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException($message ?? 'value must be non empty');
        }
    }
}