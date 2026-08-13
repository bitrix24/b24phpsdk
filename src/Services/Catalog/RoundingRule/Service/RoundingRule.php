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

namespace Bitrix24\SDK\Services\Catalog\RoundingRule\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleFieldsResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRulesResult;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['catalog']))]
class RoundingRule extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    /**
     * Adds a new price rounding rule
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-add.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.add',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-add.html',
        'Adds a new price rounding rule'
    )]
    public function add(array $fields): RoundingRuleResult
    {
        return new RoundingRuleResult($this->core->call('catalog.roundingRule.add', ['fields' => $fields]));
    }

    /**
     * Updates a price rounding rule by its identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-update.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.update',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-update.html',
        'Updates a price rounding rule by its identifier'
    )]
    public function update(int $id, array $fields): RoundingRuleResult
    {
        return new RoundingRuleResult($this->core->call('catalog.roundingRule.update', ['id' => $id, 'fields' => $fields]));
    }

    /**
     * Returns price rounding rule information by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.get',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get.html',
        'Returns price rounding rule information by identifier'
    )]
    public function get(int $id): RoundingRuleResult
    {
        return new RoundingRuleResult($this->core->call('catalog.roundingRule.get', ['id' => $id]));
    }

    /**
     * Returns a list of price rounding rules by filter
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-list.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.list',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-list.html',
        'Returns a list of price rounding rules by filter'
    )]
    public function list(array $select = [], array $filter = [], array $order = []): RoundingRulesResult
    {
        return new RoundingRulesResult(
            $this->core->call(
                'catalog.roundingRule.list',
                ['select' => $select, 'filter' => $filter, 'order' => $order]
            )
        );
    }

    /**
     * Deletes a price rounding rule by identifier
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-delete.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.delete',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-delete.html',
        'Deletes a price rounding rule by identifier'
    )]
    public function delete(int $id): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call('catalog.roundingRule.delete', ['id' => $id]));
    }

    /**
     * Returns the fields of a price rounding rule
     *
     * @link https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get-fields.html
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'catalog.roundingRule.getFields',
        'https://apidocs.bitrix24.com/api-reference/catalog/rounding-rule/catalog-rounding-rule-get-fields.html',
        'Returns the fields of a price rounding rule'
    )]
    public function getFields(): RoundingRuleFieldsResult
    {
        return new RoundingRuleFieldsResult($this->core->call('catalog.roundingRule.getFields'));
    }
}
