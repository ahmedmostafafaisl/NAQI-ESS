# Naqi ESS — Employee Self Service Platform

Complete Laravel 11 / PHP 8.4 project: admin dashboard (roles & permissions), full REST
auth API, notification cycle (admins ⇄ users), and a Microsoft Dynamics 365 integration
service.

This is a **complete Laravel project** (official `laravel/laravel` 11.x skeleton — has
`artisan`, `public/index.php`, `bootstrap/`, `storage/`, etc. — with the Naqi ESS app code
merged in). Only `vendor/` is missing, since installing it requires Composer/Packagist
access which isn't available in the environment this was built in.

## 1. Install dependencies

```bash
cd naqi-ess-full
composer install
```

## 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Set your DB credentials in `.env`, and for Dynamics 365 the Azure AD app registration values:

```
DYNAMICS_TENANT_ID=...
DYNAMICS_CLIENT_ID=...
DYNAMICS_CLIENT_SECRET=...
DYNAMICS_RESOURCE=https://yourorg.crm.dynamics.com
```

## 3. Migrate & seed

```bash
php artisan migrate
php artisan db:seed
```

This creates the permission tables, base roles (`super-admin`, `admin`, `employee`,
`customer`) with permissions, and a default admin:

```
Email:    admin@naqi-ess.test
Password: Password@123
```

**Change this password immediately in production.**

## 4. Storage link (for uploaded profile images)

```bash
php artisan storage:link
```

## 5. Run

```bash
php artisan serve
```

- Admin dashboard: `http://localhost:8000/admin/login`
- API base: `http://localhost:8000/api/v1`


## What's included

### Admin dashboard (Blade + Tailwind CDN)
- Session-based login restricted to users holding the `admin` or `super-admin` role
  (`app/Http/Controllers/Auth/AdminAuthController.php`)
- Roles & Permissions CRUD screens (Spatie `laravel-permission`)
- Users CRUD screen with role assignment
- Notification center (view + broadcast to all/employees/customers/specific users)
- Sidebar links auto-hide based on the logged-in admin's permissions (`@can`)

### REST API (Sanctum token auth) — `routes/api.php`, `app/Http/Controllers/Api`
- `POST /api/v1/auth/register` → creates account + sends OTP
- `POST /api/v1/auth/verify-otp` → verifies OTP, issues Sanctum token
- `POST /api/v1/auth/resend-otp`
- `POST /api/v1/auth/login` → login via phone/username/email + password
- `POST /api/v1/auth/login-pin` → quick PIN login
- `POST /api/v1/auth/forgot-password` / `reset-password`
- `POST /api/v1/auth/logout` (auth required)
- `POST /api/v1/auth/change-password`
- `POST /api/v1/auth/fcm-token` → register device push token
- `GET/POST /api/v1/profile`
- `GET /api/v1/notifications`, `unread-count`, `{id}/read`, `read-all`, `DELETE {id}`

### Notification cycle
- `app/Notifications/GeneralNotification.php` — database + FCM push channels
- `app/Services/NotificationService.php` — `notifyUser`, `notifyUsers`, `notifyAdmins`,
  plus ESS-specific helpers: `leaveRequestSubmitted`, `leaveRequestDecided`,
  `payslipPublished`. Wire these into your leave/attendance/payslip modules as those
  actions occur — they already fan out to the right audience (employee → admins,
  admin decision → employee) and persist to the `notifications` table for the
  in-app feed used by both the dashboard and the API.

### Dynamics 365 integration — `app/Services/Dynamics365Service.php`
- OAuth2 client-credentials token acquisition (cached ~55 min) against Azure AD
- Generic Web API methods: `get`, `find`, `create`, `update`, `delete` (OData)
- Domain helpers: `syncEmployee`, `getEmployee`, `submitLeaveRequest`, `getAttendance`,
  `getPayslips` — adjust entity/field logical names in `config/dynamics365.php` to match
  your org's Dynamics schema
- Registered as a singleton in `AppServiceProvider`; inject `Dynamics365Service` anywhere

## Notes / next steps
- The `users` migration you supplied is included as-is with two small necessary
  additions: `otp_expires_at` (so OTPs actually expire) and `dynamics_id` /
  `dynamics_synced_at` (to link a user to their Dynamics 365 record). Soft deletes were
  also added so admin "delete" doesn't destroy audit history.
- Leave/attendance/payslip domain tables aren't included since they weren't specified —
  the notification and Dynamics services are written to be dropped straight into those
  modules once you build them.
- FCM push requires a Firebase service account JSON at the path configured in
  `FIREBASE_CREDENTIALS`; without it, notifications still work via the `database`
  channel, FCM will just silently no-op/fail per that package's config.
- Run `php artisan permission:cache-reset` after seeding if Blade `@can` directives don't
  pick up new permissions during local dev.
