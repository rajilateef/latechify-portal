# Latechify Digital Hub

The Latechify Digital Hub marketing website, rebuilt on **Laravel 12 + Livewire 3 + Filament 3**.
Every piece of content is editable from a Filament admin panel — courses, pricing, home‑page
sections, testimonials, FAQs, legal pages, blog posts, and **advert fliers / promotions**.

## Stack

- **Laravel 12** (PHP 8.2), **MySQL**
- **Livewire 3** for the dynamic public forms (contact, apply/enrol, consultation, newsletter)
- **Filament 3** admin panel at `/admin`
- **Tailwind CSS v4** + Alpine.js (bundled with Livewire) + **Lucide** icons
- **Paystack** for online course payments (with a bank‑transfer fallback)

## Local setup

```bash
composer install
npm install
cp .env.example .env        # then set DB_* credentials
php artisan key:generate
php artisan migrate --seed  # creates schema + seeds all content + the admin user
php artisan storage:link
npm run build               # or `npm run dev` while developing
php artisan serve
```

> If `npm install` skips dev dependencies, your shell has `NODE_ENV=production`.
> Run `NODE_ENV=development npm install --include=dev`.

## Admin panel

- URL: **`/admin`**
- Default login: **latechify2024@gmail.com** / **password**  (change the password after first login)

The sidebar is grouped:

| Group | Manage |
|-------|--------|
| **Home Page** | Hero slides, **Adverts & Fliers**, **Why Choose Us**, Testimonials, Stats, Cohort activities, **Partners & Logos** (technologies / accreditations / employers) |
| **Courses & Services** | Courses (category, curriculum, highlights, FAQs, pricing features), Services |
| **About & Content** | Milestones, Core values, Team, FAQs, Pages (Terms/Privacy/Cookies), Cookies, Blog posts, **Certificates** (for `/verify-certificate`) |
| **Submissions** | Applications (+ payments), Contact messages, Consultations, Newsletter subscribers |
| **Settings** | Site Settings (branding, contact, socials, **Paystack keys**, bank details, home copy, SEO) |

### Posting an advert flier (home page)
**Admin → Home Page → Adverts & Fliers → New.** Upload the flier image, add a title, an optional
link, choose the **placement** (home *section*, *pop‑up* on first visit, or *both*) and an optional
schedule window. Active adverts appear immediately in the home "Latest Promotions" section and/or as
a dismissible pop‑up.

### Enabling online payments
**Admin → Settings → Site Settings → Payment** and paste your Paystack public/secret keys (or set
`PAYSTACK_PUBLIC_KEY` / `PAYSTACK_SECRET_KEY` in `.env`). Until keys are set, the *Apply* flow
automatically falls back to the bank‑transfer instructions (bank details are also set on that tab).

## Tests

```bash
php artisan test
```

Covers: all public pages render, all admin panel pages render, and the contact/apply form flows
(record creation, validation, Paystack fallback).
