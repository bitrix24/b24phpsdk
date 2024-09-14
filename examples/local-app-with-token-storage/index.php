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

require_once 'vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('index.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);
?>
    <pre>
    Application is worked, auth tokens from bitrix24:
    <?= print_r($_REQUEST, true) ?>
</pre>
<?php

// todo try work with app

