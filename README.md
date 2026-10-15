# 📡 WiFi Payment & Attendance System (v2.0 Monolith)

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-19-20232A?style=for-the-badge&logo=react&logoColor=61DAFB)
![Inertia.js](https://img.shields.io/badge/Inertia.js-3.x-9553E9?style=for-the-badge&logo=inertia&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-007ACC?style=for-the-badge&logo=typescript&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)

Unified, modern Enterprise Monolith application built with **Laravel 13**, **Inertia.js**, and **React (TypeScript)**. This application serves as the core management system for **Field Employee Attendance (Presensi)** and **WiFi Subscriber Billing, Subscriptions, & Invoicing**.

---

## 🔄 Project Evolution: v1 vs v2

This project is the **v2.0 Evolution** of the previous production system which was originally built with a decoupled multi-repository architecture:

| Component / Metric     | v1.0 (Decoupled Legacy)                          | v2.0 (Unified Monolith - Current)                          |
| :--------------------- | :----------------------------------------------- | :--------------------------------------------------------- |
| **Architecture**       | Microservices / Decoupled (Multi-repo)           | **Single Unified Monolith** (Single-repo)                  |
| **Backend Engine**     | Golang (Gin-Gonic + GORM)                        | **PHP 8.3 + Laravel 13 (Eloquent ORM)**                    |
| **Frontend Engine**    | React 18 + Vite (Independent SPA)                | **Inertia.js v3 + React 19 (TypeScript)**                  |
| **Data Continuity**    | PostgreSQL `sistem-wifi`                         | **Same PostgreSQL Database (Zero Data Loss / Zero Reset)** |
| **Styling & UI**       | Tailwind CSS + Shadcn UI                         | **100% Identical UI / Design Tokens preserved**            |
| **Deployment**         | Multi-port Docker bridge (Go :8081 & Vite :5173) | **Single Server Deployment (Apache/Nginx/cPanel/VPS)**     |
| **Client Maintenance** | High complexity (Go compilation + SPA CORS)      | **Easy In-House Maintenance for Client IT Team**           |

---

## ✨ Core Features

### 1. 🛡️ Role-Based Access Control (RBAC)

- **Admin (Business Owner / Operator)**: Full access to users, wifi packages, subscriber billing, payment verifications, employee payroll slip generation, and attendance recap reports.
- **Employee (Field Worker)**: Daily GPS-based clock-in/clock-out, customer onboarding, overtime claims, and allowance tracking.
- **Customer (WiFi Subscriber)**: Self-service portal to check active internet packages, billing due dates, and payment history.

### 2. 📍 Smart Attendance & Anti-Fraud Security

- **GPS Coordinates Logging**: Records latitude & longitude upon Clock-In and Clock-Out.
- **Device Lock Policy**:
  - **Employee**: Strictly locked to 1 physical device (`device_id`) to eliminate attendance fraud / buddy-punching (_titip absen_).
  - **Admin & Customer**: Unlocked for flexible multi-device login across smartphones, PCs, and tablets.
- **Double-Attendance Prevention**: Database unique constraint guarantees zero duplicate records per employee per day.

### 3. 💰 WiFi Billing & Subscription Management

- **Recurring Subscriptions**: Automatic tracking of monthly billing cycles (`billing_day` and `next_due_date`).
- **Invoicing & Taxes**: Automated invoice generation with customizable package prices and PPN calculations.
- **Payment Verification**: Cash and bank transfer verification workflows.

### 4. ⏰ Automated Background Scheduler

- **12:01 WIB**: Automated national holiday detection and attendance status adjustment.
- **17:01 WIB**: Automated check-out for employees who forgot to clock out at the end of their shift.

### 5. 📄 Payroll Slips & Reporting

- Export employee payroll slips (_Slip Gaji_) and monthly attendance recap reports directly to PDF.

---

## 🚀 Tech Stack

- **Backend Framework:** [Laravel 13](https://laravel.com/) (PHP 8.3+)
- **Adapter Layer:** [Inertia.js v3](https://inertiajs.com/)
- **Frontend Framework:** [React 19](https://react.dev/) with [TypeScript](https://www.typescriptlang.org/)
- **Styling:** [Tailwind CSS v4](https://tailwindcss.com/)
- **UI Component System:** [Radix UI](https://www.radix-ui.com/) & [shadcn/ui](https://ui.shadcn.com/)
- **Icons & Notifications:** [Lucide React](https://lucide.dev/) & [Sonner Toast](https://sonner.emilkowal.ski/)
- **Database:** PostgreSQL (Compatible with GORM v1 database schema)
- **Permissions:** [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Routing:** [Ziggy](https://github.com/tighten/ziggy)

---

## 📁 Project Structure

```text
System-Wifi-Laravel/
├── app/
│   ├── Console/Commands/       # Background Scheduler Commands (Attendance Auto-Checkout, etc.)
│   ├── Http/
│   │   ├── Controllers/        # Inertia Controllers (Admin, Employee, Customer, Auth)
│   │   ├── Middleware/         # Role & Session Guard Middlewares
│   │   └── Requests/           # Form Request Validations
│   └── Models/                 # Eloquent Models (User, Attendance, WifiPackage, Payment, etc.)
├── config/                     # Application configurations (session, database, auth)
├── database/                   # Migrations & Seeders
├── resources/
│   ├── css/                    # Tailwind CSS v4 stylesheets
│   └── js/                     # Inertia + React TypeScript
│       ├── Components/         # Shared UI Components (Buttons, Dialogs, Tables, Sonner)
│       ├── Layouts/            # AppLayout, AppSidebar, Header, ThemeProvider
│       └── Pages/              # Inertia Views (Admin, Employee, Customer, Auth)
├── routes/
│   ├── web.php                 # Web & Inertia Application Routes
│   └── console.php             # Task Scheduling definitions
└── public/                     # Compiled Assets and Static Files
```

---

## 🛠️ Quick Start & Setup

### 1. Prerequisites

- **PHP 8.3** or higher installed with extensions: `pdo_pgsql`, `mbstring`, `intl`, `bcmath`, `curl`.
- **Composer 2.x**.
- **Node.js 18+** and **npm** / **pnpm**.
- **PostgreSQL** server running locally or remotely.

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Configure your PostgreSQL database connection:

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistem-wifi
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Configure Session Lifetime (3 Hours Auto-Logout):

```ini
SESSION_LIFETIME=180
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install
```

### 4. Running the Development Server

Run Laravel and Vite concurrently:

```bash
npm run dev
# OR run separately:
# Terminal 1: php artisan serve
# Terminal 2: npm run dev
```

Open your browser at `http://localhost:8000`.

---

## 🔒 Security & Data Migration Notes

- **Password Hashes**: The existing database uses standard `bcrypt` hashing from Golang (`$2a$`), which is 100% native-compatible with Laravel's `Hash::make()` / `bcrypt` (`$2y$`). All existing users and admins can log in immediately without password resets.
- **Database Safety**: Never run `php artisan migrate:fresh` on existing databases to protect production subscriber and attendance records.

---

_Built for Modern ISP & Workforce Management — Evolved into v2.0 Monolith._
