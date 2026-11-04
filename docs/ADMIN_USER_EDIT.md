# FSH-26 — Admin user editing

Admin-only routes, inside the authenticated web middleware group:

| Method | URL | Contract |
| --- | --- | --- |
| GET | `/admin/users/{user}/edit` | Browser: real Blade form. Accept JSON: `data` with id/name/email only. |
| PUT | `/admin/users/{user}` | Required name/email, max 255 characters, email unique except target. |

Only name and email are editable; role/status/password payload fields are ignored.
No migration is required. Changes use a database transaction. Save=false or an
exception rolls back; logs contain actor ID, target ID and changed field names,
without field values or SQL/exception messages.

JSON responses: 200 on success, 401 for guest, 403 for non-admin, 404 for missing
target, 422 for invalid input and 500 for failed persistence. Browser validation
redirects with inline errors and old input. Browser save redirects to the edit
page with a localized success notice; persistence failure redirects with an
error and safe name/email input so the admin can retry. CSRF remains enabled.

## Handoff to FSH-25 and FSH-27

FSH-25 owns the separate database-backed user list/detail pages and their search,
pagination and empty states. Link its Edit action with
`route('admin.users.edit', $user)`; the destination is now a usable form, not a JSON
screen. No user list/detail placeholders or duplicate routes are introduced here.
FSH-27 owns block/activate/delete; this edit endpoint must not change status or role.
This PR completes the edit destination without claiming completion of FSH-25/27.

The form uses the authenticated layout, existing `x-ui.form-field`,
`x-ui.button`, `profile-panel` and external `resources/js/ui.js` form state/focus
behavior. No inline CSS or JavaScript. Indonesia/English messages follow session
locale; the breadcrumb identifies Edit user.

## Verification

Worktree test command with shared dependency autoload isolated to this checkout:
`php D:/dev/project/anufish/vendor/phpunit/phpunit/phpunit --bootstrap ../review-bootstrap.php --colors=never`.
The helper is local-only; a normal checkout uses `php artisan test`.
SQLite in-memory and test fixtures; no application database or live AI requests.

- Full PHP suite: 196 tests, 916 assertions pass.
- AdminUserEditTest: 16 tests, 72 assertions pass: existing JSON contracts,
  browser form/read/save, duplicate email and retained input, guest/non-admin/
  missing target, safe audit, field allowlist, ID/EN and post-write rollback.
- Pint and git diff --check pass. npm run build passes.
- Browser QA on isolated synthetic database: desktop ID validation and successful
  persistence, mobile 390x844 EN validation, language switch and returned enabled
  submit button. Mobile document width is 390px with no horizontal overflow;
  form width 358px. Existing external form state supplies disabled Saving feedback.
- Route bootstrap probe with output_buffering=0: headers_sent=false.

QA server and synthetic authentication helper are never committed or deployed.
No CI checks are configured; these are local test and browser results, not CI.
