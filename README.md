# SimpleCarRental (Symfony 7, Doctrine, Tailwind)

Simple car rental demo built with Symfony 7, Doctrine ORM, Twig, Webpack Encore, and Tailwind CSS v4. It supports authentication, car search with availability filtering, reservations, and a sandbox payment flow via Sentoo.

## Prerequisites

- PHP 8.2+ with required extensions (ctype, iconv)
- Composer
- Node.js 18+ and npm
- Symfony CLI (recommended) for local server and tooling
- Database: MySQL/MariaDB (dev) or SQLite (tests)

## 1) Clone and install dependencies

Windows PowerShell (run in the project root):

```
composer install
npm ci
```

If you don’t have Composer in PATH, you can use the Symfony wrapper:

```
symfony composer install
```

## 2) Configure environment

Copy `.env` to `.env.local` and set your values. Minimum required:

```
# Example MySQL connection
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/simple_car_rental?serverVersion=8.0&charset=utf8mb4"

# Routing base for CLI URL generation
DEFAULT_URI=http://localhost:8000

# Sentoo sandbox (replace with your keys)
SENTOO_BASE_URL="https://api.sandbox.sentoo.io"
SENTOO_MERCHANT="<your-merchant-id>"
SENTOO_SECRET="<your-secret>"
SENTOO_CURRENCY="ANG"
SENTOO_RETURN_URL="http://localhost:8000/payments/return"
```

Optional: seed cars after DB is created: see `sql/cars_seed.sql`.

## 3) Create database and run migrations

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```

## 4) Build frontend assets

Development build with watch:

```
npm run dev
```

Production build:

```
npm run build
```

Assets output to `public/build/` via Webpack Encore. Tailwind v4 is enabled via PostCSS.

## 5) Run the app

Using Symfony CLI (recommended):

```
symfony serve --no-tls
```

Open http://127.0.0.1:8000

If you prefer PHP’s built-in server:

```
php -S 127.0.0.1:8000 -t public
```

## Features overview

- Auth: register/login with CSRF protection
- Car search: filter by passengers; results show only cars available for the selected date range
- Reservations: create, list your reservations; total price computed from car price × days
- Payments (sandbox):
   - Creates a pending reservation (isPaid=false), calls Sentoo to create a payment, and redirects to `success.data.url`
   - Saves payment reference from `success.message` to the reservation
   - Form submit is a full navigation (Turbo disabled) to avoid CORS issues

## Payment setup notes

- Ensure `.env.local` contains the valid `SENTOO_MERCHANT` and `SENTOO_SECRET`.
- The payment creation is a server-side POST (form-urlencoded) with headers:
   - `Content-Type: application/x-www-form-urlencoded`
   - `X-SENTOO-SECRET: <your-secret>`
- On success, provider returns JSON `{ success: { data: { url }, message } }`.
- The app redirects the user to `success.data.url` for hosted checkout.

## Tests (optional)

This project includes basic tests configured for SQLite. To run them:

```
vendor\bin\phpunit
```

## Troubleshooting

- Styles not loading?
   - Ensure `public/build/app.css` exists and the base layout includes Encore assets.
   - Try `npm run dev` again and hard-refresh the browser.

- Payment doesn’t redirect / CORS errors?
   - The Rent form is configured to do a full-page submit (no XHR) to avoid CORS.
   - Check `var/log/dev.log` for the payment response and the chosen redirect URL.
   - Verify your Sentoo credentials and currency in `.env.local`.

- Database issues?
   - Double-check `DATABASE_URL` and run migrations.
   - On schema drift, run `php bin/console doctrine:schema:validate`.

## Project structure (high level)

- `src/Entity`: Doctrine entities (User, Car, Reservation)
- `src/Repository`: Custom queries (availability checks, search)
- `src/Controller`: MVC controllers (cars, reservations, payment)
- `templates/`: Twig templates (Tailwind-styled)
- `assets/`: JS/CSS sources (Encore, PostCSS, Tailwind)
- `public/`: Document root; built assets in `public/build/`
- `migrations/`: Doctrine migrations

---

If you need a one-liner checklist to verify your environment:

```
symfony -v
php -v
composer -V
npm -v
```

If any command is missing, install it first, then re-run the steps above.
