RPLint Dashboard
================
Filament-powered attendance dashboard for RPLint. The app manages students, classes, homeroom teachers, schedules, special days, attendance submissions (including fingerprint events), and monthly/yearly Excel exports.

Features
--------
- Role-aware Filament admin at `/admin` for admins and homeroom teachers.
- Attendance tracking with check-in/out, auto-absent job (`attendance:auto-check-in`), and export jobs (`attendance:export`, `attendance:export-master`).
- Special day rules (holiday/permit/sick/custom) applied per student, class, or globally.
- Student, class, schedule, and homeroom teacher management with photo uploads.
- Mobile/API endpoints for login, attendance feed, and fingerprint devices (Bearer `APP_TOKEN`).

Requirements
------------
- PHP 8.2+, Composer
- Node.js 20+ with npm
- Database (default SQLite) and writable `storage` directory

Setup
-----
1) Install dependencies  
`composer install`  
`npm install`

2) Environment  
`cp .env.example .env`  
Set `APP_KEY` (`php artisan key:generate`) and `APP_TOKEN` (used by fingerprint/API clients). Configure database; for SQLite create `database/database.sqlite`. Run `php artisan storage:link` for profile photos.

3) Database  
`php artisan migrate --seed` (seeds admin/homeroom users, classes, schedules, students, sample attendance).

4) Run  
- Dev: `npm run dev` and `php artisan serve` (or `composer dev` to run serve + queue + vite).  
- Prod build: `npm run build`.

Access
------
- Filament admin: `/admin` (seeded admin `admin@gmail.com` / `12345678`).
- Homeroom users are seeded; they see only their classes.

API quick reference
-------------------
- Auth: `POST /api/app/login` (nis, password) → Sanctum token; `POST /api/app/logout` (auth required).
- App data (Bearer Sanctum): `GET /api/app/home`, `/api/app/account`, `/api/app/recently`, `/api/app/subject`.
- Fingerprint/device (Bearer `APP_TOKEN`): `GET /api/students`, `PUT /api/students/{id}` to update fingerprint templates.
- Attendance device endpoints: `GET /api/students/attendances` (today), `GET /api/students/fingerprint` (all templates), `POST /api/students/fingerprint` (`student_id`, `scanned_at` ISO datetime) to check-in/out.

Console tasks
-------------
- `php artisan attendance:auto-check-in` mark absent (skips weekends, respects special days).
- `php artisan attendance:export` export previous month (basic and advanced) to `storage/app/attendances` and record in `attendance_reports`.
- `php artisan attendance:export-master` export previous year summary.

Notes
-----
- Profile photos are stored on the `public` disk; deleting/updating students cleans old files.
- Classes named `Graduate` are hidden from most class filters/charts.
- Update `APP_URL` in `.env` so generated links and storage URLs resolve correctly.
