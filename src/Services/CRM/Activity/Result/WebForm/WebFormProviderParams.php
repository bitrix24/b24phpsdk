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

namespace Bitrix24\SDK\Services\CRM\Activity\Result\WebForm;

class WebFormProviderParams
{
    public function __construct(private readonly array $fields, private readonly WebFormMetadata $webForm, private readonly array $visitedPages)
    {
    }

    /**
     * @return WebFormFieldItem[]
     */
    public function getFields(): array
    {
        $res = [];
        foreach ($this->fields as $field) {
            $res[] = new WebFormFieldItem($field);
        }

        return $res;
    }

    public function getWebForm(): WebFormMetadata
    {
        return $this->webForm;
    }

    /**
     * @return VisitedPageItem[]
     */
    public function getVisitedPages(): array
    {
        $res = [];
        foreach ($this->visitedPages as $visitedPage) {
            $res[] = new VisitedPageItem($visitedPage);
        }

        return $res;
    }
}
