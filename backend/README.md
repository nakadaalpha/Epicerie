# Épicerie Backend, Database & API Documentation

Direktori ini merupakan pusat arsitektur backend, skema database Supabase, dan dokumentasi kontrak API resmi:

- `docs/`: Dokumentasi **OpenAPI 3.0** (`openapi.json`) dan **Swagger UI Standalone** (`index.html`).
- `database/`: Berkas skema master SQL PostgreSQL / Supabase (`schema.sql`), migrasi, dan relasi 11 tabel legacy.
- `scripts/`: Skrip otomasi (sinkronisasi data, migrasi media Cloudinary, backup berkas).

## Menjalankan Swagger UI Dokumentasi API
Anda dapat menjalankan Swagger UI secara mandiri dari direktori ini:

```bash
cd backend
npm run docs
# Atau menggunakan Python bawaan:
npm run docs:python
```
Buka browser ke `http://localhost:8080` untuk melihat antarmuka Swagger UI. Anda juga dapat langsung membuka file `backend/docs/index.html` menggunakan browser apapun.
