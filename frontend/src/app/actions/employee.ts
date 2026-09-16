'use server';

import { apiFetch } from '@/lib/api';
import { revalidatePath } from 'next/cache';

export async function getEmployeesAction() {
  try {
    const res = await apiFetch<any[]>('/api/employees');
    return res.success && res.data ? res.data : [];
  } catch (error) {
    console.error('Failed to get employees:', error);
    return [];
  }
}

export async function createEmployeeAction(formData: {
  nama: string;
  username: string;
  password: string;
  role: string;
  no_hp?: string;
}): Promise<{ success: boolean; data?: any; error?: string }> {
  try {
    const res = await apiFetch('/api/employees', {
      method: 'POST',
      body: JSON.stringify(formData),
    });
    revalidatePath('/admin/karyawan');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menambahkan karyawan.' };
  }
}

export async function updateEmployeeAction(
  id: number,
  formData: {
    nama?: string;
    username?: string;
    password?: string;
    role?: string;
    no_hp?: string;
  }
): Promise<{ success: boolean; data?: any; error?: string }> {
  try {
    const res = await apiFetch(`/api/employees/${id}`, {
      method: 'PUT',
      body: JSON.stringify(formData),
    });
    revalidatePath('/admin/karyawan');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal memperbarui karyawan.' };
  }
}

export async function deleteEmployeeAction(id: number) {
  try {
    const res = await apiFetch(`/api/employees/${id}`, {
      method: 'DELETE',
    });
    revalidatePath('/admin/karyawan');
    return res;
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal menghapus karyawan.' };
  }
}
