# Stackonet Support Ticket
Easy & Powerful support ticket system for WordPress. Easy to configure and easy to use is our first priority.

# Shortcodes
```
[support_ticket] - All In One support features for front-end. Page having this must be selected as support page in general setting of support ticket.
[create_ticket] - Create ticket form. Can be used as contact form.
```

# REST Endpoint

### Get collection of tickets

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/tickets]`

Params:

| Property          | Type      | Required  | Default   | Description                                                           |
|-------------------|-----------|-----------|-----------|-----------------------------------------------------------------------|
| `page`            | integer   | **no**    | `1`       | Current page of the collection.                                       |
| `per_page`        | integer   | **no**    | `10`      | Maximum number of items to be returned in result set.                 |
| `search`          | string    | **no**    | `null`    | Limit results to those matching a string.                             |
| `city`            | string    | **no**    | `null`    | Limit results to those matching a city.                               |
| `ticket_status`   | integer   | **no**    | `null`    | Limit results to those matching ticket status.                        |
| `ticket_category` | integer   | **no**    | `null`    | Limit results to those matching ticket category.                      |
| `ticket_priority` | integer   | **no**    | `null`    | Limit results to those matching ticket priority.                      |
| `agent`           | integer   | **no**    | `null`    | Agent user id. Limit results to those matching support ticket agents  |


</details>

### Create a ticket

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/tickets]`

Params:

| Property          | Type      | Required  | Default   | Description                   |
|-------------------|-----------|-----------|-----------|-------------------------------|
| `name`            | string    | **yes**   | `null`    | User full name.               |
| `email`           | string    | **yes**   | `null`    | User email address.           |
| `subject`         | string    | **yes**   | `null`    | Ticket subject.               |
| `content`         | string    | **yes**   | `null`    | Ticket content.               |
| `phone_number`    | string    | **no**    | `null`    | User phone number.            |
| `category`        | integer   | **no**    | `null`    | Ticket category id.           |
| `status`          | integer   | **no**    | `null`    | Ticket status id.             |
| `priority`        | integer   | **no**    | `null`    | Ticket priority.              |
| `attachments`     | array     | **no**    | `[]`      | Array of WordPress media ID.  |


</details>

### Get a ticket

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/tickets/{id}]`

Replace `{id}` with actual ticket id.

</details>

### Update a ticket

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/tickets/{id}]`

Replace `{id}` with actual ticket id.

Params: This endpoint accept same argument as create endpoint.

</details>

### Delete a ticket

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/tickets/{id}]`

Replace `{id}` with actual ticket id.

Params:

| Property  | Type     | Required | Default | Description                                        |
|-----------|----------|----------|---------|----------------------------------------------------|
| `action`  | string   | **no**   | `trash` | Value can be `trash` or `restore` or `delete`.     |


</details>

### Update batch tickets

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/tickets/batch]`

Params:

| Property  | Type     | Required | Default | Description                           |
|-----------|----------|----------|---------|---------------------------------------|
| `trash`   | array    | **no**   | `[]`    | Array of ticket id to be trashed.     |
| `restore` | array    | **no**   | `[]`    | Array of ticket id to be restored.    |
| `delete`  | array    | **no**   | `[]`    | Array of ticket id to be deleted.     |

</details>

### Create ticket thread

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/tickets/{id}/thread]`

Replace `{id}` with actual ticket id.

Params:

| Property              | Type     | Required | Default | Description                                                                           |
|-----------------------|----------|----------|---------|---------------------------------------------------------------------------------------|
| `thread_type`         | string   | **no**   | `null`  | Thread type. Value can be `report` or `log` or `reply` or `note` or `sms` or `email`. |
| `thread_content`      | array    | **no**   | `null`  | Thread content.                                                                       |
| `thread_attachments`  | array    | **no**   | `[]`    | Thread attachments. Array of WordPress media attachment id.                           |

</details>

### Update ticket thread

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/tickets/{id}/thread/{thread_id}]`

Replace `{id}` with actual ticket id. and replace `{thread_id}` with actual thread id.

Params:

| Property              | Type     | Required | Default | Description       |
|-----------------------|----------|----------|---------|-------------------|
| `thread_content`      | array    | **no**   | `null`  | Thread content.   |

</details>

### Delete a ticket thread

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/tickets/{id}/thread/{thread_id}]`

Replace `{id}` with actual ticket id. and replace `{thread_id}` with actual thread id.

</details>


### Update a ticket agent(s)

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/tickets/{id}/agent]`

Replace `{id}` with actual ticket id.

Params:

| Property      | Type     | Required | Default | Description                             |
|---------------|----------|----------|---------|-----------------------------------------|
| `agents_ids`  | array    | **no**   | `[]`    | Array of agents ids to assign ticket.   |

</details>


### Get collection of categories

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/categories]`

</details>


### Create a category

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/categories]`

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **yes**  | `null`  | Category name.                                |
| `slug`        | string   | **no**   | `null`  | Category slug. Must be unique for category.   |
| `description` | string   | **no**   | `null`  | Category description.                         |
| `parent`      | integer  | **no**   | `null`  | Parent category ID.                           |

</details>

### Update a category

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/categories/{id}]`

Replace `{id}` with actual category id.

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **no**   | `null`  | Category name.                                |
| `slug`        | string   | **no**   | `null`  | Category slug. Must be unique for category.   |

</details>

### Delete a category

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/categories/{id}]`

Replace `{id}` with actual ticket id.

</details>

### Update categories sorting order

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/categories/batch]`

Params:

| Property      | Type     | Required | Default | Description                                                         |
|---------------|----------|----------|---------|---------------------------------------------------------------------|
| `menu_orders` | array    | **no**   | `[]`    | Array of all categories ID. New order will be set by numeric order. |

</details>


### Get collection of statuses

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/statuses]`

</details>


### Create a status

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/statuses]`

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **yes**  | `null`  | Status name.                                  |
| `slug`        | string   | **no**   | `null`  | Status slug. Must be unique for status.       |
| `description` | string   | **no**   | `null`  | Status description.                           |
| `parent`      | integer  | **no**   | `null`  | Parent status ID.                             |

</details>

### Update a status

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/statuses/{id}]`

Replace `{id}` with actual status id.

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **no**   | `null`  | Status name.                                  |
| `slug`        | string   | **no**   | `null`  | Status slug. Must be unique for status.       |

</details>

### Delete a status

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/statuses/{id}]`

Replace `{id}` with actual status id.

</details>

### Update statuses sorting order

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/statuses/batch]`

Params:

| Property      | Type     | Required | Default | Description                                                        |
|---------------|----------|----------|---------|--------------------------------------------------------------------|
| `menu_orders` | array    | **no**   | `[]`    | Array of all statuses ID. New order will be set by numeric order.  |

</details>



### Get collection of priorities

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/priorities]`

</details>


### Create a priority

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/priorities]`

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **yes**  | `null`  | Priority name.                                |
| `slug`        | string   | **no**   | `null`  | Priority slug. Must be unique for priority.   |
| `description` | string   | **no**   | `null`  | Priority description.                         |
| `parent`      | integer  | **no**   | `null`  | Parent priority ID.                           |

</details>

### Update a priority

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/priorities/{id}]`

Replace `{id}` with actual priority id.

Params:

| Property      | Type     | Required | Default | Description                                   |
|---------------|----------|----------|---------|-----------------------------------------------|
| `name`        | string   | **no**   | `null`  | Priority name.                                |
| `slug`        | string   | **no**   | `null`  | Priority slug. Must be unique for priority.   |

</details>

### Delete a priority

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/priorities/{id}]`

Replace `{id}` with actual priority id.

</details>

### Update priorities sorting order

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/priorities/batch]`

Params:

| Property      | Type     | Required | Default | Description                                                         |
|---------------|----------|----------|---------|---------------------------------------------------------------------|
| `menu_orders` | array    | **no**   | `[]`    | Array of all priorities ID. New order will be set by numeric order. |

</details>



### Get collection of agents

<details>
<summary>View contents</summary>


Endpoint

`[GET /wp-json/stackonet-support-ticket/v1/agents]`

</details>


### Create an agent

<details>
<summary>View contents</summary>


Endpoint

`[POST /wp-json/stackonet-support-ticket/v1/agents]`

Params:

| Property      | Type     | Required | Default | Description           |
|---------------|----------|----------|---------|-----------------------|
| `user_id`     | integer  | **yes**  | `null`  | WordPress user ID.    |
| `role_id`     | string   | **yes**  | `null`  | Agent role ID.        |

</details>

### Update an agent

<details>
<summary>View contents</summary>


Endpoint

`[POST|PUT|PATCH /wp-json/stackonet-support-ticket/v1/agents/{id}]`

Replace `{id}` with actual agent id.

Params:

| Property      | Type     | Required | Default | Description       |
|---------------|----------|----------|---------|-------------------|
| `role_id`     | string   | **no**   | `null`  | Agent role ID.    |

</details>

### Delete an agent

<details>
<summary>View contents</summary>


Endpoint

`[DELETE /wp-json/stackonet-support-ticket/v1/agents/{id}]`

Replace `{id}` with actual agent id.

</details>
