-- ====================================================================
-- SKEMA RESMI DATABASE POSTGRESQL / SUPABASE - ÉPICERIE
-- 100% SESUAI DENGAN SELURUH MODEL & MIGRASI PROYEK LEGACY
-- ====================================================================

-- 1. TABEL KATEGORI
CREATE TABLE IF NOT EXISTS public.kategori (
    id_kategori BIGSERIAL PRIMARY KEY,
    nama_kategori VARCHAR(255) NOT NULL,
    gambar VARCHAR(255),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABEL PRODUK
CREATE TABLE IF NOT EXISTS public.produk (
    id_produk BIGSERIAL PRIMARY KEY,
    id_kategori BIGINT NOT NULL REFERENCES public.kategori(id_kategori) ON DELETE CASCADE,
    nama_produk VARCHAR(255) NOT NULL,
    harga_produk DOUBLE PRECISION NOT NULL,
    stok INTEGER NOT NULL DEFAULT 0,
    deskripsi_produk TEXT,
    gambar VARCHAR(255),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABEL USER (MEMBER, KARYAWAN, PEMILIK)
CREATE TABLE IF NOT EXISTS public."user" (
    id_user BIGINT PRIMARY KEY, -- Format: DDMMYY + 6 random digits (12 digit angka)
    nama VARCHAR(255) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255), -- Bcrypt hash (nullable jika login via Google)
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP WITHOUT TIME ZONE,
    no_hp VARCHAR(30) UNIQUE,
    no_hp_verified_at TIMESTAMP WITHOUT TIME ZONE,
    foto_profil VARCHAR(255),
    status_cetak_kartu VARCHAR(50), -- 'pending', 'completed'
    pin_keamanan VARCHAR(10), -- 6 digit angka rahasia untuk reset password
    role VARCHAR(50) DEFAULT 'Pelanggan', -- 'Pemilik', 'Karyawan', 'Pelanggan', 'kiosk'
    google_id VARCHAR(255),
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABEL ALAMAT PENGIRIMAN
CREATE TABLE IF NOT EXISTS public.alamat_pengiriman (
    id_alamat BIGSERIAL PRIMARY KEY,
    id_user BIGINT NOT NULL REFERENCES public."user"(id_user) ON DELETE CASCADE,
    label VARCHAR(50) NOT NULL, -- 'Rumah', 'Kantor', dll
    penerima VARCHAR(100) NOT NULL,
    no_hp_penerima VARCHAR(20) NOT NULL,
    detail_alamat TEXT NOT NULL,
    plus_code VARCHAR(100), -- Google Plus Code
    is_primary SMALLINT DEFAULT 0,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 5. TABEL TRANSAKSI
CREATE TABLE IF NOT EXISTS public.transaksi (
    id_transaksi BIGSERIAL PRIMARY KEY,
    kode_transaksi VARCHAR(100) UNIQUE NOT NULL, -- e.g. TRX-20260912-001
    id_user_pembeli BIGINT REFERENCES public."user"(id_user) ON DELETE SET NULL,
    id_karyawan BIGINT REFERENCES public."user"(id_user) ON DELETE SET NULL, -- Kasir atau Kurir
    id_alamat BIGINT REFERENCES public.alamat_pengiriman(id_alamat) ON DELETE SET NULL,
    total_bayar DOUBLE PRECISION NOT NULL,
    status VARCHAR(50) DEFAULT 'selesai', -- 'pending', 'diproses', 'dikemas', 'dikirim', 'selesai', 'batal'
    nama_pelanggan_hold VARCHAR(255), -- Nama antrean hold di Kiosk
    nama_pelanggan VARCHAR(255), -- Nama pembeli umum (walk-in)
    metode_pembayaran VARCHAR(50), -- 'Tunai', 'Midtrans', 'QRIS', 'Transfer'
    tanggal_transaksi TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ongkir DOUBLE PRECISION DEFAULT 0, -- Ongkos kirim flat Rp 5.000 (Rp 0 untuk Gold)
    bukti_bayar VARCHAR(255),
    snap_token VARCHAR(255),
    resi VARCHAR(100),
    kurir_lat DOUBLE PRECISION, -- Koordinat GPS Latitude Kurir
    kurir_long DOUBLE PRECISION, -- Koordinat GPS Longitude Kurir
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 6. TABEL DETAIL TRANSAKSI
CREATE TABLE IF NOT EXISTS public.detail_transaksi (
    id_detail_transaksi BIGSERIAL PRIMARY KEY,
    id_transaksi BIGINT NOT NULL REFERENCES public.transaksi(id_transaksi) ON DELETE CASCADE,
    id_produk BIGINT NOT NULL REFERENCES public.produk(id_produk) ON DELETE CASCADE,
    jumlah INTEGER NOT NULL DEFAULT 1,
    harga_produk_saat_beli DOUBLE PRECISION NOT NULL, -- Harga final setelah diskon
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 7. TABEL ULASAN (REVIEW & RATING)
CREATE TABLE IF NOT EXISTS public.ulasan (
    id_ulasan BIGSERIAL PRIMARY KEY,
    id_user BIGINT NOT NULL REFERENCES public."user"(id_user) ON DELETE CASCADE,
    id_produk BIGINT NOT NULL REFERENCES public.produk(id_produk) ON DELETE CASCADE,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    komentar TEXT NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product_review UNIQUE (id_user, id_produk)
);

-- 8. TABEL KERANJANG BELANJA
CREATE TABLE IF NOT EXISTS public.keranjang (
    id_keranjang BIGSERIAL PRIMARY KEY,
    id_user BIGINT NOT NULL REFERENCES public."user"(id_user) ON DELETE CASCADE,
    id_produk BIGINT NOT NULL REFERENCES public.produk(id_produk) ON DELETE CASCADE,
    jumlah INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product_cart UNIQUE (id_user, id_produk)
);

-- 9. TABEL SLIDERS (BANNER PROMO)
CREATE TABLE IF NOT EXISTS public.sliders (
    id_slider BIGSERIAL PRIMARY KEY,
    judul VARCHAR(255),
    deskripsi VARCHAR(255),
    gambar VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    urutan INTEGER NOT NULL DEFAULT 1,
    is_active SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 10. TABEL WISHLISTS
CREATE TABLE IF NOT EXISTS public.wishlists (
    id BIGSERIAL PRIMARY KEY,
    id_user BIGINT NOT NULL REFERENCES public."user"(id_user) ON DELETE CASCADE,
    id_produk BIGINT NOT NULL REFERENCES public.produk(id_produk) ON DELETE CASCADE,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_user_product_wishlist UNIQUE (id_user, id_produk)
);

-- 11. TABEL LOG AKTIVITAS
CREATE TABLE IF NOT EXISTS public.log_aktivitas (
    id_log BIGSERIAL PRIMARY KEY,
    id_user BIGINT NOT NULL REFERENCES public."user"(id_user) ON DELETE CASCADE,
    waktu_aktivitas TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    jenis_aktivitas VARCHAR(255) NOT NULL
);
