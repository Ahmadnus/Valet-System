# Changelog

All notable changes made during the technical audit and hardening of the
Valet-System API are documented here. Grouped by category, with the reason and
impact of each change. Entries reference the commit that introduced them.

The baseline for this changelog is commit `1439141` ("final version"), the state
of the repository before the audit.

---

## Mobile-Readiness Fixes (post-audit)

### Two broken endpoints implemented
- **Files:** `app/Http/Controllers/RequestController.php`,
  `app/Http/Controllers/PaymentController.php`
- **Problem:** `PUT /api/parking-requests/{userRequest}/prepare`
  (`requestCarPreparation`) and `GET /api/payments/latest` (`showLatestPayment`)
  were routed to controller methods that did not exist — every call returned a
  500. The prepare route is a core customer flow.
- **Change:** Implemented `requestCarPreparation` (drives the request to
  `preparing` through the state machine via the `PreparingCarRequests` form
  request) and `showLatestPayment` (returns the latest valet time + invoice).
- **Verified:** both routes now resolve (401 when unauthenticated, JSON
  envelope) instead of 500.

### Standardized API response envelope
- **Files:** `bootstrap/app.php`, `app/Http/Controllers/AuthController.php`,
  `app/Http/Controllers/PaymentController.php`
- **Problem:** The API returned at least four different shapes. Worst,
  `AuthController::signup` returned a raw array and returned error messages as
  plain strings with HTTP 200; `login`/`verify` returned bare bodies; the
  exception handler used a different shape than the `ApiResponse` trait.
- **Change:** Adopted the `ApiResponse` envelope `{ success, message, data, code }`
  (plus `errors` for validation) everywhere: the global exception handler now
  emits it, and `AuthController` and `PaymentController` were converted to use
  the `successResponse`/`errorResponse` helpers. Bad login now returns 401 in the
  envelope; validation returns 422 with `errors`.
- **Impact:** A single response contract a mobile client can model against.
- **Note (breaking):** auth responses now nest `user`/`token` under `data`.

---

## Security Fixes

### Leaked Firebase service-account credentials removed from version control
- **Commit:** `751492b`
- **Files:** `firebase_config.json` (deleted from tracking), `.gitignore`
- **Problem:** A live Firebase service-account **private key** was committed to
  the repository and was not gitignored. Anyone with repository access could
  authenticate as the Firebase admin service account (send push notifications,
  read/write Firebase resources).
- **Change:** Stopped tracking `firebase_config.json` and added it to
  `.gitignore`. The file is retained on disk for local runtime only.
- **Impact:** Stops further exposure through the repository.
- **⚠️ Still required (cannot be done from code):** the key must be **rotated**
  in the Google Cloud console and **purged from git history**; it remains valid
  and visible in prior commits until then.

### Unauthenticated / broken notification route removed
- **Commit:** `6b05302`
- **Files:** `routes/api.php`
- **Problem:** `POST /send-notification` was registered without `auth:sanctum`
  and pointed to a non-existent `NotificationController::send` method — an
  unprotected route that could only error at runtime.
- **Change:** Removed the dead, unauthenticated route.
- **Impact:** Removes an unauthenticated, broken endpoint from the API surface.

### Rate limiting added to authentication and OTP endpoints
- **Commit:** `5a91c57`
- **Files:** `routes/api/auth.php`
- **Problem:** `signup`, `login`, `verify`, and `verifylog` had no throttling. A
  5-digit OTP (100k keyspace) within a 5-minute window is brute-forceable.
- **Change:** Applied `throttle:6,1` (6 requests/minute/IP) to all four routes.
- **Impact:** Makes OTP/credential brute-forcing impractical.

### Cryptographic OTP generation and constant-time comparison
- **Commit:** `e863961`
- **Files:** `app/Services/AuthService.php`
- **Problem:** OTPs were generated with `mt_rand` (predictable, not
  cryptographically secure) and compared with a loose `!=` (timing side-channel).
- **Change:** Switched to `random_int` for generation and `hash_equals` for
  comparison.
- **Impact:** OTP values are unpredictable and compared in constant time.

### Access token issued only after OTP verification (2FA bypass fixed)
- **Commit:** `85e6ba6`
- **Files:** `app/Services/AuthService.php`
- **Problem:** `login()` created and returned a usable Sanctum token immediately
  after the password check, **before** OTP verification — making the OTP step a
  no-op (full two-factor bypass).
- **Change:** Removed token issuance from `login()`; the token is now created
  inside `verifyOtpCode()` only after a successful OTP check.
- **Impact:** Authentication genuinely requires both password and OTP.
- **Verified:** login issues 0 tokens; OTP verification issues exactly 1.

### Payments confirmed only via signature-verified Stripe webhook
- **Commit:** `32a6be8`
- **Files:** `app/Http/Controllers/PaymentController.php`, `app/Models/Payment.php`,
  `config/services.php`, `routes/api/payment.php`, `.env.example`
- **Problem (payment bypass):** `paymentSuccess` (a public GET) marked a payment
  `completed` from an unverified `session_id` query param, and when it was
  missing it completed *the latest pending payment* outright. Compounding this,
  `stripe_session_id` was missing from `Payment::$fillable`, so it was never
  persisted and the lookup always fell through to that "latest pending" branch.
- **Change:**
  - Added `handleWebhook()` that verifies the Stripe signature with
    `Webhook::constructEvent` and `STRIPE_WEBHOOK_SECRET`, then idempotently
    marks the matching payment completed on `checkout.session.completed`. This
    is now the single source of truth for payment confirmation.
  - Made `paymentSuccess()` read-only (informational landing) and moved it out
    of the Sanctum group (the browser redirect carries no bearer token).
  - Added `stripe_session_id` to `Payment::$fillable`.
  - Added `STRIPE_WEBHOOK_SECRET` to config and `.env.example`.
- **Impact:** Payments can no longer be forged by hitting a URL; state changes
  only on a cryptographically verified Stripe event.
- **Verified:** unsigned and bad-signature webhook POSTs return HTTP 400.

### Mass-assignment lockdown on the User model
- **Commit:** `ec683c4`
- **Files:** `app/Models/User.php`
- **Problem:** `User` used `$guarded = []`, allowing every column to be
  mass-assigned, including `id`, `email_verified_at`, and `remember_token`.
- **Change:** Replaced with an explicit `$fillable` list (`name`, `email`,
  `phone_number`, `password`, `userable_id`, `userable_type`).
- **Impact:** Sensitive/identity columns can no longer be set via mass
  assignment. All existing call sites use explicit validated arrays, so
  behaviour is unchanged.

### RequestPolicy added; payment IDOR closed
- **Commit:** `566e92e`
- **Files:** `app/Policies/RequestPolicy.php` (new),
  `app/Http/Controllers/PaymentController.php`
- **Problem (IDOR):** `createStripePayment()` started a Stripe checkout for a
  route-bound `Request` without verifying the request belonged to the
  authenticated customer — any customer could pay against (and read the amount
  of) another customer's request.
- **Change:** Added an auto-discovered `RequestPolicy` with `view`/`pay`
  abilities requiring ownership (preserving the existing Manager override) and
  enforced it via `$this->authorize('pay', $userRequest)`.
- **Impact:** Object-level authorization now protects the payment flow.
- **Verified:** owner allowed, non-owner denied (403), manager allowed.

---

## Bug Fixes

### `updateStatus` route parameter binding
- **Commit:** `3358e81`
- **Files:** `app/Http/Controllers/RequestController.php`
- **Problem:** The route declared `{userRequest}` but the controller argument was
  `$requestId`; the names did not match, so the id was never injected and the
  route failed to resolve.
- **Change:** Renamed the argument to `$userRequest` to match the route.
- **Impact:** The parking-request status-update endpoint resolves correctly.
- **Verified:** the endpoint returns 401 (auth) rather than a 500 binding error.

### `stripe_session_id` never persisted
- **Commit:** `32a6be8` (see Security → Stripe webhook)
- **Files:** `app/Models/Payment.php`
- **Problem:** The column was absent from `$fillable`, so
  `update(['stripe_session_id' => ...])` was silently discarded.
- **Change:** Added the column to `$fillable`.
- **Impact:** The Stripe session id is stored and payments can be matched.

### `login()` returned HTTP 500 on invalid credentials
- **Commit:** `e66b4ed`
- **Files:** `app/Services/AuthService.php`
- **Problem:** `login()` threw a generic `Exception` on a bad password, which the
  new exception handler treated as an unexpected error (500). It also did not
  guard against a null user.
- **Change:** Throw `AuthenticationException` (mapped to 401 by the handler) and
  added a null-user guard.
- **Impact:** Invalid credentials return a correct 401.

### `CanceledState` could never cancel a request (two schema mismatches)
- **Commit:** `064664a`
- **Files:** `app/Services/Requests/ParkingRequestState/States/CanceledState.php`,
  `database/migrations/2025_07_08_000000_make_key_box_nullable_on_rejected_requests_table.php` (new)
- **Problem:** Surfaced by the new state-machine tests. When canceling a pending
  request, `CanceledState` inserted a `rejected_requests` row that (1) never set
  `key_box` (a NOT NULL column) and (2) wrote to `'reason'` when the actual
  column is `rejection_reason` (also NOT NULL). Cancellation therefore always
  crashed with a NOT NULL constraint violation.
- **Change:** Use the correct `rejection_reason` column (defaulting to an empty
  string) and add a migration making `key_box` nullable — at cancellation the
  request is still pending, so no physical key box has been assigned yet.
- **Impact:** Canceling a pending request now succeeds.
- **Verified:** covered by `test_agent_can_cancel_a_pending_request`.

### `getMessages()` returned the wrong relation
- **Commit:** `88c75df` (see Performance → pagination)
- **Files:** `app/Services/ChatService.php`
- **Problem:** The method built a filtered, eager-loaded query into `$messages`
  but then returned the unfiltered lazy `$chat->messages` relation instead.
- **Change:** Return the paginated query that was actually built.
- **Impact:** Chat message retrieval returns the correct, filtered result set.

---

## Architectural / Design Improvements

### Centralized API exception handling
- **Commit:** `bb3c7ec`
- **Files:** `bootstrap/app.php`
- **Problem:** `withExceptions` was empty, so unhandled errors returned framework
  HTML and (with `APP_DEBUG` on) leaked messages and stack traces to clients.
- **Change:** Registered a render callback for API/JSON requests returning a
  consistent `{success, message, errors}` envelope for validation (422), auth
  (401/403), not-found (404), and HTTP exceptions, plus a generic `Server error.`
  for unexpected 500s unless `app.debug` is enabled (details logged via
  `report()`).
- **Impact:** Consistent API error contract; no internal detail leakage in
  production.
- **Note (breaking):** API error response shape changed.

### Notification listeners queued (off-request I/O)
- **Commit:** `2185320`
- **Files:** all 7 listeners under `app/Listeners/`, `bootstrap/app.php`
- **Problem:** The seven notification listeners ran synchronously, so every
  request that dispatched an event blocked on Firebase/DB I/O inside the
  request/response cycle. Six already imported the queue traits but never
  implemented them.
- **Change:** Made all seven `implements ShouldQueue` (using
  `InteractsWithQueue`) so notifications are processed off-request on the
  database queue.
- **Impact:** External notification I/O no longer blocks API responses.

### Scheduler cadence corrected
- **Commit:** `2185320`
- **Files:** `bootstrap/app.php`
- **Problem:** `parking:cancel-expired` and `fee-waivers:auto-reject` were
  scheduled `->everySecond()` — unsupported by a standard once-a-minute cron and
  liable to pile up overlapping runs.
- **Change:** Changed to `->everyMinute()->withoutOverlapping()`.
- **Impact:** Reliable, non-overlapping scheduled execution.

### Pagination on unbounded list endpoints
- **Commit:** `88c75df`
- **Files:** `app/Services/AgentService.php`, `app/Services/ChatService.php`,
  `app/Services/ManagerService.php`
- **Problem:** The all-users (Manager), agent-requests, and chat-messages queries
  used `->get()` and materialized/serialized unbounded, growing collections on
  every call.
- **Change:** Converted to `->paginate()` (15/15/20). Bounded reference-data
  lists (car types, locations, parking slots) were intentionally left as-is.
- **Impact:** Bounded memory and payload size on list endpoints.
- **Note (breaking):** these list responses now carry `data`/`meta`/`links`.

### Dead code and stray classes removed
- **Commit:** `de990fc`
- **Files:** `app/Models/LoyaltyPointController.php` (deleted),
  `app/Services/Agent.php` (deleted)
- **Problem:** An empty Eloquent model misplaced in `Models` (shadowing the real
  HTTP controller name) and an empty service stub, both unreferenced.
- **Change:** Deleted both.
- **Impact:** Less confusion; cleaner structure. The loyalty routes correctly use
  `App\Http\Controllers\LoyaltyPointController`.

---

## Documentation

### Project README
- **Commit:** `fa2dc74`
- **Files:** `README.md`
- **Problem:** The README was the stock Laravel boilerplate with no
  project-specific information.
- **Change:** Documented purpose, tech stack, architecture, installation
  (including the required out-of-band Firebase credentials), the auth/OTP flow,
  API route layout, and security caveats.
- **Impact:** A developer can now understand and run the project.

---

## Tests

### Feature/unit tests for auth, payments, and authorization
- **Commit:** `e66b4ed`
- **Files:** `tests/Feature/AuthOtpTest.php` (new),
  `tests/Feature/PaymentWebhookTest.php` (new),
  `tests/Unit/RequestPolicyTest.php` (new), `phpunit.xml`
- **Added coverage:**
  - Login issues no token before OTP; token granted only after OTP; bad
    credentials rejected (401).
  - Webhook rejects missing/invalid signatures; success landing is read-only.
  - Policy allows owner/manager, denies non-owner.
- **Config:** Enabled in-memory SQLite for the test run (`phpunit.xml`).

### State-machine tests for the parking-request lifecycle
- **Commit:** `064664a`
- **Files:** `tests/Feature/ParkingRequestStateMachineTest.php` (new)
- **Added coverage (13 tests):**
  - Happy-path transitions: accept, start, deliver, complete, cancel.
  - Illegal transitions rejected: start-when-not-accepted,
    deliver-when-not-preparing, complete-when-not-delivering,
    complete-with-mismatched-plate, cancel-when-not-pending, unknown status.
  - Authorization guards: a customer cannot accept; an unavailable agent cannot
    accept.
- **Note:** These tests surfaced the `CanceledState` bug documented above.

---

## Files Changed (summary)

| File | Type | Change |
|------|------|--------|
| `firebase_config.json` | deleted (untracked) | Remove leaked credentials |
| `.gitignore` | modified | Ignore credentials file |
| `routes/api.php` | modified | Remove dead notification route |
| `routes/api/auth.php` | modified | Rate limiting |
| `routes/api/payment.php` | modified | Webhook + public success route |
| `app/Services/AuthService.php` | modified | OTP hardening, token-after-OTP, 401 on bad login |
| `app/Http/Controllers/PaymentController.php` | modified | Webhook, read-only success, IDOR authorize |
| `app/Http/Controllers/RequestController.php` | modified | Route-binding fix |
| `app/Models/Payment.php` | modified | `stripe_session_id` fillable |
| `app/Models/User.php` | modified | Explicit `$fillable` |
| `config/services.php` | modified | Stripe webhook secret |
| `.env.example` | modified | Stripe env vars |
| `bootstrap/app.php` | modified | Exception handler + scheduler cadence |
| `app/Listeners/*` (7 files) | modified | `ShouldQueue` |
| `app/Services/AgentService.php` | modified | Pagination |
| `app/Services/ChatService.php` | modified | Pagination + relation bug |
| `app/Services/ManagerService.php` | modified | Pagination |
| `app/Policies/RequestPolicy.php` | new | Ownership authorization |
| `app/Models/LoyaltyPointController.php` | deleted | Stray class |
| `app/Services/Agent.php` | deleted | Stray class |
| `README.md` | modified | Project documentation |
| `phpunit.xml` | modified | In-memory SQLite |
| `tests/Feature/AuthOtpTest.php` | new | Auth/OTP tests |
| `tests/Feature/PaymentWebhookTest.php` | new | Payment/webhook tests |
| `tests/Unit/RequestPolicyTest.php` | new | Policy tests |
| `app/Services/Requests/ParkingRequestState/States/CanceledState.php` | modified | Cancel bug fix (`064664a`) |
| `database/migrations/2025_07_08_000000_make_key_box_nullable_on_rejected_requests_table.php` | new | Make `key_box` nullable (`064664a`) |
| `tests/Feature/ParkingRequestStateMachineTest.php` | new | State-machine tests (`064664a`) |

---

## Outstanding (not yet addressed)

- **Rotate the leaked Firebase key and purge it from git history** (manual;
  cannot be done from code).
- Extract HTTP concerns out of the service layer (`CalculateService`,
  `StripeService` still return `response()->json(...)`).
- Replace magic status strings with a backed enum across the state machine.
- Collapse the dual role / `userable_type` authorization model into one source
  of truth.
- Broader test coverage (fee-waiver, loyalty, ratings, distance calculation).
