# Placement registration in application installers

Use `getPlacementScope()->registrar()` to declare widget handlers in an installer or reinstall workflow. The registrar reads the application's registered handlers and returns a result for each declaration. It needs an OAuth application context with the placement and widget-specific permissions; an incoming webhook cannot register placements.

## Declare handlers

```php
use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\IM\Placements\ImTextareaPlacementOptions;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangItem;
use Bitrix24\SDK\Services\IM\Placements\PlacementLangMap;
use Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes;
use Bitrix24\SDK\Services\Placement\Common\PlacementHandlerMetadata;

$declarations = [
    new PlacementHandlerMetadata(
        placementCode: PlacementLocationCodes::IM_TEXTAREA,
        handlerUrl: new Url('https://example.com/widgets/textarea'),
        lang: PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('My widget'))
            ->toArray(),
        options: (new ImTextareaPlacementOptions('fa-comment'))
            ->width(400)
            ->height(160),
    ),
];

$registrar = $serviceBuilder->getPlacementScope()->registrar();
$report = $registrar->register($declarations);

foreach ($report->getItems() as $item) {
    echo $item->declaration->placementCode . ': ' . $item->status->value;
    if ($item->error !== null) {
        // Inspect the failed operation; do not mark the installer successful.
        $error = $item->error;
    }
}

if (!$report->isSuccess()) {
    // Handle registration failures in the application installation workflow.
}
```

The declaration snapshots a typed option builder when it is constructed. Changing that builder afterwards does not change the declaration. Strings remain accepted for handler URLs, but must be valid HTTP(S) URLs. Codes are normalized to uppercase. Localization uses the same raw `LANG_ALL` array as the generic placement service; existing typed IM language maps can provide it through `toArray()`.

## Identity and repeated installation

A registration's identity is its placement code, exact handler URL and optional Bitrix24 user ID. Omit `b24UserId` for a global registration. A positive user ID describes a user-specific registration where the widget supports it; Bitrix24 currently documents user-specific binding for `PAGE_BACKGROUND_WORKER`.

| Status | Meaning |
|---|---|
| `created` | No handler with that identity existed, and registration succeeded. |
| `skipped` | The same handler, localization and options were already registered. |
| `updated` | An explicit replacement successfully applied a different configuration. |
| `failed` | The requested configuration was not applied. The item contains the error. |

Language locale names and field names are normalized before binding. Duplicate names after normalization and non-string TITLE/DESCRIPTION/GROUP_NAME values are rejected before any REST calls.

Repeating the same declaration skips an unchanged registration. Associative key ordering and omitted empty optional localization strings do not cause a replacement. Option values retain their types and unknown keys when comparing configurations. Map key order is ignored; list order and map/list structure remain significant.

A different URL is a separate registration. The registrar keeps handlers that are absent from the declaration list, including other URLs at the same placement. It does not infer an old URL to remove when an application changes its routing.

An empty declaration list makes no REST requests. Invalid declarations and duplicate identities are rejected before requests. A failure reading the initial remote state is thrown before writes. Per-handler API/transport failures are reported individually so another independent declaration can still be registered; programming errors are not converted into successful reports.

## Explicit replacement

The default conflict policy reports `failed` when an existing identity has a different configuration and leaves that handler unchanged. Opt into replacement when the installer owns and intends to update that identity:

```php
use Bitrix24\SDK\Services\Placement\PlacementConflictPolicy;

$report = $registrar->register(
    $declarations,
    conflictPolicy: PlacementConflictPolicy::replace,
);
```

Replacement removes the exact handler and user identity, then binds the requested configuration. The low-level `Placement::unbind()` accepts an optional third `$b24UserId` argument; omitting it retains its previous behavior. The registrar supplies explicit zero for a global identity and a positive ID for a user-specific identity. It never uses an empty handler URL or falls back to removing all handlers for a placement.

Replacement is not atomic: the handler is absent between unbind and bind. Parallel installers may observe conflicting state. The registrar does not add retries or locking, and reports an unexpected deletion count instead of claiming an update succeeded.

If the new bind fails, the registrar attempts to restore the saved old configuration. The result remains `failed` because the requested update was not applied:

- `error` describes the original replacement failure.
- `previousConfigurationRestored` is `true` or `false` when restoration was attempted, otherwise `null`.
- `rollbackError` describes a restoration failure separately.

Restoration is best effort. A successful restoration preserves the old configuration; it does not make the requested update successful. If the remote record lacks information needed to restore its options/localization, replacement is refused before removal. Legacy top-level titles/descriptions without reproducible `LANG_ALL` are one such case.

## Application tests

The focused `make test-integration-placement-registrar` suite exercises create, repeat, conflict, replacement, unrelated-handler preservation and global/user-specific removal with the test OAuth application. Configure and authorize `tests/ApplicationBridge` before running it. The suite uses unique handler URLs and removes only its own registrations afterwards.

The `PAGE_BACKGROUND_WORKER` case needs that placement in the test application's available placement list. An incomplete isolation case is not evidence that global/user-specific replacement has been verified. Confirm this behavior and real option/localization normalization before relying on replacement in production.

Official API contracts: [placement.bind](https://apidocs.bitrix24.com/api-reference/widgets/placement-bind.html), [placement.get](https://apidocs.bitrix24.com/api-reference/widgets/placement-get.html), [placement.unbind](https://apidocs.bitrix24.com/api-reference/widgets/placement-unbind.html).
