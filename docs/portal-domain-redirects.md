# Controlling portal domain changes

The SDK follows cross-domain HTTP 302 responses by default for compatibility with portal migrations. Applications can veto a transition using `PortalDomainUrlChangingEvent`, before credentials change and before the access token or request parameters are sent to the new domain.

Register your listener on the dispatcher passed to `CoreBuilder`:

```php
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Exceptions\PortalDomainChangeRejectedException;
use Bitrix24\SDK\Events\PortalDomainUrlChangingEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$dispatcher->addListener(
    PortalDomainUrlChangingEvent::class,
    static function (PortalDomainUrlChangingEvent $event): void {
        // This application requires reinstallation to approve any new portal address.
        $event->deny('Portal domain must be updated through reinstallation');
    }
);

// $credentials is your existing OAuth Credentials instance.
$core = (new CoreBuilder())
    ->withCredentials($credentials)
    ->withEventDispatcher($dispatcher)
    ->build();

try {
    $core->call('app.info');
} catch (PortalDomainChangeRejectedException $exception) {
    $oldUrl = $exception->getOldDomainUrl();
    $newUrl = $exception->getNewDomainUrl();
    $reason = $exception->getDenialReason();
    // Ask the application owner to approve the move or reinstall the application.
    // Retrying without changing the application's decision will fail again.
}
```

The event exposes `getOldDomainUrl()` and `getNewDomainUrl()` as scheme-and-host URLs, so a listener may deny only transitions rejected by its own policy. These accessors differ from the existing changed event's `getOldDomainUrlHost()` and `getNewDomainUrlHost()`, which return hostnames only.

- `deny(string $reason)` is irreversible for that event. The first denial reason is preserved even if another listener calls `deny()` again. An empty reason still denies the transition.
- `isDenied()` starts as `false`; `getDenialReason()` starts as `null`.
- `stopPropagation()` only stops subsequent listeners. It does not deny the transition; call `deny()` to veto it. Register policy listeners with an appropriate priority if other listeners stop propagation.
- A denied transition throws `PortalDomainChangeRejectedException`, a subclass of `PortalUnavailableException`. Credentials retain the address used for that request; no request is sent to the denied destination.
- Every cross-domain hop emits a new preliminary event. If a later hop is denied, credentials retain the last allowed address; earlier transitions are not rolled back.
- Without a denial, the existing `PortalDomainUrlChangedEvent` is still dispatched after the repeated call returns successfully. It is not emitted when that call throws, including a denial of a later hop.
- Without a listener, cross-domain redirects are still followed. Installing this SDK update alone does not restrict destinations.

Keep denial reasons free of secrets. The exception exposes the supplied reason through its getter; its message contains only the old/new addresses and the fact of rejection. Neither the event nor the exception adds request parameters or OAuth tokens to its context.

The existing same-domain redirect guard and webhook restriction remain in effect.
