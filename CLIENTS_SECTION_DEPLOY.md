# Clients section — production deploy checklist

No local PHP/DB is required. Test on production after deploy.

## Deploy order

1. **Backend** — deploy `innovation-tech-backend` code.
2. On the server run:
   ```bash
   php artisan migrate --force
   php artisan permission:cache-reset
   ```
   (Or clear Spatie permission cache however you usually do.)
3. If client logos 404 after upload: `php artisan storage:link`
4. **Dashboard** — build and deploy `innovation-tech-dashboard`.
5. **Website** — build and deploy `Innovation-Technology`.

The migration `2026_09_18_150000_create_clients_table_and_seed_cms` will:

- Create the `clients` table
- Add `view/create/edit/delete_clients` permissions and attach them to roles/users that already have partner permissions
- Merge default `home.clients` `{ tag, title, subtitle }` into existing EN + AR page content **only if missing** (does not overwrite live copy)

Optional fresh seeds (dev only — do **not** re-run full `PageContentSeeder` on production):

```bash
php artisan db:seed --class=ClientsSeeder
```

## Verify after deploy

1. **Dashboard → Clients** (`/clients`): create/edit/delete logos, reorder, toggle active.
2. **Dashboard → Pages → Home → Clients headers**: edit EN + AR tag/title/subtitle and save.
3. **Public home**: Clients marquee appears **below Partners**; empty list hides the section.
4. Switch site language to Arabic — Clients headers show AR copy.
5. **Team Members** add/edit modal: typing must keep focus (per-keystroke unfocus bug fixed).
6. **Site Settings → Client satisfaction (%)** still works (unchanged; unrelated to logo Clients).

## Rollback

```bash
php artisan migrate:rollback --step=1
```

Then redeploy previous dashboard/website builds. Leftover `clients` keys in `page_contents` JSON are harmless.
