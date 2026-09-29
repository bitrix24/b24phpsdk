# Value-object inputs in SDK 3.x

Issue [#533](https://github.com/bitrix24/b24phpsdk/issues/533) extends the Stage 1 pattern introduced by #493. Existing string and array inputs remain accepted. Prefer `Url`, `LocalizedString`, `ActivityCode` and `RobotCode` for new code. Primitive input forms are soft-deprecated in documentation only: no runtime deprecation warnings are emitted.

## Using the new inputs

```php
use Bitrix24\SDK\Core\Contracts\LangCodes;
use Bitrix24\SDK\Core\ValueObjects\LocalizedString;
use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Workflows\ValueObjects\ActivityCode;

$activity->update(
    code: new ActivityCode('my_activity'),
    handlerUrl: new Url('https://app.example.com/activity'),
    b24AuthUserId: null,
    localizedName: (new LocalizedString(LangCodes::EN, 'Review'))
        ->with(LangCodes::RU, 'Проверка'),
    localizedDescription: null,
    isUseSubscription: null,
    properties: null,
    isUsePlacement: null,
    returnProperties: null,
    documentType: null,
    limitationFilter: null,
);
```

The legacy `code: 'my_activity'`, `handlerUrl: 'https://app.example.com/activity'`, and `localizedName: ['en' => 'Review', 'ru' => 'Проверка']` produce the same REST payload. Null update values still omit fields; empty localization arrays remain empty arrays.

```php
use Bitrix24\SDK\Services\ServiceBuilderFactory;

$sdk = ServiceBuilderFactory::createServiceBuilderFromWebhook(
    new Url('https://example.bitrix24.com/rest/1/YOUR_TOKEN/')
);
```

Do not put production tokens in source control. Credential getters and serialized payloads still return strings, not value objects.

## Migrated input surfaces

| Surface | Object inputs |
| --- | --- |
| `WebhookUrl`, `Endpoints` | `Url`; client endpoints still accept bare domain strings and add `https://` |
| `Credentials::changeDomainUrl/createFromPlacementRequest` | domain/OAuth `Url` |
| `ServiceBuilderFactory` webhook, OAuth, account and placement factories | explicit URL arguments accept `Url` |
| `LocalAppAuth` constructor | domain/OAuth `Url`; legacy domain strings retain their serialized representation |
| `Robot::add/update/delete` | `RobotCode`; add/update handler and localization objects; pre-existing `placementHandlerUrl` stays object-only |
| `Activity::add/update/delete` | `ActivityCode`; add/update `Url` and `LocalizedString` |
| `Event::bind/unbind`, `EventHandlerMetadata` | handler `Url` |
| `Placement::bind/unbind`, `UserFieldType::add/update` | handler `Url` |
| IM `Placements` typed bind/unbind methods | handler `Url` |
| `Sender::add/update` | handler `Url`, name/description `LocalizedString`; plain names/descriptions are still supported |
| `Engine::register` | completions `Url` |
| IMBot `Bot::register` | optional webhook `Url` |
| `InfoCall::startWithSound` | recording `Url` |
| IM attachment link, image, file, grid and user builders | `Url` for explicit link/preview/avatar arguments |

## Validation and compatibility boundaries

Absolute callback URL strings are now checked through `Url` before reaching transport. Invalid strings that previously reached Bitrix24 now throw `Core\Exceptions\InvalidArgumentException` locally. `Url` retains its existing `FILTER_VALIDATE_URL` semantics; it is not an HTTP-only validator or a network reachability check.

Workflow code strings are validated through their existing code objects. Raw localization maps retain their existing contents, including unknown language keys and empty arrays. Using `LocalizedString` provides the typed `LangCodes` construction API.

`Placement::unbind()` retains both `null` and `''` as the all-handlers sentinel. Event bind/unbind retain the empty handler for offline events. Explicit nonempty callback URLs are validated. IM attachment builders keep their previous non-empty-string behavior for relative and deep links, while accepting `Url` objects for absolute URLs.

These other contracts remain distinct:

- `voximplant.sip.update`'s `serverUrl` is a SIP server address, not necessarily an absolute URL.
- Lists `LIST_ELEMENT_URL` is a path template with substitutions.
- Landing `resolveIdByPublicUrl` accepts relative paths.
- `LANG_ALL` and currency localization structures contain multiple fields per language; they are not `LocalizedString` maps. IM placements retain `PlacementLangMap`.
- Generic REST field/options arrays are not recursively converted. Pass scalar strings/arrays into those bags unless a particular method explicitly documents object support.
- Public application interfaces implemented by consumers are unchanged. Widening their parameters would break existing implementations in PHP.
- Response DTOs, event payloads, diagnostic URLs and documentation metadata are unchanged.

`ValueObjectResolver` owns primitive URL/localization conversion in Core. `WorkflowCodeResolver` owns workflow codes in Services. Core does not depend on workflow services.

## Stage 2: SDK 4.0

Target: the next major SDK release, 4.0. No release date is assigned. Stage 2 must not ship in a 3.x patch or minor release.

Before releasing 4.0:

- Remove primitive alternatives from migrated absolute-URL and workflow-code signatures.
- Remove array alternatives from flat localized-map arguments and provide upgrade examples for existing callers.
- Preserve intentionally different contracts: plain display strings, relative/deep links, SIP server addresses, path templates, structured language maps and generic field bags.
- Design explicit replacements for null/empty sentinels before removing their primitive representation.
- Version exported application interfaces together with their implementations and contract tests.
- Update factories, examples, integrations and named-argument tests; document all breaking signature changes in the major-release migration guide.

Stage 1 does not claim completion of Stage 2. The checklist above schedules that work at the major-version boundary.
