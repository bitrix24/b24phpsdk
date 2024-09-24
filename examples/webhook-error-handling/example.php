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

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';
try {
    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook('INSERT_HERE_YOUR_WEBHOOK_URL');

    // call unknown method and throw  exception
    $b24Service->core->call('Unknown method');
} catch (InvalidArgumentException $exception) {
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class.PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class.PHP_EOL);
    print($throwable->getTraceAsString());
}