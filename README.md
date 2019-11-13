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
