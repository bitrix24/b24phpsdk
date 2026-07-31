# Plan: Add imbot.v2.* and im.v2.* chat bot methods (issue #505)

## Context

Implement support for Bitrix24 REST API v2 chat-bot methods:
- `imbot.v2.*` — chat-bot management (scope: `imbot`)
- `im.v2.*` — IM event subscriptions and file operations (scope: `im`)

The implementation creates a new `IMBot` scope under `src/Services/IMBot/` for all `imbot.v2.*` methods,
and extends the existing `IM` scope with `EventV2` and `FileV2` services for `im.v2.*` methods.

Author: © Dmitriy Ignatenko <algonexys@gmail.com>

API documentation: https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/index.html

---

## Files to Create

### IMBot Scope

#### Enums
1. `src/Services/IMBot/Bot/BotType.php` — enum: bot, network, openline, supervisor, personal
2. `src/Services/IMBot/Bot/BotEventMode.php` — enum: fetch, webhook
3. `src/Services/IMBot/Bot/BotBackground.php` — enum: azure, mint, steel, slate, teal, cornflower, sky, peach, frost
4. `src/Services/IMBot/Chat/ChatColor.php` — enum: red, green, mint, lightBlue, darkBlue, purple, aqua, pink, lime, brown, azure, khaki, sand, marengo, gray, graphite

#### Bot Results
5. `src/Services/IMBot/Bot/Result/BotItemResult.php` — @property-read for bot fields
6. `src/Services/IMBot/Bot/Result/BotResult.php` — wraps result.bot + result.users
7. `src/Services/IMBot/Bot/Result/BotsResult.php` — wraps result.bots + result.users

#### Chat Results
8. `src/Services/IMBot/Chat/Result/ChatItemResult.php` — @property-read for chat fields
9. `src/Services/IMBot/Chat/Result/ChatResult.php` — wraps result.chat + result.users
10. `src/Services/IMBot/Chat/Result/ChatUserListResult.php` — wraps result (array of users)

#### ChatMessage Results
11. `src/Services/IMBot/ChatMessage/Result/ChatMessageItemResult.php` — @property-read for message fields
12. `src/Services/IMBot/ChatMessage/Result/ChatMessageResult.php` — wraps result.message + result.user
13. `src/Services/IMBot/ChatMessage/Result/ChatMessageSentResult.php` — wraps result.id + result.uuidMap

#### Command Results
14. `src/Services/IMBot/Command/Result/CommandItemResult.php` — @property-read for command fields
15. `src/Services/IMBot/Command/Result/CommandResult.php` — wraps result.command
16. `src/Services/IMBot/Command/Result/CommandsResult.php` — wraps result.commands (for list)

#### File Results
17. `src/Services/IMBot/File/Result/FileItemResult.php` — @property-read for file fields
18. `src/Services/IMBot/File/Result/FileUploadResult.php` — wraps result.file + messageId + chatId + dialogId

#### Revision Results
19. `src/Services/IMBot/Revision/Result/RevisionItemResult.php` — rest, web, mobile, desktop
20. `src/Services/IMBot/Revision/Result/RevisionResult.php`

#### Services
21. `src/Services/IMBot/Bot/Service/Bot.php` — register, update, get, list, unregister
22. `src/Services/IMBot/Chat/Service/Chat.php` — add, get, update, leave, setOwner
23. `src/Services/IMBot/Chat/Service/ChatUser.php` — add, delete, list
24. `src/Services/IMBot/Chat/Service/ChatManager.php` — add, delete
25. `src/Services/IMBot/Chat/Service/ChatInputAction.php` — notify
26. `src/Services/IMBot/Chat/Service/ChatTextField.php` — enabled
27. `src/Services/IMBot/ChatMessage/Service/ChatMessage.php` — send, update, delete, read, get, getContext
28. `src/Services/IMBot/ChatMessage/Service/ChatMessageReaction.php` — add, delete
29. `src/Services/IMBot/Command/Service/Command.php` — register, update, list, unregister, answer
30. `src/Services/IMBot/Event/Service/Event.php` — get
31. `src/Services/IMBot/File/Service/File.php` — upload, download
32. `src/Services/IMBot/Revision/Service/Revision.php` — get

#### Service Builder
33. `src/Services/IMBot/IMBotServiceBuilder.php`

### IM Scope additions
34. `src/Services/IM/EventV2/Service/EventV2.php` — subscribe, unsubscribe, get
35. `src/Services/IM/FileV2/Result/FileV2ItemResult.php`
36. `src/Services/IM/FileV2/Result/FileV2UploadResult.php`
37. `src/Services/IM/FileV2/Service/FileV2.php` — upload, download

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`
Add `eventV2()` and `fileV2()` methods.

### 2. `src/Services/ServiceBuilder.php`
Add `getIMBotScope()` method.

### 3. `phpunit.xml.dist`
Add test suites for IMBot scope and new IM services.

### 4. `Makefile`
Add make targets for IMBot integration tests.

### 5. `CHANGELOG.md`
Add entries under `## 3.4.0 – UNRELEASED` → `### Added`.

---

## Deptrac compliance

All new code is under `src/Services/IMBot/` and `src/Services/IM/` — these fall into the `Services` layer,
which is already allowed to depend on `Core` and `Application`. No new violations.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-imbot
```

