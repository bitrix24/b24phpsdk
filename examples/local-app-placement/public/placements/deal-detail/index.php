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

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('deal_placent_detail.init', ['request' => $incomingRequest->request->all(), 'query' => $incomingRequest->query->all()]);
?>
<pre>
    Application in DEAL_DETAIL PLACEMENT is worked, auth tokens from bitrix24:
    <?= print_r($_REQUEST, true) ?>
</pre>
<?php
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container text-center">
    <div class="row">
        <div class="col">
            <h1>Work with bx24.js example</h1>
            <button type="button" class="btn btn-primary" onClick="BX24.selectUser(function(args) {
                console.log('selectUser.start');
                console.log(args);
                console.log('selectUser.finish');
            })">Select User
            </button>
            <button type="button" class="btn btn-primary" onClick="void BX24.selectUsers(function(args) {
                console.log('selectUsers.start');
                console.log(args);
                console.log('selectUsers.finish');
            })">Select Users
            </button>
            <button type="button" class="btn btn-primary" onClick="void BX24.selectAccess(function(args) {
                console.log('selectAccess.start');
                console.log(args);
                console.log('selectAccess.finish');
            })">Select Access
            </button>
            <button type="button" class="btn btn-primary" onClick="void BX24.selectCRM(
                {
	                entityType: ['lead', 'contact', 'company', 'deal', 'quote'],
	                multiple: false
                }, function(args) {
                console.log('selectCRM.start');
                console.log(args);
                console.log('selectCRM.finish');
            })">Select CRM
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <button type="button" class="btn btn-primary" onClick="BX24.openPath('/crm/deal/details/4/', function(args) {
                console.log('openPath.start');
                console.log(args);
                console.log('openPath.finish');
            })">Open Path
            </button>

        </div>
    </div>
</div>
<script src="//api.bitrix24.com/api/v1/"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        BX24.init(function () {
            console.log('bx24.js initialized', BX24.isAdmin());
        });
    });
</script>
</body>
</html>





