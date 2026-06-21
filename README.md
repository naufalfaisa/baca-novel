# Baca Novel Platform

<img width="1763" height="2137" alt="Screenshot_21-6-2026_81315_localhost" src="https://github.com/user-attachments/assets/3f765dad-7c7c-4af6-a12e-22b67159d2f7" />
<br></br>

Baca Novel is a web application for reading, writing, and managing novels. Built on Laravel 12, it supports three user roles (readers, authors, admins) and integrates with the Xendit payment gateway for premium subscriptions.

## Features

### Readers (Users)
- Profile management and options to apply as an author.
- Novel browsing by genre, popularity, and search.
- Responsive reading interface for novel chapters.
- Personal library to bookmark and track novels.
- Novel voting, content reports, and chapter comments.
- Paid subscription options processed through Xendit.

### Authors
- Dashboard showing novel list and statistics.
- Create and edit novels with covers and genre tags.
- Create, edit, and publish chapters.

### Administrators
- Review and approve/reject author applications.
- Manage novel visibility and publishing status.
- Add, update, and delete any novel or chapter on the platform.

---

## Tech Stack

- Framework: Laravel 12.x
- Authentication: Laravel Breeze
- Styling: Tailwind CSS & PostCSS
- Asset Bundling: Vite
- Payments: Xendit PHP SDK

---

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js (v18 or higher) and NPM

---

## Installation and Setup

1. Clone the repository.

2. Create the environment configuration file:
   ```bash
   cp .env.example .env
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Install frontend assets:
   ```bash
   npm install
   ```

5. Generate the application key:
   ```bash
   php artisan key:generate
   ```

6. Run database migrations and seed data:
   ```bash
   php artisan migrate --seed
   ```

7. Create the storage symbolic link:
   ```bash
   php artisan storage:link
   ```

8. Configure Xendit keys in your `.env` file:
   ```env
   XENDIT_SECRET_KEY=your_xendit_secret_key
   XENDIT_WEBHOOK_TOKEN=your_xendit_webhook_token
   ```

---

## Running the Application

To start the local servers concurrently:
```bash
composer dev
```

Alternatively, run them individually:

1. PHP development server:
   ```bash
   php artisan serve
   ```

2. Vite development server:
   ```bash
   npm run dev
   ```

3. Queue listener for background tasks:
   ```bash
   php artisan queue:listen --tries=1
   ```

---

## License

This software is open-sourced under the MIT license.
