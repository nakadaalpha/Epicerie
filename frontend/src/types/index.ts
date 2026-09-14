// ==========================================
// PETA TIPE DATA RESMI ÉPICERIE (100% SESUAI LEGACY)
// ==========================================

export interface Kategori {
  id_kategori: number;
  nama_kategori: string;
  gambar?: string | null;
  created_at?: string;
  updated_at?: string;
  produk_count?: number;
}

export interface Produk {
  id_produk: number;
  id_kategori: number;
  nama_produk: string;
  harga_produk: number;
  stok: number;
  deskripsi_produk?: string | null;
  gambar?: string | null;
  created_at?: string;
  updated_at?: string;
  // Computed & Join Aliases
  harga: number; // alias untuk kemudahan UI
  deskripsi?: string | null; // alias
  nama_kategori?: string;
  total_terjual?: number;
  persen_diskon?: number;
  harga_final?: number;
}

export interface DetailTransaksi {
  id_detail_transaksi?: number;
  id_transaksi?: number;
  id_produk: number;
  jumlah: number;
  harga_produk_saat_beli: number;
  created_at?: string;
  updated_at?: string;
  // UI Helpers
  nama_produk?: string;
  gambar?: string | null;
  subtotal?: number;
  harga_satuan?: number;
}

export interface Transaksi {
  id_transaksi: number;
  kode_transaksi: string;
  id_user_pembeli?: number | null;
  id_karyawan?: number | null;
  id_alamat?: number | null;
  total_bayar: number;
  status: 'pending' | 'diproses' | 'dikemas' | 'dikirim' | 'selesai' | 'batal';
  nama_pelanggan_hold?: string | null;
  nama_pelanggan?: string | null;
  metode_pembayaran?: string | null;
  tanggal_transaksi?: string;
  ongkir?: number;
  ongkos_kirim?: number;
  bukti_bayar?: string | null;
  snap_token?: string | null;
  resi?: string | null;
  kurir_lat?: number | null;
  kurir_long?: number | null;
  created_at?: string;
  updated_at?: string;
  items?: DetailTransaksi[];
  detail_transaksi?: DetailTransaksi[];
}

export interface User {
  id_user: number;
  nama: string;
  username: string;
  password?: string | null;
  email?: string | null;
  email_verified_at?: string | null;
  no_hp?: string | null;
  no_hp_verified_at?: string | null;
  foto_profil?: string | null;
  status_cetak_kartu?: 'pending' | 'completed' | null;
  pin_keamanan?: string | null;
  role: 'Pemilik' | 'Karyawan' | 'Pelanggan' | 'kiosk' | 'admin' | string;
  google_id?: string | null;
  created_at?: string;
  updated_at?: string;
  // Computed Membership
  membership?: 'Gold' | 'Silver' | 'Bronze' | 'Classic' | string;
  discountPercent?: number;
  badgeColor?: string;
  totalBelanja?: number;
  totalOrder?: number;
}

export interface AlamatPengiriman {
  id_alamat: number;
  id_user: number;
  label: string; // 'Rumah', 'Kantor', dll
  penerima: string;
  no_hp_penerima: string;
  detail_alamat: string;
  plus_code?: string | null;
  is_primary?: boolean | number;
  is_utama?: boolean | number;
  created_at?: string;
  updated_at?: string;
}

export interface Ulasan {
  id_ulasan: number;
  id_user: number;
  id_produk: number;
  rating: number; // 1 s/d 5
  komentar: string;
  created_at?: string;
  updated_at?: string;
  // Join user info
  nama_user?: string;
  foto_profil?: string | null;
}

export interface Keranjang {
  id_keranjang: number;
  id_user: number;
  id_produk: number;
  jumlah: number;
  created_at?: string;
  updated_at?: string;
  produk?: Produk;
}

export interface Slider {
  id_slider: number;
  judul?: string | null;
  deskripsi?: string | null;
  gambar: string;
  link?: string | null;
  urutan: number;
  is_active: number | boolean;
  created_at?: string;
  updated_at?: string;
}

export interface Wishlist {
  id: number;
  id_user: number;
  id_produk: number;
  created_at?: string;
  updated_at?: string;
  produk?: Produk;
}

export interface LogAktivitas {
  id_log: number;
  id_user: number;
  waktu_aktivitas: string;
  jenis_aktivitas: string;
}

export interface CartItem {
  produk: Produk;
  jumlah: number;
}
