# Get Chat Data im.dialog.get

> Scope: [`im`](../scopes/permissions.md)
>
> Who can execute the method: any user — chat participant

The method `im.dialog.get` retrieves information about a chat.

## Response Handling

HTTP Status: **200**

### Returned Data

#|
|| **Name**
`type` | **Description** ||
|| **result**
[`object`](../data-types.md) | Root object containing chat data [(detailed description)](#result-item) ||
|| **time**
[`time`](../data-types.md#time) | Information about the request execution time ||
|#

#### Object result-item {#result-item}

#|
|| **Name**
`type` | **Description** ||
|| **id**
[`integer`](../data-types.md) | Chat identifier ||
|| **parent_chat_id**
[`integer`](../data-types.md) | Parent chat identifier ||
|| **parent_message_id**
[`integer`](../data-types.md) | Parent message identifier ||
|| **name**
[`string`](../data-types.md) | Chat name ||
|| **description**
[`string`](../data-types.md) | Chat description ||
|| **owner**
[`integer`](../data-types.md) | Chat owner identifier ||
|| **extranet**
[`boolean`](../data-types.md) | Indicator of external extranet user participation ||
|| **avatar**
[`string`](../data-types.md) | Link to the chat avatar ||
|| **color**
[`string`](../data-types.md) | Chat color in HEX format ||
|| **type**
[`string`](../data-types.md) | Chat type ||
|| **counter**
[`integer`](../data-types.md) | Unread message counter ||
|| **user_counter**
[`integer`](../data-types.md) | Number of chat participants ||
|| **message_count**
[`integer`](../data-types.md) | Number of messages in the chat ||
|| **unread_id**
[`integer`](../data-types.md) | Identifier of the first unread message ||
|| **restrictions**
[`object`](../data-types.md) | Restrictions on actions in the chat [(detailed description)](#restrictions) ||
|| **last_message_id**
[`integer`](../data-types.md) | Identifier of the last message ||
|| **last_id**
[`integer`](../data-types.md) | Identifier of the last read message ||
|| **marked_id**
[`integer`](../data-types.md) | Identifier of the marked message ||
|| **disk_folder_id**
[`integer`](../data-types.md) | Identifier of the chat folder on Drive ||
|| **entity_type**
[`string`](../data-types.md) | External code of the chat: type ||
|| **entity_id**
[`string`](../data-types.md) | External code of the chat: identifier ||
|| **entity_data_1**
[`string`](../data-types.md) | External data 1 for the chat ||
|| **entity_data_2**
[`string`](../data-types.md) | External data 2 for the chat ||
|| **entity_data_3**
[`string`](../data-types.md) | External data 3 for the chat ||
|| **mute_list**
[`array`](../data-types.md) | List of users with notifications disabled ||
|| **date_create**
[`datetime`](../data-types.md) | Chat creation date in ATOM format ||
|| **message_type**
[`string`](../data-types.md) | Type of chat messages ||
|| **public**
[`string`](../data-types.md) | Indicator of chat public status ||
|| **role**
[`string`](../data-types.md) | Current user's role in the chat ||
|| **entity_link**
[`object`](../data-types.md) | Link to the related object [(detailed description)](#entity-link) ||
|| **text_field_enabled**
[`boolean`](../data-types.md) | Availability of the message input field ||
|| **background_id**
[`integer`](../data-types.md) | Identifier of the chat background. If not specified, the value is `null` ||
|| **permissions**
[`object`](../data-types.md) | Permissions for actions in the chat [(detailed description)](#permissions) ||
|| **is_new**
[`boolean`](../data-types.md) | Indicator of a new dialog ||
|| **readed_list**
[`array`](../data-types.md) | List of users and read statuses [(detailed description)](#readed-list-item) ||
|| **manager_list**
[`array`](../data-types.md) | List of chat manager identifiers ||
|| **last_message_views**
[`object`](../data-types.md) | Information about views of the last message [(detailed description)](#last-message-views) ||
|| **dialog_id**
[`string`](../data-types.md) | Identifier of the dialog passed in the `DIALOG_ID` parameter ||
|#

#### Object restrictions {#restrictions}

#|
|| **Name**
`type` | **Description** ||
|| **avatar**
[`boolean`](../data-types.md) | Availability of avatar change ||
|| **rename**
[`boolean`](../data-types.md) | Availability of name change ||
|| **extend**
[`boolean`](../data-types.md) | Availability of chat extension ||
|| **call**
[`boolean`](../data-types.md) | Availability of calls ||
|| **mute**
[`boolean`](../data-types.md) | Availability of notifications mute ||
|| **leave**
[`boolean`](../data-types.md) | Availability of leaving the chat ||
|| **leave_owner**
[`boolean`](../data-types.md) | Availability of owner leaving the chat ||
|| **send**
[`boolean`](../data-types.md) | Availability of message sending ||
|| **user_list**
[`boolean`](../data-types.md) | Availability of viewing the participant list ||
|| **path**
[`string`](../data-types.md) | Link to the chat ||
|| **path_title**
[`string`](../data-types.md) | Text of the link to the chat ||
|#

#### Object entity_link {#entity-link}

#|
|| **Name**
`type` | **Description** ||
|| **type**
[`string`](../data-types.md) | Type of the related object ||
|| **url**
[`string`](../data-types.md) | Link to the related object ||
|| **id**
[`string`](../data-types.md) | Identifier of the related object ||
|#

#### Object permissions {#permissions}

#|
|| **Name**
`type` | **Description** ||
|| **manage_users_add**
[`string`](../data-types.md) | Permission to add participants ||
|| **manage_users_delete**
[`string`](../data-types.md) | Permission to remove participants ||
|| **manage_ui**
[`string`](../data-types.md) | Permission to manage the chat interface ||
|| **manage_settings**
[`string`](../data-types.md) | Permission to manage chat settings ||
|| **manage_messages**
[`string`](../data-types.md) | Permission to manage messages ||
|| **can_post**
[`string`](../data-types.md) | Permission to send messages ||
|#

#### Object readed_list {#readed-list-item}

#|
|| **Name**
`type` | **Description** ||
|| **user_id**
[`integer`](../data-types.md) | User identifier ||
|| **user_name**
[`string`](../data-types.md) | User name ||
|| **message_id**
[`integer`](../data-types.md) | Identifier of the last read message ||
|| **date**
[`datetime`](../data-types.md) | Read date. If not specified, the value is `null` ||
|#

#### Object last_message_views {#last-message-views}

#|
|| **Name**
`type` | **Description** ||
|| **message_id**
[`integer`](../data-types.md) | Identifier of the message ||
|| **first_viewers**
[`array`](../data-types.md) | List of first viewers ||
|| **count_of_viewers**
[`integer`](../data-types.md) | Number of views ||
|#
