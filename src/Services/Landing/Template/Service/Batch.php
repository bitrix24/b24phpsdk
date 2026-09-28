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

namespace Bitrix24\SDK\Services\Landing\Template\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Template\Result\TemplateItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of view templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order, ID is always added as a tie-breaker
     * @param int|null $limit Maximum number of templates to return
     *
     * @return Generator<int, TemplateItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.template.getlist',
        'https://apidocs.bitrix24.com/api-reference/landing/template/landing-template-get-list.html',
        'Batch list of view templates'
    )]
    public function list(array $select = [], array $filter = [], array $order = [], ?int $limit = null): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'select' => $select,
                'filter' => $filter,
                'order' => $order,
                'limit' => $limit,
            ]
        );

        $params = [
            'select' => $select,
            'filter' => $filter,
            'order' => $order,
        ];

        foreach ($this->batch->getTraversableListByOffset('landing.template.getlist', $params, $limit) as $key => $value) {
            yield $key => new TemplateItemResult($value);
        }
    }
}
