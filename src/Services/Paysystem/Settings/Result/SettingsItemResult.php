<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Paysystem\Settings\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * Class SettingsItemResult
 *
 * Represents payment system settings data. The structure of the settings is defined
 * when adding the payment system handler in the method sale.paysystem.handler.add
 * under the CODES key of the SETTINGS parameter.
 *
 * The keys of the result object are the parameter codes specified when adding the handler,
 * and the values are the parameter values: either filled in manually by the user when
 * creating the payment system or specified when adding the payment system via
 * sale.paysystem.add or specified when executing the method sale.paysystem.settings.update.
 *
 * Common setting properties (examples from documentation):
 * @property-read string|null $REST_SERVICE_ID_IFRAME Service ID for iframe integration
 * @property-read string|null $REST_SERVICE_KEY_IFRAME Service key for iframe integration
 * @property-read string|null $PS_WORK_MODE_IFRAME Payment system work mode (e.g., "REGULAR")
 *
 * Note: The actual properties depend on the specific payment system handler configuration
 * and may vary for different payment systems.
 */
class SettingsItemResult extends AbstractItem
{
}
