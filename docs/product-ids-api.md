# User Product IDs API

Returns the IDs of the products in the authenticated user's **cart** and **wishlist** in one call. It's meant for clients that list products (home, search, category, brand pages) and need to mark each card as "in cart" or "in wishlist" without fetching the full cart and wishlist.

## `GET /api/v1/profile/product-ids`

- **Authentication:** required (`Authorization: Bearer <token>`). Guests get `401`.
- **Parameters:** none.

### Example

```bash
curl http://localhost:8080/api/v1/profile/product-ids \
  -H "Authorization: Bearer $TOKEN"
```

### `200 OK`

```json
{
  "success": true,
  "message": "Product IDs retrieved.",
  "data": {
    "cart_product_ids": [2, 9],
    "wishlist_product_ids": [5, 2]
  }
}
```

| Field | Type | Description |
|-------|------|-------------|
| `cart_product_ids` | integer[] | Distinct IDs of products in the cart, ascending |
| `wishlist_product_ids` | integer[] | IDs of products in the wishlist, most recently added first |

Both arrays are empty (`[]`) when there is nothing to report, including when the user has no cart yet.

### Notes

- **Distinct cart IDs:** the cart keeps one line per product and option combination (for example the same T-shirt in two sizes). The same product ID is returned only once.
- **Active products only:** products that are inactive or soft-deleted are left out of both lists, which matches what `GET /cart` and `GET /wishlist` show.
- **A product can be in both lists**, as `2` is in the example above.

### Errors

| Status | When | `message` |
|--------|------|-----------|
| `401` | No or invalid Bearer token | `No authentication token provided.` / `Invalid or expired token.` |
| `500` | Unexpected server error | `An unexpected error occurred.` |

### Client usage

Call it once after login and on app start. Keep the two lists as sets, update them locally after add and remove calls, and refetch when needed.

```js
const { data } = await (await fetch('/api/v1/profile/product-ids', {
  headers: { Authorization: `Bearer ${token}` },
})).json();

const inCart     = new Set(data.cart_product_ids);
const inWishlist = new Set(data.wishlist_product_ids);

inWishlist.has(product.id); // heart filled?
inCart.has(product.id);     // "in cart" badge?
```

### Implementation

| Layer | File |
|-------|------|
| Route | `routes/V1/profile.php` |
| Controller | `app/Controllers/V1/Profile/UserProductIdsController.php` |
| Service | `app/Services/Profile/UserProductIdsService.php` |
| Repositories | `CartRepository::findProductIdsByUserId()`, `WishlistRepository::findProductIdsByUser()` |
