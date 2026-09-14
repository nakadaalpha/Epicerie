// TypeScript definitions for Épicerie (11 Database Tables)

export interface User {
  id_user: number;
  nama: string;
  username: string;
  password?: string;
  email?: string | null;
  no_hp?: string | null;
  role: 'pemilik' | 'admin' | 'karyawan' | 'kasir' | 'kurir' | 'pelanggan';
  foto_profil?: string | null;
  status_cetak_kartu?: 'pending' | 'completed' | null;
  pin_keamanan?: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface Kategori {
  id_kategori: number;
  nama_kategori: string;
  gambar?: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface Produk {
  id_produk: number;
  id_kategori: number;
  nama_produk: string;
  harga_produk: number;
  harga?: number; // Alias
  stok: number;
  deskripsi_produk?: string | null;
  deskripsi?: string | null; // Alias
  gambar?: string | null;
  nama_kategori?: string;
  total_terjual?: number;
  created_at?: string;
  updated_at?: string;
}

export interface Slider {
  id_slider: number;
  judul: string;
  deskripsi?: string | null;
  gambar: string;
  link?: string | null;
  urutan: number;
  is_active: number;
  created_at?: string;
  updated_at?: string;
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
  ongkos_kirim?: number;
  ongkir?: number;
  bukti_bayar?: string | null;
  snap_token?: string | null;
  resi?: string | null;
  kurir_lat?: number | null;
  kurir_long?: number | null;
  created_at?: string;
  updated_at?: string;
  items?: DetailTransaksi[];
}

export interface DetailTransaksi {
  id_detail_transaksi: number;
  id_transaksi: number;
  id_produk: number;
  jumlah: number;
  harga_produk_saat_beli: number;
  nama_produk?: string;
  gambar?: string;
  created_at?: string;
  updated_at?: string;
}

export interface AlamatPengiriman {
  id_alamat: number;
  id_user: number;
  label: string;
  penerima: string;
  no_hp_penerima: string;
  detail_alamat: string;
  plus_code?: string | null;
  is_primary: number;
  created_at?: string;
  updated_at?: string;
}

export interface Ulasan {
  id_ulasan: number;
  id_transaksi: number;
  id_produk: number;
  id_user: number;
  rating: number;
  komentar?: string | null;
  foto_ulasan?: string | null;
  created_at?: string;
  updated_at?: string;
  nama_user?: string;
  foto_profil?: string;
}

export interface Wishlist {
  id_wishlist: number;
  id_user: number;
  id_produk: number;
  created_at?: string;
  updated_at?: string;
}

export interface LogAktivitas {
  id_log: number;
  id_user?: number | null;
  aktivitas: string;
  tipe: string;
  ip_address?: string | null;
  user_agent?: string | null;
  created_at?: string;
}
