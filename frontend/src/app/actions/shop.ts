'use server';

import { apiFetch } from '@/lib/api';
import { Produk, Kategori, Slider, Transaksi } from '@/types';
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
