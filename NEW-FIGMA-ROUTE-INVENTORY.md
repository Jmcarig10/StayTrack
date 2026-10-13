# StayTrack — New Figma Flow Route Inventory

Source of truth: `http://127.0.0.1:8000/` and the Laravel route/view source in `routes/web.php` and `resources/views/`.

## Landing and authentication

- `/` — Landing Page — `NEW FLOW / Landing / Website Source`
- `/login` — Login — `NEW FLOW / Authentication / Login`
- `/register` — Register — `NEW FLOW / Authentication / Register`
- `/forgot-password` — Forgot Password — `NEW FLOW / Authentication / Forgot Password`
- `/reset-password/{token}` — Reset Password — `NEW FLOW / Authentication / Reset Password`
- `/verify-email` and `/verify-email/{id}/{hash}` — Verify Email — `NEW FLOW / Authentication / Verify Email`
- `/confirm-password` — Confirm Password — `NEW FLOW / Authentication / Confirm Password`

## Admin

- `/dashboard` — `NEW FLOW / Admin / Dashboard`
- `/apartments` — `Index`; `/apartments/create` — `Create`; `/apartments/{apartment}` — `Show`; `/apartments/{apartment}/edit` — `Edit`
- `/tenants` — `Index`; `/tenants/create` — `Create`; `/tenants/{tenant}` — `Show`; `/tenants/{tenant}/edit` — `Edit`
- `/leases` — `Index`; `/leases/create` — `Create`; `/leases/{lease}` — `Show`; `/leases/{lease}/edit` — `Edit`
- `/maintenance` — `Index`; `/maintenance/create` — `Create`; `/maintenance/{maintenanceRequest}` — `Show`; `/maintenance/{maintenanceRequest}/edit` — `Edit`
- `/bills` — `Index`; `/bills/create` — `Create`; `/bills/{bill}` — `Show`; `/bills/{bill}/edit` — `Edit`
- `/payments` — `Index`; `/payments/create` — `Create`; `/payments/{payment}` — `Show`; `/payments/{payment}/edit` — `Edit`
- `/profile` — `NEW FLOW / Admin / Profile`

## Tenant

- `/dashboard` (tenant view) — `NEW FLOW / Tenant / Dashboard`
- Tenant dashboard detail states — `My Apartment`, `My Lease`, `My Bills`, `Payment History`
- `/maintenance` — `Index`; `/maintenance/create` — `Create`; `/maintenance/{maintenanceRequest}` — `Show`
- `/profile` (tenant view) — `NEW FLOW / Tenant / Profile`

## Prototype checks

- Landing Sign in → Login; Landing Register → Register.
- Login Sign in → Admin Dashboard; Register Create account → Tenant Dashboard.
- Auth sign-in links and both Admin/Tenant Sign out controls → Login.
- Admin sidebar links → each Admin index/profile screen; table rows and form actions cover Show/Edit/Create states.
- Tenant sidebar links → Dashboard, My Apartment, My Lease, My Bills, Payment History, Maintenance, and Profile.
- Tenant dashboard summary cards and content cards → the corresponding detail screens.
- Responsive mobile navigation items → the matching Admin screens.
- Notification and state actions → the connected dashboard, CRUD, and success destinations.

Shared editable states represented in the route map: validation errors, success notifications, delete confirmation, password confirmation, search/filter/pagination, empty/loading tables, password visibility, and responsive sidebar.

High-fidelity additions are organized under `NEW FLOW - REAL STAYTRACK / States` and `... / Responsive`; reusable state controls are in `... / Components`.
