<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <titarx@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Result\NumeratorItemResult;
use Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Numerator;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

/**
 * Class NumeratorTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\CRM\Documentgenerator\Numerator\Service
 */
#[CoversMethod(Numerator::class, 'add')]
#[CoversMethod(Numerator::class, 'delete')]
#[CoversMethod(Numerator::class, 'get')]
#[CoversMethod(Numerator::class, 'list')]
#[CoversMethod(Numerator::class, 'update')]
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\CRM\Documentgenerator\Numerator\Service\Numerator::class)]
class NumeratorTest extends TestCase
{
    use CustomBitrix24Assertions;
}
