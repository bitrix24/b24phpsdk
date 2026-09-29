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

namespace Bitrix24\SDK\Services\Landing\Demos\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\DeletedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemoRegisteredBatchResult;
use Bitrix24\SDK\Services\Landing\Demos\Result\DemosItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of registered templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-list.html
     *
     * @param array $select Fields to select
     * @param array $filter Filter conditions
     * @param array $order Sort order, ID is always added as a tie-breaker
     * @param int|null $limit Maximum number of templates to return
     *
     * @return Generator<int, DemosItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.demos.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-get-list.html',
        'Batch list of registered templates'
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

        foreach ($this->batch->getTraversableListByOffset('landing.demos.getList', $params, $limit) as $key => $value) {
            yield $key => new DemosItemResult($value);
        }
    }

    /**
     * Batch registering templates in the site and page creation wizard
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-register.html
     *
     * @param array<int, array<string, mixed>> $templatesData List of template data, each item is the result of landing.site.fullExport
     * @param array $params Common registration parameters (only for on-premise versions): site_template_id, lang, lang_original
     *
     * @return Generator<int, DemoRegisteredBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.demos.register',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-register.html',
        'Batch registering templates'
    )]
    public function register(array $templatesData, array $params = []): Generator
    {
        $items = [];
        foreach ($templatesData as $templateData) {
            // Batch commands are encoded as query strings where empty arrays are dropped,
            // but landing.demos.register requires keys like 'items' to be present
            foreach ($templateData as $key => $value) {
                if ($value === []) {
                    $templateData[$key] = '';
                }
            }

            $item = ['data' => $templateData];
            if ($params !== []) {
                $item['params'] = $params;
            }

            $items[] = $item;
        }

        foreach ($this->batch->addEntityItems('landing.demos.register', $items) as $key => $item) {
            yield $key => new DemoRegisteredBatchResult($item);
        }
    }

    /**
     * Batch removing registered templates
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-unregister.html
     *
     * @param string[] $codes Template codes
     *
     * @return Generator<int, DeletedItemBatchResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.demos.unregister',
        'https://apidocs.bitrix24.com/api-reference/landing/demos/landing-demos-unregister.html',
        'Batch removing registered templates'
    )]
    public function unregister(array $codes): Generator
    {
        foreach ($this->batch->deleteEntityItems('landing.demos.unregister', $codes) as $key => $item) {
            yield $key => new DeletedItemBatchResult($item);
        }
    }
}
