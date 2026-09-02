# Do Cafe

Do Cafe is a bilingual coffee ecommerce application built with Laravel and Filament. It provides an English/Arabic administration panel, a versioned storefront API, persistent carts, stock-safe checkout, Paymob Unified Checkout, articles, public settings, role-based access control, and media management.

## Main features

- English and Arabic catalog content using Astrotomic Translatable
- Automatic localized slugs generated from English and Arabic names or titles
- Products, hierarchical categories, articles, settings, orders, and coffee-formation stages
- Product, category, article, and stage images through Spatie Media Library
- Filament administration with modal create/edit flows and LTR/RTL localization
- Spatie roles and permissions for administrative authorization
- Sanctum API authentication for storefront customers
- Persistent customer carts with inventory validation
- Transaction-safe checkout with backend-generated idempotency keys
- Delivery fee and tax calculation from administrative settings
- Paymob payment intentions, signed callbacks, transactions, and payment-result handling
- OpenAPI, Swagger UI, and an import-ready Postman collection
- English and Arabic validation and operational messages

## Technology

- PHP 8.3+
- Laravel 13
- Filament 5
- MySQL
- Laravel Sanctum
- Astrotomic Laravel Translatable
- Spatie Laravel Media Library
- Spatie Laravel Permission
- Tailwind CSS 4 and Vite

## Requirements

Install these services directly on the development machine:

- PHP 8.3 or newer with the extensions required by Laravel and MySQL
- Composer
- MySQL
- Node.js and npm

Docker and Laravel Sail are not required.

## Local installation

Clone the repository and enter its directory, then run:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Create a MySQL database and configure `.env`:

```env
APP_NAME="Do Cafe"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=do_cafe
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations, create the public storage link, and build the frontend:

```bash
php artisan migrate
php artisan storage:link
npm run build
```

For active frontend development, use `npm run dev`. Start Laravel locally with:

```bash
php artisan serve
```

The root URL redirects to the Filament administration panel at `/admin`.

## Languages

The application supports English and Arabic:

```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
TRANSLATABLE_LOCALES=en,ar
TRANSLATABLE_USE_FALLBACK=true
```

Storefront API clients select the response language with `Accept-Language: en` or `Accept-Language: ar`. Dynamic catalog translations are stored in database translation tables; interface translations are stored in JSON and validation language files under `lang`.

## Database seeding

Configure the initial administrator before running the root seeder:

```env
ADMIN_USER_NAME="Store Admin"
ADMIN_USER_EMAIL=admin@example.com
ADMIN_USER_PASSWORD="use-a-long-random-password"
```

The administrator password must contain at least 12 characters. Run all seeders with:

```bash
php artisan db:seed
```

This creates roles and permissions, the initial administrator, a bilingual coffee-drink catalog with categories and preparation stages, bilingual coffee articles, and seeded media.

Individual catalog seeders can be safely rerun without duplicating their canonical records:

```bash
php artisan db:seed --class=CoffeeCatalogSeeder
php artisan db:seed --class=CoffeeArticleSeeder
```

Do not suppress model events while seeding translated models. Astrotomic uses model lifecycle events to persist pending translations.

## Media storage

Spatie Media Library stores media on the `public` disk by default:

```env
MEDIA_DISK=public
```

Ensure the public storage link exists:

```bash
php artisan storage:link
```

The web server must be able to write to `storage` and `bootstrap/cache`. Production deployments must include the seed assets under `database/seeders/assets/coffee`.

## Administration panel

Open `/admin` and sign in with the seeded administrator. The panel manages products and formation stages, categories, articles, settings, orders, payment transactions and refunds, and administrative users.

Administrative actions are protected by policies and Spatie permissions. English and Arabic can be switched from the panel, with the corresponding LTR or RTL layout.

## Storefront API

All storefront endpoints are versioned under `/api/v1`.

### Public endpoints

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `/api/v1/auth/register` | Register a customer and issue a token |
| `POST` | `/api/v1/auth/login` | Authenticate and issue a token |
| `GET` | `/api/v1/products` | Search, filter, sort, and paginate products |
| `GET` | `/api/v1/products/{slug}` | View a localized product |
| `GET` | `/api/v1/categories` | List the category hierarchy |
| `GET` | `/api/v1/articles` | List published articles |
| `GET` | `/api/v1/articles/{slug}` | View a published article |
| `GET` | `/api/v1/settings` | Retrieve public settings |
| `POST` | `/api/v1/payments/paymob/webhook` | Receive signed Paymob callbacks |

### Authenticated endpoints

Send `Authorization: Bearer YOUR_TOKEN` with authenticated requests.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/auth/user` | Get the authenticated customer |
| `POST` | `/api/v1/auth/logout` | Revoke the current token |
| `GET` | `/api/v1/cart` | View the cart |
| `POST` | `/api/v1/cart/items` | Add a product |
| `PATCH` | `/api/v1/cart/items/{cartItem}` | Change item quantity |
| `DELETE` | `/api/v1/cart/items/{cartItem}` | Remove an item |
| `POST` | `/api/v1/checkout` | Create an order and Paymob checkout URL |
| `GET` | `/api/v1/orders` | List the customer’s orders |
| `GET` | `/api/v1/orders/{order}` | View an owned order |

API responses use a consistent envelope:

```json
{
  "data": {},
  "message": "",
  "errors": null
}
```

Money is represented as integer minor units. For example, `11500` represents EGP 115.00.

## API documentation

- Swagger UI: `/api/documentation`
- Raw OpenAPI specification: `/api/openapi.yaml`
- Import-ready Postman collection: `/api/postman-collection`
- Source OpenAPI file: `docs/api/openapi.yaml`
- Source Postman template: `docs/api/do-cafe.postman_collection.json`

The served Postman collection generates its `base_url` from `APP_URL`.

## Checkout and Paymob

Configure Paymob using environment variables only:

```env
PAYMOB_BASE_URL=https://accept.paymob.com
PAYMOB_SECRET_KEY=
PAYMOB_PUBLIC_KEY=
PAYMOB_HMAC_SECRET=
PAYMOB_INTEGRATION_IDS=
PAYMOB_NOTIFICATION_URL="${APP_URL}/api/v1/payments/paymob/webhook"
PAYMOB_REDIRECTION_URL="${APP_URL}/payment/result"
PAYMOB_TIMEOUT=20
```

`PAYMOB_INTEGRATION_IDS` is a comma-separated list.

The checkout flow is:

1. An authenticated customer adds available products to the cart.
2. The client submits `POST /api/v1/checkout`.
3. The backend locks inventory, calculates totals, creates an order, generates its idempotency key, and creates a Paymob intention.
4. The API returns `checkout_url`; the frontend redirects the customer to it.
5. Paymob sends a signed server callback to `PAYMOB_NOTIFICATION_URL`.
6. Paymob redirects the customer’s browser to `PAYMOB_REDIRECTION_URL`.
7. The backend verifies the HMAC, amount, currency, and order reference before marking the order paid.

The webhook is the primary payment confirmation. The signed `/payment/result` handler safely reconciles the transaction when the webhook is delayed.

### Local Paymob testing

Paymob cannot send server callbacks to `localhost`. Expose Laravel through a public HTTPS tunnel such as ngrok:

```bash
ngrok http 8000
```

Then configure:

```env
PAYMOB_NOTIFICATION_URL=https://your-tunnel-domain.ngrok-free.app/api/v1/payments/paymob/webhook
PAYMOB_REDIRECTION_URL=http://localhost:8000/payment/result
```

Clear cached configuration and create a new checkout intention:

```bash
php artisan optimize:clear
```

Existing Paymob intentions retain the callback URLs used when they were created.

## Queue worker

Queued media conversions and other background work require a worker:

```bash
php artisan queue:work --tries=3
```

Configure a persistent process manager such as Supervisor in production.

## Testing and quality checks

```bash
php artisan test
vendor/bin/pint --test
composer validate --no-check-publish
npm run build
```

Run `vendor/bin/pint` to format PHP files. Tests use isolated databases, factories, fake media disks, and fake Paymob HTTP responses; they do not require live provider credentials.

## Production deployment

Use production-safe environment settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
```

A typical deployment includes:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Also ensure that:

- `docs/api` and `database/seeders/assets/coffee` are included in the deployment artifact.
- `storage` and `bootstrap/cache` are writable by the PHP process.
- A queue worker is supervised and restarted after deployment.
- Paymob notification and redirection URLs use the production HTTPS domain.
- Secret keys and administrator credentials exist only in the production environment.
- `APP_DEBUG` remains disabled to prevent stack traces from exposing server details.

After changing environment configuration, run:

```bash
php artisan optimize:clear
php artisan config:cache
```

Frequently requested public settings are cached and automatically invalidated after administrative updates.

## License

This is a private Do Cafe application. Distribution and usage are governed by the project owner’s terms.
