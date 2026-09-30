# Legacy imbot audit — issue #270

Research date: 2026-09-30. Target SDK branch: v3-dev. This is a legacy REST inventory, not REST API v3 coverage.

Baseline **0/37 legacy imbot methods** is from the Make CLI coverage audit (after #637/#638 correction). Existing `imbot.v2.*` wrappers are not credited to legacy `imbot.*`. This document does not calculate a replacement coverage percentage.

**28 deprecated/documented legacy methods; nine unresolved undocumented methods.** No new legacy wrappers recommended from current public contracts. Issue #270 remains open for the nine unresolved contracts. Method presence in a portal inventory proves availability there, not a supported public contract. No runtime messages, support questions, bots, or mutations were sent.

Sources: official bitrix-tools/b24-rest-docs refreshed main archive at commit 29bc9d23ec5d887ca697205152d2c053999a6bbd66e; official Bitrix24 MCP exact method details. Source links below are direct primary evidence. Documentation method casing differs from the lowercase portal inventory.

For bot/chat/message/command methods, each linked current source explicitly says DEPRECATED and names the replacement. Several MCP responses are stale and omit that warning; the current source controls the disposition. For the three app methods, MCP labels DEPRECATED and current docs place them under chats/outdated. The official [chat app overview](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chats/outdated/chat-apps.md) says old embedding currently works but new chat applications should use [REST placements](https://apidocs.bitrix24.com/api-reference/widgets/im/index.html); do not invent a v2 chat-app replacement.

SDK paths below come from exact ApiEndpointMetadata lookup in existing source. They establish wrapper presence only, not successful integration tests or legacy coverage. No existing legacy wrappers in this scope require a new Deprecated attribute; do not mark modern v2 services deprecated.

| Legacy endpoint (inventory casing) | Disposition and primary evidence | Official replacement | Existing SDK wrapper path |
|---|---|---|---|
| `imbot.app.register` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chats/outdated/create-app/imbot-app-register.md)) | REST placements for new chat (not 1:1) | No 1:1 v2 wrapper applicable |
| `imbot.app.unregister` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chats/outdated/create-app/imbot-app-unregister.md)) | REST placements for new chat (not 1:1) | No 1:1 v2 wrapper applicable |
| `imbot.app.update` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chats/outdated/create-app/imbot-app-update.md)) | REST placements for new chat (not 1:1) | No 1:1 v2 wrapper applicable |
| `imbot.bot.list` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/bots/imbot-bot-list.md)) | `imbot.v2.Bot.list` | `src/Services/IMBot/Bot/Service/Bot.php` |
| `imbot.chat.add` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-add.md)) | `imbot.v2.Chat.add` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.get` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-get.md)) | `imbot.v2.Chat.get` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.leave` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-leave.md)) | `imbot.v2.Chat.leave` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.sendtyping` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-send-typing.md)) | `imbot.v2.Chat.InputAction.notify` | `src/Services/IMBot/Chat/Service/ChatInputAction.php` |
| `imbot.chat.setmanager` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-set-manager.md)) | `imbot.v2.Chat.Manager.add`<br>`imbot.v2.Chat.Manager.delete` | `src/Services/IMBot/Chat/Service/ChatManager.php` |
| `imbot.chat.setowner` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-set-owner.md)) | `imbot.v2.Chat.setOwner` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.updateavatar` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-update-avatar.md)) | `imbot.v2.Chat.update` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.updatecolor` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-update-color.md)) | `imbot.v2.Chat.update` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.updatetextfieldenabled` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.chat.updatetitle` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-update-title.md)) | `imbot.v2.Chat.update` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.chat.user.add` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-user-add.md)) | `imbot.v2.Chat.User.add` | `src/Services/IMBot/Chat/Service/ChatUser.php` |
| `imbot.chat.user.delete` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-user-delete.md)) | `imbot.v2.Chat.User.delete` | `src/Services/IMBot/Chat/Service/ChatUser.php` |
| `imbot.chat.user.list` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-chat-user-list.md)) | `imbot.v2.Chat.User.list` | `src/Services/IMBot/Chat/Service/ChatUser.php` |
| `imbot.command.answer` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/commands/imbot-command-answer.md)) | `imbot.v2.Command.answer` | `src/Services/IMBot/Command/Service/Command.php` |
| `imbot.command.register` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/commands/imbot-command-register.md)) | `imbot.v2.Command.register` | `src/Services/IMBot/Command/Service/Command.php` |
| `imbot.command.unregister` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/commands/imbot-command-unregister.md)) | `imbot.v2.Command.unregister` | `src/Services/IMBot/Command/Service/Command.php` |
| `imbot.command.update` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/commands/imbot-command-update.md)) | `imbot.v2.Command.update` | `src/Services/IMBot/Command/Service/Command.php` |
| `imbot.dialog.get` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/chats/imbot-dialog-get.md)) | `imbot.v2.Chat.get` | `src/Services/IMBot/Chat/Service/Chat.php` |
| `imbot.dialog.vote` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.message.add` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/messages/imbot-message-add.md)) | `imbot.v2.Chat.Message.send` | `src/Services/IMBot/ChatMessage/Service/ChatMessage.php` |
| `imbot.message.delete` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/messages/imbot-message-delete.md)) | `imbot.v2.Chat.Message.delete` | `src/Services/IMBot/ChatMessage/Service/ChatMessage.php` |
| `imbot.message.like` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/messages/imbot-message-like.md)) | `imbot.v2.Chat.Message.Reaction.add`<br>`imbot.v2.Chat.Message.Reaction.delete` | `src/Services/IMBot/ChatMessage/Service/ChatMessageReaction.php` |
| `imbot.message.update` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/messages/imbot-message-update.md)) | `imbot.v2.Chat.Message.update` | `src/Services/IMBot/ChatMessage/Service/ChatMessage.php` |
| `imbot.network.question.add` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.network.question.list` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.network.question.search` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.register` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/bots/imbot-register.md)) | `imbot.v2.Bot.register` | `src/Services/IMBot/Bot/Service/Bot.php` |
| `imbot.support24.question.add` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.support24.question.config.get` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.support24.question.list` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.support24.question.search` | Undocumented: no official contract located | Unconfirmed | Not applicable; do not infer equivalence |
| `imbot.unregister` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/bots/imbot-unregister.md)) | `imbot.v2.Bot.unregister` | `src/Services/IMBot/Bot/Service/Bot.php` |
| `imbot.update` | Deprecated ([official source](https://github.com/bitrix-tools/b24-rest-docs/blob/29bc9d2/api-reference/chat-bots/outdated/bots/imbot-update.md)) | `imbot.v2.Bot.update` | `src/Services/IMBot/Bot/Service/Bot.php` |

## Unresolved endpoint handling

The nine undocumented endpoints were absent from the refreshed official api-reference tree and exact MCP lookups (including canonical-case retry of updateTextFieldEnabled). Official-domain searches yielded no public contracts. This is not proof that the endpoints are private, removed, or deprecated. Their request parameters, response envelopes, authorization requirements, and supported lifecycle remain unverified. Await official specification or vendor clarification before adding wrappers. `imbot.v2.Chat.TextField.enabled` exists in the SDK but no official migration mapping was located for the legacy `imbot.chat.updatetextfieldenabled`; do not claim a confirmed replacement based on similar names.

No runtime calls should be made to network/support24 question.add or dialog.vote merely to probe capability: they can send real messages or feedback. Bot getters require correct application/bot context; legacy docs sometimes support webhooks with CLIENT_ID, so blanket OAuth-only classification is not justified.
