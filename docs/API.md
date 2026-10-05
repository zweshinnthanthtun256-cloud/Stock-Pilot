# StockPilot API v1

Base URL: `/api/v1`. Protected endpoints require an authenticated Sanctum SPA session and an active account. Validation failures use HTTP 422, missing permission uses 403, and authentication failures use 401.

| Method | Endpoint | Permission | Purpose |
|---|---|---|---|
| POST | `/auth/login` | Public | Start a cookie-based session after first requesting `/sanctum/csrf-cookie`. |
| GET | `/auth/user` | Authenticated | Current user, roles, permissions, and warehouses. |
| POST | `/auth/logout` | Authenticated | Invalidate the current session. |
| GET | `/dashboard` | Authenticated | Optimized KPIs, alerts, warehouse stock, recent movements. |
| GET | `/products` | Authenticated | Paginated, searchable catalog. |
| GET | `/inventory` | Authenticated | Paginated inventory by product and warehouse. |
| POST | `/inventory/manual` | `inventory.adjust` | Atomic stock in/out with mandatory movement record. |
| GET | `/movements` | Authenticated | Filterable immutable ledger. |
| GET | `/purchase-orders` | Authenticated | Paginated purchase orders. |
| POST | `/purchase-orders/{id}/receive` | `purchases.receive` | Partial/final receiving; validates outstanding quantities. |
| GET | `/transfers` | Authenticated | Paginated transfer workflow. |
| POST | `/transfers/{id}/{approve|ship|receive}` | `transfers.manage` | State-gated transfer transition. |
| GET/PUT | `/settings` | `settings.manage` | Read or update validated system configuration. |
| GET | `/notifications` | Authenticated | Paginated database notifications. |
| GET | `/audit-logs` | `audit.view` | Filterable immutable audit history. |
| POST | `/products/{id}/image` | `master.manage` | Validated product image upload. |
| GET | `/products/{id}/barcode` | Authenticated | Printable SVG barcode. |
| GET | `/reports/{report}` | `reports.view` | Inventory, low-stock, movements, purchasing, supplier, transfer, adjustment, or valuation report. |

## Receive request

```json
{"items":[{"item_id":12,"quantity":60}],"notes":"Delivery receipt DR-4021"}
```

Receipt creation, order status, inventory balances, alert evaluation, and stock movements commit as one database transaction.

## Manual stock request

```json
{"warehouse_id":1,"product_id":3,"quantity":5,"direction":"out","reason":"Internal usage","reference":"ISS-104"}
```

The API rejects stock-out below zero or below reserved stock. Inventory rows are unique on `(product_id, warehouse_id)`.
