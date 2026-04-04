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

namespace Bitrix24\SDK\Services\CRM\Documentgenerator\Template\Result;

use Bitrix24\SDK\Services\CRM\Common\Result\AbstractCrmItem;
use Carbon\CarbonImmutable;

/**
 * Class TemplateItemResult
 *
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $region
 * @property-read string|null $code
 * @property-read string|null $download
 * @property-read string|null $moduleId
 * @property-read string|null $active
 * @property-read int|null $numeratorId
 * @property-read string|null $withStamps
 * @property-read string|null $isDeleted
 * @property-read array|null $users
 * @property-read int|null $sort
 * @property-read CarbonImmutable|null $createTime
 * @property-read CarbonImmutable|null $updateTime
 * @property-read int|null $createdBy
 * @property-read int|null $updatedBy
 */
class TemplateItemResult extends AbstractCrmItem
{
    /**
     * @param int|string $offset
     *
     * @return CarbonImmutable|int|mixed|null
     */
    #[\Override]
    public function __get($offset)
    {
        switch ($offset) {
            case 'createTime':
            case 'updateTime':
                if (isset($this->data[$offset]) && $this->data[$offset] !== '') {
                    return CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset]);
                }

                return null;
            case 'numeratorId':
            case 'sort':
                if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                    return (int)$this->data[$offset];
                }

                return null;
            default:
                return parent::__get($offset);
        }
    }
}
