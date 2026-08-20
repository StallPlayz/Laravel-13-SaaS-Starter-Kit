# Laravel 13 SaaS Starter Kit

A highly opinionated, production-ready Software as a Service (SaaS) boilerplate. Built on **Laravel 13, Vue 3 (Composition API), Inertia v3, and Tailwind CSS**, this starter kit is designed to provide a lightning-fast Single Page Application (SPA) experience while maintaining the robust security of Laravel's stateful session architecture.

---

## Tech Stack & Core Packages

- **Framework:** Laravel 13
- **Frontend:** Vue.js 3 + Vite
- **Data Bridge:** Inertia.js v3 (No manual API endpoints required)
- **Styling:** Tailwind CSS v3/v4
- **Authentication:** Laravel Fortify (Headless Auth)
- **Testing:** Pest PHP

---

## Core Features & Architecture

### 1. Advanced Authentication Flow (Fortify + OTP)
Unlike standard starter kits, this project implements a real-world, highly secure authentication flow:
- **Extended Registration:** Captures granular user data including Phone Numbers, Terms of Service acceptance, and Location (Country, Province, City, District).
- **Two-Factor Authentication (OTP):** Protects against credential stuffing. After a successful password check, users are emailed a 6-digit OTP before gaining access to the dashboard.
- **Session Security:** Automatically revokes previous session tokens upon a new login to prevent concurrent unauthorized access.
- **Rate Limiting:** Throttles failed login attempts rather than the entire route to prevent locking out legitimate users.

### 2. The "Inertia Way" (Stateful SPA)
This project intentionally avoids a separated `api.php` and frontend architecture. 
- All routes are defined in `routes/web.php`.
- CSRF protection and encrypted session cookies are handled automatically by Laravel.
- Vue components act as the view layer, receiving data directly from Laravel controllers via Inertia props.

### 3. Global Toast Notification System
We have replaced clunky third-party notification packages and native browser pop-ups with a seamless, globally available Toast system.
- **Backend Trigger:** Simply flash data to the session in your Laravel controllers:
  ```php
  return redirect()->route('dashboard')->with('success', 'Welcome back!');
  ```
- **Frontend Catch:** The global Vue layout automatically listens for `$page.props.flash` and triggers the custom UI toast without any manual JavaScript intervention.

---

## Project Structure Guide

When navigating the codebase, here are the key directories you need to know:

- `app/Actions/Fortify/` - Contains the core authentication logic (e.g., `CreateNewUser.php` where the extended registration validation lives).
- `app/Http/Controllers/` - Standard Laravel controllers returning `Inertia::render()`.
- `resources/js/pages/` - Vue components that act as full pages (e.g., `Auth/Register.vue`, `Dashboard.vue`).
- `resources/js/components/` - Reusable Vue UI components (Buttons, Inputs, Toast container).
- `resources/js/layouts/` - Global page wrappers (e.g., `AppLayout.vue` which houses the Toast listener).

---

## Getting Started

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & NPM
- A local database (MySQL / PostgreSQL / SQLite)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/your-repo-name.git
   cd your-repo-name
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Important: You must configure your `DB_*` and `MAIL_*` credentials in the `.env` file. The OTP authentication flow requires a working mail driver (e.g., Mailtrap, Resend, or SMTP).*

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Start the Development Servers:**
   You need to run both the backend and frontend compilers:
   ```bash
   # Terminal 1: Start Laravel server
   php artisan serve

   # Terminal 2: Start Vite for Vue/Tailwind
   npm run dev
   ```

---

## Developer Workflow Examples

### Creating a New Page
1. Create a new Vue component in `resources/js/pages/MyPage.vue`.
2. Create a route in `routes/web.php`:
   ```php
   Route::get('/my-page', function () {
       return Inertia::render('MyPage', [
           'customProp' => 'Hello World'
       ]);
   });
   ```

### Handling Form Submissions
Always use Inertia's `useForm` helper for forms. It automatically handles loading states and maps Laravel's `422` validation errors directly to your inputs.
```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post('/login')
}
</script>
```

---

## Deployment

When deploying to production, ensure you build the frontend assets and cache the Laravel configuration:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
