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

use Examples\Workflows\Application;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('event-handler.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);

//try to process incoming requests and send processing result to response
Application::processRequest($incomingRequest)->send();

