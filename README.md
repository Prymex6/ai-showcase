# AI Showcase — Laravel Edition

> Multi-module AI business toolkit built with **Laravel 11**, **Inertia.js 2**, **Vue 3** and **Tailwind CSS**.

[![PHP](https://img.shields.io/badge/PHP-8.2-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-red)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3-brightgreen)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia.js-2-purple)](https://inertiajs.com)

## Moduły / Modules

| Moduł | Opis |
|-------|------|
| 🎯 **Lead Manager** | CRM — lista, kanban, szablony maili, statystyki |
| 🧾 **FakturaAI** | Faktury, klienci, faktury cykliczne, integracje |
| 🤖 **AI Agent** | Chatbot oparty na GPT, baza wiedzy, historia |
| 📅 **Social Planner** | Planowanie postów, szablony, analityka |
| 📊 **Raporty AI** | KPI dashboard, cele, harmonogram raportów |
| ⭐ **Opinie Manager** | Opinie klientów, odpowiedzi AI, trendy |

## Stack technologiczny

- **Backend**: Laravel 11, PHP 8.2, Eloquent ORM, MySQL 8
- **Frontend**: Vue 3 (Composition API), Inertia.js 2, Tailwind CSS v3, Vite
- **AI**: OpenAI GPT-4o-mini z trybem demo (bez klucza API)
- **Auth**: Laravel Breeze

## Instalacja

```bash
# 1. Klonuj repo
git clone <repo-url> ai-showcase-laravel
cd ai-showcase-laravel

# 2. Zależności PHP
composer install

# 3. Konfiguracja środowiska
cp .env.example .env
php artisan key:generate

# 4. Baza danych (MySQL)
# Utwórz bazę danych: ai_showcase
# Uzupełnij DB_* w .env

# 5. Migracje + dane demo
php artisan migrate --seed

# 6. Zależności Node.js
npm install && npm run build

# 7. Uruchom
php artisan serve
```

## Konfiguracja AI (opcjonalna)

W pliku `.env`:

```env
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-4o-mini
DEMO_MODE=false
```

Bez klucza API aplikacja działa w **trybie demo** z predefiniowanymi odpowiedziami.

## Konto demo

Po seedowaniu:

- **Email**: admin@ai-showcase.pl
- **Hasło**: admin123

## Licencja

MIT
