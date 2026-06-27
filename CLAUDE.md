# Claude.md

## Project Overview

This project is a scalable E-Commerce Backend API built using pure PHP and MySQL.

The backend will expose REST APIs consumed by mobile apps and/or web frontend clients.

The system must support:

* Guest browsing
* Authentication using Bearer Tokens
* Products, Categories, Brands
* Cart and Checkout
* Orders management
* User Profiles and Addresses
* Product Reviews and Ratings
* Offers and Deals
* Home page dynamic sections

The project architecture must prioritize:

* Clean Architecture
* Scalability
* Security
* Maintainability
* Reusability
* Performance
* Clear folder structure
* Pure PHP without frameworks

---

# Tech Stack

## Backend

* Pure PHP 8+
* MySQL 8+
* Apache or Nginx
* RESTful APIs
* JSON Request/Response format

## Authentication

* Bearer Token Authentication
* Secure password hashing using:

  * `password_hash`
  * `password_verify`

## Database

* MySQL relational database
* UUIDs or BIGINT IDs (decision pending)
* Indexed frequently queried columns
* Soft delete support where needed

---

# Coding Standards

## General Rules

* Write clean and readable code.
* Use SOLID principles whenever applicable.
* Avoid duplicated code.
* Separate:

  * Controllers
  * Services
  * Repositories
  * Models
  * Helpers
  * Middleware
* Never place SQL directly inside controllers.
* Use dependency injection whenever possible.
* All APIs must return JSON.
* Use proper HTTP status codes.
* Validate all inputs.
* Sanitize all user inputs.
* Never trust frontend validation alone.

---

# Folder Structure

Recommended structure:

/project-root
│
├── /app
│   ├── /Controllers
│   ├── /Services
│   ├── /Repositories
│   ├── /Models
│   ├── /Middleware
│   ├── /Helpers
│   ├── /Validators
│   ├── /Traits
│   └── /Exceptions
│
├── /config
├── /routes
├── /storage
│   ├── /logs
│   └── /uploads
│
├── /database
│   ├── /migrations
│   └── /seeders
│
├── /public
├── /tests
└── /vendor

---

# API Standards

## Base Response Format

Success response:

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

Error response:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

---

# Authentication Module

## Features

### Login

Using:

* Email
* Password

Returns:

* User object
* Bearer token

### Register

Using:

* Full name
* Email
* Password
* Gender
* Birthdate

Returns:

* User object
* Bearer token

### Logout

* Token invalidation

### Guest Access

Guest users can:

* Browse products
* Browse categories
* Browse brands
* View product details
* Search products

Guest users cannot:

* Add reviews
* Add to cart (unless guest cart is implemented later)
* Checkout
* View orders
* Manage profile

---

# User Module

## User Fields

* id
* fullname
* email
* password
* gender
* birthdate
* profile_image
* created_at
* updated_at

---

# Home Module

## Home Sections

### Advertisements Slider

Contains:

* image
* title
* redirect_type
* redirect_value

### Most Popular Products

Product card contains:

* Main image
* Name
* Price
* In stock status
* Has offer
* Offer price

### Most Popular Categories

Category contains:

* Icon
* Name

### Most Popular Brands

Brand contains:

* Icon
* Name

### Recently Requested Products

---

# Product Module

## Product Fields

* id
* brand_id
* category_id
* name
* slug
* description
* price
* offer_price
* has_offer
* stock_quantity
* main_image
* images
* is_active
* created_at
* updated_at

## Product Features

### Product Details

Contains:

* Main image
* Brand
* Category
* Offer status
* Original price
* Offer price
* Description
* Related products
* Stock availability
* Options
* Reviews
* Ratings
* Add to cart support

### Product Options

Examples:

* Sizes
* Colors

Must support:

* Multiple option groups
* Multiple values per option

Suggested tables:

* product_options
* product_option_values

### Product Reviews

Users can:

* Add review
* Add rating

Review fields:

* user_id
* product_id
* rating
* comment
* created_at

---

# Categories Module

## Category Fields

* id
* name
* icon
* image
* is_active

---

# Brands Module

## Brand Fields

* id
* name
* icon
* image
* is_active

---

# Products Listing Module

Products page can be reached from:

* Search
* Category
* Brand
* View all sections

Must support:

* Pagination
* Sorting
* Filtering
* Search
* Infinite scrolling readiness

---

# Deals Module

Contains products where:

* has_offer = true

---

# Cart Module

## Cart Features

### Cart Items

Each item contains:

* Product
* Quantity
* Selected options

### Cart Summary

Contains:

* Subtotal
* Delivery fees
* Total price

---

# Checkout Module

## Checkout Sections

### Address

Selected address with ability to change.

### Delivery Type

Each delivery type has:

* Name
* Fee
* Estimated delivery time

### Payment Type

Examples:

* Cash
* Card
* Wallet

### Requested Products

### Prices Summary

Contains:

* Subtotal
* Delivery fees
* Total price

### Place Order

Creates order.

---

# Orders Module

## Order Statuses

* Pending
* Preparing
* Delivering
* Finished
* Canceled
* Rejected

## Order Features

Users can:

* View orders
* View order details
* Cancel orders (based on status rules)

Order details contain:

* Address snapshot
* Product snapshot
* Payment type
* Delivery type
* Prices
* Quantities

Important:
Order data must remain immutable after creation.

---

# Address Module

## Address Fields

* id
* user_id
* latitude
* longitude
* city_name
* street_name
* building_number
* additional_info
* is_default

Users can:

* Create address
* Update address
* Delete address
* Select default address

---

# Security Rules

## Required Security Practices

* Use prepared statements everywhere.
* Never expose passwords.
* Validate uploaded files.
* Limit upload types.
* Rate limit authentication APIs.
* Escape outputs when needed.
* Prevent SQL Injection.
* Prevent XSS.
* Prevent CSRF where applicable.
* Use HTTPS in production.

---

# Database Rules

## General Rules

* Use foreign keys.
* Use indexes for:

  * email
  * product search fields
  * category_id
  * brand_id
  * order status
* Add timestamps to all major tables.
* Use transactions for:

  * Checkout
  * Order creation
  * Stock updates

---

# Performance Rules

* Avoid N+1 queries.
* Use optimized joins.
* Paginate large datasets.
* Cache frequently requested data later if needed.
* Optimize product search queries.

---

# Error Handling

* Centralized exception handling.
* Never expose internal server errors directly.
* Log unexpected exceptions.

---

# Logging

Log:

* Authentication failures
* Server exceptions
* Payment failures
* Order creation issues

---

# API Versioning

Use:
`/api/v1/`

Example:
`/api/v1/products`

---

# Future Scalability

The architecture should allow future support for:

* Coupons
* Wishlist
* Notifications
* Multi-language support
* Multiple currencies
* Online payments
* Admin dashboard
* Analytics
* Inventory management
* Product variants
* Push notifications

---

# Important Development Rules For Claude

When generating code:

1. Always separate business logic from controllers.
2. Always use reusable helper functions.
3. Always validate request data.
4. Always return standardized JSON responses.
5. Always write modular code.
6. Prefer reusable services over duplicated logic.
7. Use descriptive naming conventions.
8. Keep functions small and focused.
9. Write secure SQL queries using prepared statements.
10. Never hardcode secrets.

---

# Recommended Initial Database Tables

* users
* user_tokens
* advertisements
* categories
* brands
* products
* product_images
* product_options
* product_option_values
* product_reviews
* carts
* cart_items
* addresses
* delivery_types
* payment_types
* orders
* order_items
* order_status_history

---

# Recommended API Groups

## Authentication

* POST /register
* POST /login
* POST /logout

## Home

* GET /home

## Products

* GET /products
* GET /products/{id}
* GET /products/search

## Categories

* GET /categories
* GET /categories/{id}/products

## Brands

* GET /brands
* GET /brands/{id}/products

## Deals

* GET /deals

## Cart

* GET /cart
* POST /cart/add
* PUT /cart/item
* DELETE /cart/item

## Checkout

* POST /checkout

## Orders

* GET /orders
* GET /orders/{id}
* POST /orders/{id}/cancel

## Profile

* GET /profile
* PUT /profile

## Addresses

* GET /addresses
* POST /addresses
* PUT /addresses/{id}
* DELETE /addresses/{id}

## Reviews

* POST /products/{id}/reviews

---

# Development Philosophy

The project should prioritize:

* Simplicity
* Security
* Maintainability
* Scalability
* API consistency
* Clear architecture

Avoid overengineering.
Build reusable systems.
Keep the backend production-ready.
