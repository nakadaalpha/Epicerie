'use server';

import { apiFetch } from '@/lib/api';
import { revalidatePath } from 'next/cache';

export interface ReviewItem {
  id_ulasan: number;
  id_produk: number;
  id_user: number;
  rating: number;
  komentar: string;
  created_at: string;
  nama_user?: string;
  foto_profil?: string | null;
  nama_produk?: string;
  gambar?: string | null;
  harga_produk?: number;
  nama_kategori?: string;
}

export interface ProductReviewsData {
  total: number;
  average: number;
  distribution: Record<number, number>;
  reviews: ReviewItem[];
}

export interface PendingReviewItem {
  id_produk: number;
  nama_produk: string;
  harga_produk: number;
  gambar?: string | null;
  nama_kategori?: string;
  tgl_beli: string;
  id_transaksi: number;
}

export async function getProductReviewsAction(productId: number): Promise<ProductReviewsData> {
  try {
    const res = await apiFetch<ProductReviewsData>(`/api/reviews/product/${productId}`);
    if (res.success && res.data) {
      return res.data;
    }
    return {
      total: 0,
      average: 0,
      distribution: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 },
      reviews: [],
    };
  } catch (error) {
    console.error('Failed to get product reviews:', error);
    return {
      total: 0,
      average: 0,
      distribution: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 },
      reviews: [],
    };
  }
}

export async function submitReviewAction(formData: {
  id_produk: number;
  rating: number;
  komentar: string;
}) {
  try {
    const res = await apiFetch<any>('/api/reviews', {
      method: 'POST',
      body: JSON.stringify(formData),
    });

    if (!res.success) {
      return { success: false, error: res.error || 'Gagal mengirim ulasan.' };
    }

    revalidatePath(`/produk/${formData.id_produk}`);
    revalidatePath('/ulasan');

    return { success: true, message: res.message || 'Ulasan berhasil diterbitkan!' };
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal memproses ulasan.' };
  }
}

export async function getPendingReviewsAction(): Promise<PendingReviewItem[]> {
  try {
    const res = await apiFetch<PendingReviewItem[]>('/api/reviews/pending');
    if (res.success && res.data) {
      return res.data;
    }
    return [];
  } catch (error) {
    console.error('Failed to get pending reviews:', error);
    return [];
  }
}

export async function getUserReviewsAction(): Promise<ReviewItem[]> {
  try {
    const res = await apiFetch<ReviewItem[]>('/api/reviews/history');
    if (res.success && res.data) {
      return res.data;
    }
    return [];
  } catch (error) {
    console.error('Failed to get user reviews:', error);
    return [];
  }
}
