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

namespace App;

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__,2).'/vendor/autoload.php';
require_once 'src/Application.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('event-handler.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);

//try to process incoming events and send processing result to response
Application::processEvents($incomingRequest)->send();

