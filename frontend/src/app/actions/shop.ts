'use server';

import { apiFetch } from '@/lib/api';
import { Produk, Kategori, Slider, Transaksi, AlamatPengiriman } from '@/types';
import { revalidatePath } from 'next/cache';

export async function getCategories(): Promise<Kategori[]> {
  try {
    const res = await apiFetch<Kategori[]>('/api/categories');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get categories from Express API:', error);
    return [];
  }
}

export async function getSliders(): Promise<Slider[]> {
  try {
    const res = await apiFetch<Slider[]>('/api/sliders');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get sliders from Express API:', error);
    return [];
  }
}

export async function getProducts(categoryId?: number, search?: string): Promise<Produk[]> {
  try {
    const params = new URLSearchParams();
    if (categoryId && categoryId > 0) {
      params.append('category_id', String(categoryId));
    }
    if (search && search.trim().length > 0) {
      params.append('search', search.trim());
    }

    const queryStr = params.toString() ? `?${params.toString()}` : '';
    const res = await apiFetch<Produk[]>(`/api/products${queryStr}`);
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get products from Express API:', error);
    return [];
  }
}

export async function getProductDetail(id: number): Promise<any | null> {
  try {
    const res = await apiFetch<any>(`/api/products/${id}`);
    if (res.success && res.data) {
      return res.data;
    }
    return null;
  } catch (error) {
    console.error('Failed to get product detail from Express API:', error);
    return null;
  }
}

export async function createTransaction(data: {
  items: { id_produk: number; jumlah: number; harga: number }[];
  total_bayar: number;
  metode_pembayaran: string;
  nama_pelanggan_hold?: string;
  id_user_kasir?: number;
}) {
  try {
    const res = await apiFetch<any>('/api/transactions', {
      method: 'POST',
      body: JSON.stringify(data),
    });

    if (!res.success) {
      return { success: false, error: res.error || 'Gagal memproses transaksi.' };
    }

    revalidatePath('/');
    revalidatePath('/admin/kiosk');
    revalidatePath('/kiosk');
    revalidatePath('/admin');

    return {
      success: true,
      kodeTransaksi: res.data?.kode_transaksi,
      idTransaksi: res.data?.id_transaksi,
      tanggal: new Date().toISOString(),
      totalBayar: data.total_bayar,
    };
  } catch (error: any) {
    console.error('Failed to create transaction via Express API:', error);
    return { success: false, error: error.message || 'Gagal memproses transaksi.' };
  }
}

export async function getRecentTransactions(limit = 15): Promise<Transaksi[]> {
  try {
    const res = await apiFetch<Transaksi[]>(`/api/transactions?limit=${limit}`);
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get recent transactions from Express API:', error);
    return [];
  }
}

export interface DashboardData {
  omzetHariIni: number;
  totalTransaksiHariIni: number;
  totalProduk: number;
  totalUser: number;
  pendingCardCount: number;
  stokHampirHabis: {
    id_produk: number;
    nama_produk: string;
    stok: number;
    gambar?: string | null;
    harga?: number;
  }[];
  produkTerlaris: {
    id_produk: number;
    nama_produk: string;
    stok: number;
    gambar?: string | null;
    total_terjual: number;
  }[];
  chartLabels: string[];
  chartData: number[];
  transaksiTerbaru: {
    id_transaksi: number;
    kode_transaksi: string;
    total_bayar: number;
    status: string;
    created_at: string;
    metode_pembayaran?: string;
    nama_pelanggan_hold?: string;
    ongkos_kirim: number;
    nama_pembeli?: string;
    no_hp_pembeli?: string;
    penerima?: string;
    no_hp_penerima?: string;
    detail_alamat?: string;
    items: {
      id_detail_transaksi: number;
      id_produk: number;
      nama_produk: string;
      gambar?: string;
      jumlah: number;
      harga_produk_saat_beli: number;
    }[];
  }[];
  // Backward compatibility
  totalSales: number;
  totalTrx: number;
  totalProducts: number;
  lowStockCount: number;
  recentTransactions: any[];
  lowStockProducts: any[];
}

export async function getDashboardStats(): Promise<DashboardData> {
  const fallback: DashboardData = {
    omzetHariIni: 0,
    totalTransaksiHariIni: 0,
    totalProduk: 0,
    totalUser: 0,
    pendingCardCount: 0,
    stokHampirHabis: [],
    produkTerlaris: [],
    chartLabels: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
    chartData: [0, 0, 0, 0, 0, 0, 0],
    transaksiTerbaru: [],
    totalSales: 0,
    totalTrx: 0,
    totalProducts: 0,
    lowStockCount: 0,
    recentTransactions: [],
    lowStockProducts: [],
  };

  try {
    const res = await apiFetch<any>('/api/reports/dashboard');
    if (res.success && res.data) {
      return res.data;
    }
    return fallback;
  } catch (error) {
    console.error('Failed to get dashboard stats from Express API:', error);
    return fallback;
  }
}

export async function updateOrderStatusAction(idTransaksi: number, status: string) {
  try {
    const res = await apiFetch<any>(`/api/transactions/${idTransaksi}/status`, {
      method: 'PATCH',
      body: JSON.stringify({ status }),
    });

    if (!res.success) {
      return { success: false, error: res.error || 'Gagal mengubah status pesanan.' };
    }

    revalidatePath('/admin');
    revalidatePath('/admin/transaksi');
    return { success: true, message: res.message || 'Status pesanan berhasil diperbarui.' };
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal mengubah status pesanan.' };
  }
}

// 1. Transactions Action
export async function getAdminTransactionsAction(params: {
  status?: string;
  search?: string;
  sort?: string;
}) {
  try {
    const qs = new URLSearchParams();
    if (params.status) qs.append('status', params.status);
    if (params.search) qs.append('search', params.search);
    if (params.sort) qs.append('sort', params.sort);
    const queryStr = qs.toString() ? `?${qs.toString()}` : '';

    const res = await apiFetch<any[]>(`/api/transactions${queryStr}`);
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get admin transactions:', error);
    return [];
  }
}

// 2. Product Inventory Actions
export async function createProductAction(data: {
  id_kategori: number;
  nama_produk: string;
  harga_produk: number;
  stok: number;
  deskripsi_produk?: string;
  gambar?: string;
}) {
  try {
    const res = await apiFetch<any>('/api/products', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin');
      revalidatePath('/admin/inventaris');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function updateProductAction(
  id: number,
  data: {
    id_kategori?: number;
    nama_produk?: string;
    harga_produk?: number;
    stok?: number;
    deskripsi_produk?: string;
    gambar?: string;
  }
) {
  try {
    const res = await apiFetch<any>(`/api/products/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin');
      revalidatePath('/admin/inventaris');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function deleteProductAction(id: number) {
  try {
    const res = await apiFetch<any>(`/api/products/${id}`, {
      method: 'DELETE',
    });
    if (res.success) {
      revalidatePath('/admin');
      revalidatePath('/admin/inventaris');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

// 3. Category Actions
export async function createCategoryAction(data: { nama_kategori: string; gambar?: string }) {
  try {
    const res = await apiFetch<any>('/api/categories', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin/kategori');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function updateCategoryAction(
  id: number,
  data: { nama_kategori?: string; gambar?: string }
) {
  try {
    const res = await apiFetch<any>(`/api/categories/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin/kategori');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function deleteCategoryAction(id: number) {
  try {
    const res = await apiFetch<any>(`/api/categories/${id}`, {
      method: 'DELETE',
    });
    if (res.success) {
      revalidatePath('/admin/kategori');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

// 4. Slider Actions
export async function getAdminSlidersAction() {
  try {
    const res = await apiFetch<Slider[]>('/api/sliders?all=true');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    return [];
  }
}

export async function createSliderAction(data: {
  judul: string;
  deskripsi?: string;
  gambar: string;
  urutan?: number;
  is_active?: boolean;
}) {
  try {
    const res = await apiFetch<any>('/api/sliders', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin/slider');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function updateSliderAction(
  id: number,
  data: {
    judul?: string;
    deskripsi?: string;
    gambar?: string;
    urutan?: number;
    is_active?: boolean;
  }
) {
  try {
    const res = await apiFetch<any>(`/api/sliders/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    if (res.success) {
      revalidatePath('/admin/slider');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

export async function deleteSliderAction(id: number) {
  try {
    const res = await apiFetch<any>(`/api/sliders/${id}`, {
      method: 'DELETE',
    });
    if (res.success) {
      revalidatePath('/admin/slider');
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

// 5. Card Actions
export async function getCardQueueAction() {
  try {
    const res = await apiFetch<any[]>('/api/reports/card-queue');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    return [];
  }
}

export async function completeCardPrintAction(idUser: number) {
  try {
    const res = await apiFetch<any>(`/api/reports/card-queue/${idUser}/complete`, {
      method: 'POST',
    });
    if (res.success) {
      revalidatePath('/admin/card');
      revalidatePath('/admin');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message };
  }
}

// 6. Financial Report Actions
export async function getFinancialReportAction(range: string = 'hari_ini') {
  try {
    const res = await apiFetch<any>(`/api/reports/financial?range=${range}`);
    if (res.success && res.data) {
      return res.data;
    }
    return {
      totalOmzet: 0,
      totalTransaksi: 0,
      labelPeriode: 'Hari Ini',
      range: 'hari_ini',
      labels: [],
      chartData: [],
      dailyData: [],
    };
  } catch (error) {
    return {
      totalOmzet: 0,
      totalTransaksi: 0,
      labelPeriode: 'Hari Ini',
      range: 'hari_ini',
      labels: [],
      chartData: [],
      dailyData: [],
    };
  }
}

// 7. Address Management Actions
export async function getUserAddressesAction(): Promise<AlamatPengiriman[]> {
  try {
    const res = await apiFetch<AlamatPengiriman[]>('/api/addresses');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get user addresses:', error);
    return [];
  }
}

export async function createAddressAction(formData: {
  label: string;
  penerima: string;
  no_hp_penerima: string;
  detail_alamat: string;
  plus_code?: string;
  is_primary?: boolean;
}): Promise<{ success: boolean; data?: AlamatPengiriman; error?: string }> {
  try {
    const res = await apiFetch<AlamatPengiriman>('/api/addresses', {
      method: 'POST',
      body: JSON.stringify(formData),
    });
    revalidatePath('/');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menambahkan alamat.' };
  }
}

export async function updateAddressAction(
  id: number,
  formData: {
    label?: string;
    penerima?: string;
    no_hp_penerima?: string;
    detail_alamat?: string;
    plus_code?: string;
    is_primary?: boolean;
  }
): Promise<{ success: boolean; data?: AlamatPengiriman; error?: string }> {
  try {
    const res = await apiFetch<AlamatPengiriman>(`/api/addresses/${id}`, {
      method: 'PUT',
      body: JSON.stringify(formData),
    });
    revalidatePath('/');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal memperbarui alamat.' };
  }
}

export async function deleteAddressAction(id: number) {
  try {
    const res = await apiFetch(`/api/addresses/${id}`, {
      method: 'DELETE',
    });
    revalidatePath('/');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menghapus alamat.' };
  }
}

export async function setPrimaryAddressAction(id: number) {
  try {
    const res = await apiFetch(`/api/addresses/${id}/primary`, {
      method: 'POST',
    });
    revalidatePath('/');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal mengatur alamat utama.' };
  }
}

// 8. Midtrans Payment Gateway Action
export async function getMidtransSnapTokenAction(payload: {
  total_bayar: number;
  order_id?: string;
  customer_details?: {
    nama?: string;
    email?: string;
    no_hp?: string;
  };
}): Promise<{
  success: boolean;
  data?: { token: string; redirect_url: string; order_id: string };
  error?: string;
}> {
  try {
    const res = await apiFetch<{ token: string; redirect_url: string; order_id: string }>(
      '/api/transactions/snap-token',
      {
        method: 'POST',
        body: JSON.stringify(payload),
      }
    );
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal membuat Snap token.' };
  }
}

// 9. Customer Order & Tracking Actions
export async function getMyOrdersAction(status?: string): Promise<Transaksi[]> {
  try {
    const url = status && status !== 'semua'
      ? `/api/transactions?status=${encodeURIComponent(status)}`
      : '/api/transactions';
    const res = await apiFetch<Transaksi[]>(url);
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get my orders:', error);
    return [];
  }
}

export async function getOrderByIdAction(id: number): Promise<Transaksi | null> {
  try {
    const res = await apiFetch<Transaksi>(`/api/transactions/${id}`);
    return res.success && res.data ? res.data : null;
  } catch (error) {
    console.error(`Failed to get order #${id}:`, error);
    return null;
  }
}

export async function completeMyOrderAction(id: number): Promise<{ success: boolean; error?: string }> {
  try {
    const res = await apiFetch<any>(`/api/transactions/${id}/complete`, {
      method: 'POST',
    });
    if (res.success) {
      revalidatePath('/riwayat');
      revalidatePath(`/tracking/${id}`);
      revalidatePath('/');
    }
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menyelesaikan pesanan.' };
  }
}

