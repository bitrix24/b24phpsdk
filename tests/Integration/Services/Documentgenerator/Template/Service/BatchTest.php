<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Template\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Documentgenerator\Template\Service\Template;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class BatchTest
 *
 * @package Bitrix24\SDK\Tests\Integration\Services\Documentgenerator\Template\Service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Services\Documentgenerator\Template\Service\Batch::class)]
class BatchTest extends TestCase
{
    protected Template $templateService;

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->templateService = Factory::getServiceBuilder()->getDocumentgeneratorScope()->template();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Batch list templates')]
    public function testBatchList(): void
    {
        $cnt = 0;
        foreach ($this->templateService->batch->list() as $item) {
            $cnt++;
        }

        self::assertGreaterThanOrEqual(0, $cnt);
    }
}

