# Wishlist API

Lets an authenticated user save products to a personal wishlist.

- **Base URL:** `/api/v1/wishlist`
- **Authentication:** every endpoint requires `Authorization: Bearer <token>`. Guests get `401`.
- **Format:** JSON request and response bodies. Send `Content-Type: application/json` on requests that have a body.
- Each user can have a given product in their wishlist at most once (enforced by a unique `(user_id, product_id)` key).

| # | Method | Endpoint | Purpose |
|---|--------|----------|---------|
| 1 | `GET` | `/wishlist` | List wishlist items (paginated) |
| 2 | `POST` | `/wishlist` | Add a product |
| 3 | `GET` | `/wishlist/{product_id}` | Check whether a product is in the wishlist |
| 4 | `POST` | `/wishlist/toggle` | Add the product if absent, remove it if present |
| 5 | `DELETE` | `/wishlist/{product_id}` | Remove a product |
| 6 | `DELETE` | `/wishlist` | Clear the whole wishlist |

> A wishlist entry has no editable fields, so there is no `PUT`. The *toggle* endpoint is the "update" operation: it flips a product's membership in a single call, which suits a heart/favorite button.

## Standard envelopes

Success:

```json
{ "success": true, "message": "...", "data": {} }
```

Error:

```json
{ "success": false, "message": "...", "errors": {} }
```

`errors` is only present for `422` validation failures.

## Wishlist item object

| Field | Type | Description |
|-------|------|-------------|
| `wishlist_id` | integer | ID of the wishlist entry |
| `wishlisted_at` | string | When the product was added (`YYYY-MM-DD HH:MM:SS`) |
| `id` | integer | Product ID |
| `name` | string | Product name |
| `slug` | string | Product slug |
| `price` | string (decimal) | Original price |
| `has_offer` | integer (0/1) | Whether the product has an active offer |
| `offer_price` | string (decimal) \| null | Offer price, when `has_offer` is 1 |
| `stock_quantity` | integer | Units in stock |
| `in_stock` | boolean | `true` when `stock_quantity > 0` |
| `main_image` | string \| null | Main image path |
| `average_rating` | string (decimal) | Average review rating |
| `category_name` | string \| null | Category name |
| `brand_name` | string \| null | Brand name |

Products that are inactive or soft-deleted are hidden from the list (and from `total`), but their wishlist rows are kept. They reappear if the product is reactivated, and they can still be removed by ID.

---

## 1. List wishlist

`GET /api/v1/wishlist`

Most recently added first.

**Query parameters**

| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `page` | integer | `1` | Minimum 1 |
| `per_page` | integer | `20` | 1–100 (values above 100 are capped) |

**Example**

```bash
curl "http://localhost:8080/api/v1/wishlist?page=1&per_page=10" \
  -H "Authorization: Bearer $TOKEN"
```

**`200 OK`**

```json
{
  "success": true,
  "message": "Wishlist retrieved.",
  "data": [
    {
      "wishlist_id": 12,
      "wishlisted_at": "2026-11-16 10:42:07",
      "id": 5,
      "name": "Wireless Headphones",
      "slug": "wireless-headphones",
      "price": "120.00",
      "has_offer": 1,
      "offer_price": "99.00",
      "stock_quantity": 14,
      "main_image": "uploads/products/2dd732886c6aa1aaf09a745336a32512.png",
      "average_rating": "4.50",
      "category_name": "Audio",
      "brand_name": "Acme",
      "in_stock": true
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1,
    "has_next": false,
    "has_prev": false
  }
}
```

An empty wishlist returns `200` with `"data": []`.

---

## 2. Add a product

`POST /api/v1/wishlist`

**Body**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | yes | ≥ 1; must be an existing, active product |

**Example**

```bash
curl -X POST http://localhost:8080/api/v1/wishlist \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 5}'
```

**`201 Created`**

```json
{
  "success": true,
  "message": "Product added to wishlist.",
  "data": {
    "wishlist_id": 12,
    "wishlisted_at": "2026-11-16 10:42:07",
    "id": 5,
    "name": "Wireless Headphones",
    "slug": "wireless-headphones",
    "price": "120.00",
    "has_offer": 1,
    "offer_price": "99.00",
    "stock_quantity": 14,
    "main_image": "uploads/products/2dd732886c6aa1aaf09a745336a32512.png",
    "average_rating": "4.50",
    "category_name": "Audio",
    "brand_name": "Acme",
    "in_stock": true
  }
}
```

**Errors**

| Status | When | `message` |
|--------|------|-----------|
| `404` | Product doesn't exist, is inactive, or is deleted | `Product not found.` |
| `409` | Already in the wishlist | `Product is already in your wishlist.` |
| `422` | Missing or non-integer `product_id` | `Validation failed.` |

---

## 3. Check a product

`GET /api/v1/wishlist/{product_id}`

Cheap lookup for rendering the heart icon state on a product screen. It doesn't verify that the product exists: an unknown ID simply returns `in_wishlist: false`.

**Example**

```bash
curl http://localhost:8080/api/v1/wishlist/5 \
  -H "Authorization: Bearer $TOKEN"
```

**`200 OK`**

```json
{
  "success": true,
  "message": "Wishlist status retrieved.",
  "data": { "product_id": 5, "in_wishlist": true }
}
```

---

## 4. Toggle a product

`POST /api/v1/wishlist/toggle`

Removes the product if it's in the wishlist, otherwise adds it. Use the returned `action` or `in_wishlist` to update the UI.

**Body**

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | yes | ≥ 1 |

**Example**

```bash
curl -X POST http://localhost:8080/api/v1/wishlist/toggle \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 5}'
```

**`200 OK`** (added)

```json
{
  "success": true,
  "message": "Product added to wishlist.",
  "data": { "action": "added", "in_wishlist": true, "product_id": 5 }
}
```

**`200 OK`** (removed)

```json
{
  "success": true,
  "message": "Product removed from wishlist.",
  "data": { "action": "removed", "in_wishlist": false, "product_id": 5 }
}
```

**Errors**

| Status | When | `message` |
|--------|------|-----------|
| `404` | Product would be *added* but doesn't exist or is inactive | `Product not found.` |
| `422` | Missing or non-integer `product_id` | `Validation failed.` |

Removing always succeeds for an entry that exists, even if the product has since been deactivated.

---

## 5. Remove a product

`DELETE /api/v1/wishlist/{product_id}`

**Example**

```bash
curl -X DELETE http://localhost:8080/api/v1/wishlist/5 \
  -H "Authorization: Bearer $TOKEN"
```

**`200 OK`**

```json
{
  "success": true,
  "message": "Product removed from wishlist.",
  "data": null
}
```

**Errors**

| Status | When | `message` |
|--------|------|-----------|
| `404` | Product isn't in the user's wishlist | `Product is not in your wishlist.` |

---

## 6. Clear the wishlist

`DELETE /api/v1/wishlist`

Removes every item from the authenticated user's wishlist. It succeeds on an already-empty wishlist.

**Example**

```bash
curl -X DELETE http://localhost:8080/api/v1/wishlist \
  -H "Authorization: Bearer $TOKEN"
```

**`200 OK`**

```json
{
  "success": true,
  "message": "Wishlist cleared.",
  "data": { "removed_count": 3 }
}
```

---

## Common errors

| Status | Cause | Example `message` |
|--------|-------|-------------------|
| `401` | No or invalid Bearer token | `No authentication token provided.` / `Invalid or expired token.` |
| `415` | Request has a body but isn't JSON | `Content-Type must be application/json or multipart/form-data.` |
| `422` | Validation failed | see below |
| `500` | Unexpected server error | `An unexpected error occurred.` |

**`422` example**

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "product_id": ["The product_id field is required."]
  }
}
```

## Implementation notes

| Layer | File |
|-------|------|
| Routes | `routes/V1/wishlist.php` |
| Controller | `app/Controllers/V1/Wishlist/WishlistController.php` |
| Service | `app/Services/Wishlist/WishlistService.php` |
| Repository | `app/Repositories/WishlistRepository.php` (+ `Contracts/WishlistRepositoryInterface.php`) |
| Validator | `app/Validators/Wishlist/WishlistProductValidator.php` |
| Table | `wishlists` in `database/ecommerce_db.sql` |

All queries use prepared statements and are scoped by the authenticated user's ID, so a user can never read or modify another user's wishlist. Adding uses `INSERT IGNORE` against the unique key, so concurrent duplicate requests can't create duplicate rows.
