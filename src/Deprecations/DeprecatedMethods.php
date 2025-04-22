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

namespace Bitrix24\SDK\Deprecations;

readonly class DeprecatedMethods
{
    public function get(): array
    {
        return [
            'crm.catalog.fields',
            'crm.catalog.get',
            'crm.catalog.list',
            'crm.productrow.fields',
            'crm.productrow.list',
            'crm.measure.add',
            'crm.measure.update',
            'crm.measure.get',
            'crm.measure.list',
            'crm.measure.delete',
            'crm.measure.fields',
            'crm.invoice.status.add',
            'crm.invoice.status.delete',
            'crm.invoice.status.get',
            'crm.invoice.status.fields',
            'crm.invoice.status.list',
            'crm.invoice.status.update',
            'crm.deal.category.add',
            'crm.deal.category.delete',
            'crm.deal.category.get',
            'crm.deal.category.fields',
            'crm.deal.category.list',
            'crm.deal.category.update',
            'crm.deal.category.default.get',
            'crm.deal.category.default.set',
            'crm.deal.category.status',
            'crm.deal.category.stage.list',
            'crm.product.add',
            'crm.product.delete',
            'crm.product.fields',
            'crm.product.get',
            'crm.product.list',
            'crm.product.update',
            'crm.product.property.types',
            'crm.product.property.fields',
            'crm.product.property.settings.fields',
            'crm.product.property.enumeration.fields',
            'crm.product.property.add',
            'crm.product.property.get',
            'crm.product.property.list',
            'crm.product.property.update',
            'crm.product.property.delete',
            'crm.productsection.add',
            'crm.productsection.delete',
            'crm.productsection.fields',
            'crm.productsection.get',
            'crm.productsection.list',
            'crm.productsection.update',
            'crm.livefeedmessage.add',
            'crm.invoice.add',
            'crm.invoice.delete',
            'crm.invoice.fields',
            'crm.invoice.get',
            'crm.invoice.list',
            'crm.invoice.recurring.add',
            'crm.invoice.recurring.delete',
            'crm.invoice.recurring.expose',
            'crm.invoice.recurring.fields',
            'crm.invoice.recurring.get',
            'crm.invoice.recurring.list',
            'crm.invoice.recurring.update',
            'crm.invoice.update',
            'crm.invoice.userfield.add',
            'crm.invoice.userfield.delete',
            'crm.invoice.userfield.get',
            'crm.invoice.userfield.list',
            'crm.invoice.userfield.update',
            'crm.paysystem.fields',
            'crm.paysystem.list',
            'crm.persontype.fields',
            'crm.persontype.list',
            'crm.invoice.getexternallink',
        ];
    }
}

