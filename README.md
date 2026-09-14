# 🏪 Épicerie - Smart Grocery & Point of Sale (POS)

Platform modern untuk toko grosir dan kasir pintar, dirancang dengan pendekatan **Decoupled Architecture** yang memisahkan Frontend Client dan Backend REST API.

---

## 📁 Struktur Proyek (Decoupled Architecture)

Proyek ini terbagi menjadi 3 direktori utama:

```text
Epicerie/
├── backend/      👉 Server REST API Express.js (TypeScript, Supabase PostgreSQL, JWT, Swagger OpenAPI)
├── frontend/     👉 Client Consumer Next.js 15 (React 19, Tailwind CSS v4, Zustand, Kiosk Storefront, Admin)
└── legacy/       👉 Arsip Proyek Lama Laravel 12 (Controller, Blade Views, Migration History)
```

---

## 🚀 Panduan Memulai

### 1. Menjalankan Backend (Express.js API - Port 5000)

Masuk ke folder `backend`, siapkan konfigurasi `.env` dari `.env.example`, lalu jalankan server:

```bash
cd backend
cp .env.example .env
npm install
npm run dev
```

- **REST API Base URL**: `http://localhost:5000/api`
- **Swagger UI Interactive Docs**: `http://localhost:5000/api-docs/`
- **Health Check Endpoint**: `http://localhost:5000/api/health`

### 2. Menjalankan Frontend (Next.js 15 - Port 3000)

Masuk ke folder `frontend`, pastikan `.env.local` merujuk ke API backend:

```bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

Buka aplikasi di browser:
- **Katalog & Kiosk Pelanggan**: [http://localhost:3000/](http://localhost:3000/)
- **Mode Kasir Cepat (Tablet POS)**: [http://localhost:3000/kiosk](http://localhost:3000/kiosk)
- **Dasbor Admin & Kasir**: [http://localhost:3000/admin](http://localhost:3000/admin)
- **Manajemen Ulasan**: [http://localhost:3000/ulasan](http://localhost:3000/ulasan)

---

## 🛡️ Catatan Lingkungan (Environment & Git)

- Seluruh berkas rahasia (`.env`, `.env.local`, `.env.*`) telah diproteksi di `.gitignore`.
- Jangan pernah mengunggah kredensial database atau token rahasia ke publik.
