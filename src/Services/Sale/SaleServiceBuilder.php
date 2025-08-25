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

namespace Bitrix24\SDK\Services\Sale;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Sale\Property\Service\Property;

#[ApiServiceBuilderMetadata(new Scope(['sale']))]
class SaleServiceBuilder extends AbstractServiceBuilder
{
	/**
	 * Order properties service (sale.property.*)
	 */
	public function property(): Property
	{
		if (!isset($this->serviceCache[__METHOD__])) {
			$this->serviceCache[__METHOD__] = new Property(
				$this->core,
				$this->log
			);
		}

		return $this->serviceCache[__METHOD__];
	}
}
