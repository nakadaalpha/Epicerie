'use server';

import { apiFetch } from '@/lib/api';
import { revalidatePath } from 'next/cache';

export async function getCourierTasksAction(status?: string) {
  try {
    const url = status ? `/api/courier/tasks?status=${status}` : '/api/courier/tasks';
    const res = await apiFetch<any[]>(url);
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get courier tasks:', error);
    return [];
  }
}

export async function startDeliveryAction(transactionId: number) {
  try {
    const res = await apiFetch(`/api/courier/tasks/${transactionId}/start`, {
      method: 'POST',
    });
    revalidatePath('/kurir');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal memulai pengantaran.' };
  }
}

export async function completeDeliveryAction(transactionId: number) {
  try {
    const res = await apiFetch(`/api/courier/tasks/${transactionId}/complete`, {
      method: 'POST',
    });
    revalidatePath('/kurir');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menyelesaikan pengantaran.' };
  }
}

export async function updateCourierLocationAction(payload: {
  id_transaksi: number;
  lat: number;
  long: number;
}) {
  try {
    const res = await apiFetch('/api/courier/location', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal memperbarui lokasi.' };
  }
}
