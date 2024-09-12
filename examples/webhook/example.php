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

use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';

// init bitrix24-php-sdk service from webhook
$b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook('INSERT_HERE_YOUR_WEBHOOK_URL');

// call some method
var_dump($b24Service->getMainScope()->main()->getApplicationInfo()->applicationInfo());
// call core
var_dump($b24Service->core->call('user.current'));