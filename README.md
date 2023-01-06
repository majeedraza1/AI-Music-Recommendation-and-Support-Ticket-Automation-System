# Stackonet Support Ticket

Easy & Powerful support ticket system for WordPress. Easy to configure and easy to use is our first priority.

# Shortcodes

```
[support_ticket] - All In One support features for front-end. Page having this must be selected as support page in general setting of support ticket.
[create_ticket] - Create ticket form. Can be used as contact form.
```

# REST Endpoint

API_ENDPOINT: `https://example.com/wp-json`
NAMESPACE: `stackonet-support-ticket`
VERSION: `v1`

Base Endpoint: `{API_ENDPOINT}/{NAMESPACE}/{VERSION}`

### Ticket

<details>
<summary>Get collection of tickets</summary>

Endpoint

`[GET /tickets]`

Params:

| Property          | Type    | Required | Default | Description                                                          |
|-------------------|---------|----------|---------|----------------------------------------------------------------------|
| `page`            | integer | **no**   | `1`     | Current page of the collection.                                      |
| `per_page`        | integer | **no**   | `10`    | Maximum number of items to be returned in result set.                |
| `search`          | string  | **no**   | `null`  | Limit results to those matching a string.                            |
| `city`            | string  | **no**   | `null`  | Limit results to those matching a city.                              |
| `ticket_status`   | integer | **no**   | `null`  | Limit results to those matching ticket status.                       |
| `ticket_category` | integer | **no**   | `null`  | Limit results to those matching ticket category.                     |
| `ticket_priority` | integer | **no**   | `null`  | Limit results to those matching ticket priority.                     |
| `agent`           | integer | **no**   | `null`  | Agent user id. Limit results to those matching support ticket agents |

</details>
<details>
<summary>Create a ticket</summary>


Endpoint

`[POST /tickets]`

Params:

| Property       | Type    | Required | Default | Description                  |
|----------------|---------|----------|---------|------------------------------|
| `name`         | string  | **yes**  | `null`  | User full name.              |
| `email`        | string  | **yes**  | `null`  | User email address.          |
| `subject`      | string  | **yes**  | `null`  | Ticket subject.              |
| `content`      | string  | **yes**  | `null`  | Ticket content.              |
| `phone_number` | string  | **no**   | `null`  | User phone number.           |
| `category`     | integer | **no**   | `null`  | Ticket category id.          |
| `status`       | integer | **no**   | `null`  | Ticket status id.            |
| `priority`     | integer | **no**   | `null`  | Ticket priority.             |
| `attachments`  | array   | **no**   | `[]`    | Array of WordPress media ID. |

</details>
<details>
<summary>Get a ticket</summary>


Endpoint

`[GET /tickets/{id}]`

Replace `{id}` with actual ticket id.

</details>
<details>
<summary>Update a ticket</summary>


Endpoint

`[POST|PUT|PATCH /tickets/{id}]`

Replace `{id}` with actual ticket id.

Params: This endpoint accept same argument as create endpoint.

</details>
<details>
<summary>Delete a ticket</summary>


Endpoint

`[DELETE /tickets/{id}]`

Replace `{id}` with actual ticket id.

Params:

| Property | Type   | Required | Default | Description                                    |
|----------|--------|----------|---------|------------------------------------------------|
| `action` | string | **no**   | `trash` | Value can be `trash` or `restore` or `delete`. |

</details>
<details>
<summary>Run batch action on tickets</summary>


Endpoint

`[POST /tickets/batch]`

Params:

| Property  | Type  | Required | Default | Description                        |
|-----------|-------|----------|---------|------------------------------------|
| `trash`   | array | **no**   | `[]`    | Array of ticket id to be trashed.  |
| `restore` | array | **no**   | `[]`    | Array of ticket id to be restored. |
| `delete`  | array | **no**   | `[]`    | Array of ticket id to be deleted.  |

</details>

<details>
<summary>Update a ticket agent(s)</summary>


Endpoint

`[POST|PUT|PATCH /tickets/{id}/agent]`

Replace `{id}` with actual ticket id.

Params:

| Property     | Type  | Required | Default | Description                           |
|--------------|-------|----------|---------|---------------------------------------|
| `agents_ids` | array | **no**   | `[]`    | Array of agents ids to assign ticket. |

</details>

### Ticket thread

<details>
<summary>Create ticket thread</summary>


Endpoint

`[POST /tickets/{id}/thread]`

Replace `{id}` with actual ticket id.

Params:

| Property             | Type   | Required | Default | Description                                                                           |
|----------------------|--------|----------|---------|---------------------------------------------------------------------------------------|
| `thread_type`        | string | **no**   | `null`  | Thread type. Value can be `report` or `log` or `reply` or `note` or `sms` or `email`. |
| `thread_content`     | array  | **no**   | `null`  | Thread content.                                                                       |
| `thread_attachments` | array  | **no**   | `[]`    | Thread attachments. Array of WordPress media attachment id.                           |

</details>
<details>
<summary>Update ticket thread</summary>


Endpoint

`[POST|PUT|PATCH /tickets/{id}/thread/{thread_id}]`

Replace `{id}` with actual ticket id. and replace `{thread_id}` with actual thread id.

Params:

| Property         | Type  | Required | Default | Description     |
|------------------|-------|----------|---------|-----------------|
| `thread_content` | array | **no**   | `null`  | Thread content. |

</details>
<details>
<summary>Delete a ticket thread</summary>


Endpoint

`[DELETE /tickets/{id}/thread/{thread_id}]`

Replace `{id}` with actual ticket id. and replace `{thread_id}` with actual thread id.

</details>

### Category

<details>
<summary>Get collection of categories</summary>


Endpoint

`[GET /categories]`

</details>
<details>
<summary>Create a category</summary>


Endpoint

`[POST /categories]`

Params:

| Property      | Type    | Required | Default | Description                                 |
|---------------|---------|----------|---------|---------------------------------------------|
| `name`        | string  | **yes**  | `null`  | Category name.                              |
| `slug`        | string  | **no**   | `null`  | Category slug. Must be unique for category. |
| `description` | string  | **no**   | `null`  | Category description.                       |
| `parent`      | integer | **no**   | `null`  | Parent category ID.                         |

</details>
<details>
<summary>Update a category</summary>


Endpoint

`[POST|PUT|PATCH /categories/{id}]`

Replace `{id}` with actual category id.

Params:

| Property | Type   | Required | Default | Description                                 |
|----------|--------|----------|---------|---------------------------------------------|
| `name`   | string | **no**   | `null`  | Category name.                              |
| `slug`   | string | **no**   | `null`  | Category slug. Must be unique for category. |

</details>
<details>
<summary>Delete a category</summary>


Endpoint

`[DELETE /categories/{id}]`

Replace `{id}` with actual ticket id.

</details>
<details>
<summary>Update categories sorting order</summary>


Endpoint

`[POST /categories/batch]`

Params:

| Property      | Type  | Required | Default | Description                                                         |
|---------------|-------|----------|---------|---------------------------------------------------------------------|
| `menu_orders` | array | **no**   | `[]`    | Array of all categories ID. New order will be set by numeric order. |

</details>

### Status

<details>
<summary>Get collection of statuses</summary>


Endpoint

`[GET /statuses]`

</details>
<details>
<summary>Create a status</summary>


Endpoint

`[POST /statuses]`

Params:

| Property      | Type    | Required | Default | Description                             |
|---------------|---------|----------|---------|-----------------------------------------|
| `name`        | string  | **yes**  | `null`  | Status name.                            |
| `slug`        | string  | **no**   | `null`  | Status slug. Must be unique for status. |
| `description` | string  | **no**   | `null`  | Status description.                     |
| `parent`      | integer | **no**   | `null`  | Parent status ID.                       |

</details>
<details>
<summary>Update a status</summary>


Endpoint

`[POST|PUT|PATCH /statuses/{id}]`

Replace `{id}` with actual status id.

Params:

| Property | Type   | Required | Default | Description                             |
|----------|--------|----------|---------|-----------------------------------------|
| `name`   | string | **no**   | `null`  | Status name.                            |
| `slug`   | string | **no**   | `null`  | Status slug. Must be unique for status. |

</details>
<details>
<summary>Delete a status</summary>


Endpoint

`[DELETE /statuses/{id}]`

Replace `{id}` with actual status id.

</details>
<details>
<summary>Update statuses sorting order</summary>


Endpoint

`[POST /statuses/batch]`

Params:

| Property      | Type  | Required | Default | Description                                                       |
|---------------|-------|----------|---------|-------------------------------------------------------------------|
| `menu_orders` | array | **no**   | `[]`    | Array of all statuses ID. New order will be set by numeric order. |

</details>

### Priorities

<details>
<summary>Get collection of priorities</summary>


Endpoint

`[GET /priorities]`

</details>
<details>
<summary>Create a priority</summary>


Endpoint

`[POST /priorities]`

Params:

| Property      | Type    | Required | Default | Description                                 |
|---------------|---------|----------|---------|---------------------------------------------|
| `name`        | string  | **yes**  | `null`  | Priority name.                              |
| `slug`        | string  | **no**   | `null`  | Priority slug. Must be unique for priority. |
| `description` | string  | **no**   | `null`  | Priority description.                       |
| `parent`      | integer | **no**   | `null`  | Parent priority ID.                         |

</details>
<details>
<summary>Update a priority</summary>


Endpoint

`[POST|PUT|PATCH /priorities/{id}]`

Replace `{id}` with actual priority id.

Params:

| Property | Type   | Required | Default | Description                                 |
|----------|--------|----------|---------|---------------------------------------------|
| `name`   | string | **no**   | `null`  | Priority name.                              |
| `slug`   | string | **no**   | `null`  | Priority slug. Must be unique for priority. |

</details>
<details>
<summary>Delete a priority</summary>


Endpoint

`[DELETE /priorities/{id}]`

Replace `{id}` with actual priority id.

</details>
<details>
<summary>Update priorities sorting order</summary>


Endpoint

`[POST /priorities/batch]`

Params:

| Property      | Type  | Required | Default | Description                                                         |
|---------------|-------|----------|---------|---------------------------------------------------------------------|
| `menu_orders` | array | **no**   | `[]`    | Array of all priorities ID. New order will be set by numeric order. |

</details>

### Agents

<details>
<summary>Get collection of agents</summary>


Endpoint

`[GET /agents]`

</details>
<details>
<summary>Create an agent</summary>


Endpoint

`[POST /agents]`

Params:

| Property  | Type    | Required | Default | Description        |
|-----------|---------|----------|---------|--------------------|
| `user_id` | integer | **yes**  | `null`  | WordPress user ID. |
| `role_id` | string  | **yes**  | `null`  | Agent role ID.     |

</details>
<details>
<summary>Update an agent</summary>


Endpoint

`[POST|PUT|PATCH /agents/{id}]`

Replace `{id}` with actual agent id.

Params:

| Property  | Type   | Required | Default | Description    |
|-----------|--------|----------|---------|----------------|
| `role_id` | string | **no**   | `null`  | Agent role ID. |

</details>
<details>
<summary>Delete an agent</summary>


Endpoint

`[DELETE /agents/{id}]`

Replace `{id}` with actual agent id.

</details>

### Roles

<details>
<summary>Get collection of roles</summary>


Endpoint

`[GET /roles]`

</details>
<details>
<summary>Create a role</summary>


Endpoint

`[POST /roles]`

Params:

| Property       | Type   | Required | Default | Description                                |
|----------------|--------|----------|---------|--------------------------------------------|
| `role`         | string | **yes**  | `null`  | Role slug. Role slug cannot change latter. |
| `name`         | string | **yes**  | `null`  | Role display name.                         |
| `capabilities` | object | **yes**  | `null`  | Role capabilities.                         |

</details>
<details>
<summary>Update a role</summary>


Endpoint

`[POST|PUT|PATCH /role]`

Params:

| Property       | Type   | Required | Default | Description        |
|----------------|--------|----------|---------|--------------------|
| `role`         | string | **yes**  | `null`  | Role slug.         |
| `name`         | string | **no**   | `null`  | Role display name. |
| `capabilities` | object | **no**   | `null`  | Role capabilities. |

</details>
<details>
<summary>Delete a role</summary>


Endpoint

`[DELETE /role]`

Params:

| Property | Type   | Required | Default | Description |
|----------|--------|----------|---------|-------------|
| `role`   | string | **yes**  | `null`  | Role slug.  |

</details>

### Attachments

<details>
<summary>Get collection of attachments</summary>


Endpoint

`[GET /attachments]`

</details>
<details>
<summary>Upload an attachment</summary>


Endpoint

`[POST /attachments]`

</details>
<details>
<summary>Delete an attachment</summary>


Endpoint

`[DELETE /attachments/:id]`

Replace `{id}` with actual attachment id.

</details>

### Send sms

<details>
<summary>View contents</summary>


Endpoint

`[POST /tickets/:id/sms]`

Replace `{id}` with actual ticket id.

Params:

| Property       | Type   | Required | Default | Description                                                                             |
|----------------|--------|----------|---------|-----------------------------------------------------------------------------------------|
| `content`      | array  | **yes**  | `[]`    | Sms Content. If sms content is more than 160 characters, then multiple SMS will be sent |
| `sms_for`      | string | **yes**  | ``      | Value can be `customer` or `custom` or `agents`                                         |
| `custom_phone` | string | **no**   | ``      | Custom phone number. Required if `sms_for` is set as `custom`                           |
| `agents_ids`   | array  | **no**   | `[]`    | Array of agents ids. Required if `sms_for` is set as `agents`                           |

</details>
