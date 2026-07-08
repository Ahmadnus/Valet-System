# Valet-System

A Laravel 11 REST API for a valet-parking platform. Customers request valet
parking at a location and slot; valet agents accept, park, prepare, and deliver
cars; managers, hosts, and providers administer locations and staff. The system
also covers Stripe payments, loyalty points, fee-waiver requests, ratings,
real-time chat, and Firebase push notifications.

> **Status:** work in progress. See [Security notes](#security-notes) before
> deploying anywhere public.

## Tech stack

- PHP 8.2+, Laravel 11
- Sanctum (API token auth) + spatie/laravel-permission (roles)
- Stripe (payments), Firebase Cloud Messaging (push), Telegram Gateway (OTP)
- spatie/laravel-medialibrary, astrotomic/laravel-translatable
- Pusher (broadcasting), SQLite by default (any Laravel-supported DB works)

## Architecture

Request flow: `Route → Sanctum auth → type middleware → FormRequest validation →
Service (in a DB transaction) → API Resource → JSON`.

- **Controllers** (`app/Http/Controllers`) stay thin and delegate to services.
- **Services** (`app/Services`) hold the business logic.
- **State pattern** drives the parking-request lifecycle
  (`app/Services/Requests/ParkingRequestState`).
- **Strategy pattern** abstracts payment providers (`PaymentsInterface`).
- **Events/Listeners** fan out notifications; **Observers** react to model changes.

## Requirements

- PHP >= 8.2 with the extensions Laravel 11 requires
- Composer 2
- A Firebase service-account JSON (push notifications)
- Stripe API keys and a Telegram Gateway token (payments / OTP)

## Installation

```bash
git clone https://github.com/Ahmadnus/Valet-System.git
cd Valet-System
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # default DB; or configure another in .env
php artisan migrate --seed
php artisan serve
```

### Firebase credentials (required to boot)

The app resolves Firebase Messaging at boot, so it will not start without
credentials. Provide a service-account JSON **out of band** (it must never be
committed) and point `.env` at it:

```env
FIREBASE_CREDENTIALS=/absolute/path/to/firebase_service_account.json
```

### Relevant environment variables

```env
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=        # see roadmap: webhook verification
TELEGRAM_GATEWAY_TOKEN=
FIREBASE_CREDENTIALS=
```

## Authentication flow

1. `POST /api/auth/signup` — creates the user and sends a 5-digit OTP via Telegram.
2. `POST /api/auth/verify` — confirms the signup OTP.
3. `POST /api/auth/login` — verifies the password and sends a login OTP.
4. `POST /api/auth/verifylog` — confirms the login OTP.

Authenticated requests send `Authorization: Bearer <sanctum-token>`. Set the
`Accept-Language` header (`en`/`ar`) for localized responses.

## API routes

Routes are split by domain under `routes/api/`: `auth`, `request`, `car`,
`rating`, `payment`, `loyalty`, `chat`, `tracking`, and the valet role groups.
Run `php artisan route:list` for the full, current list.

## API documentation

- OpenAPI 3 specification: [`docs/openapi.yaml`](docs/openapi.yaml) — view it in
  Swagger UI or any OpenAPI viewer.
- Postman collection: [`docs/Valet-System.postman_collection.json`](docs/Valet-System.postman_collection.json) —
  import into Postman, then set the `base_url` and `token` collection variables.
  Bearer auth is applied at the collection level; auth and webhook endpoints
  override it with no-auth.

All endpoints return the standard envelope
`{ "success": bool, "message": string, "data": object|null, "code": int }`,
with an additional `errors` object on `422` validation failures.

## Testing

```bash
php artisan test
```

## Security notes

- Never commit `firebase_config.json` or any credential file; provide them via
  environment/secret storage.
- Payment confirmation and full OTP enforcement are being hardened — do not run
  this against real payment data until those items are complete.

## License

MIT.
