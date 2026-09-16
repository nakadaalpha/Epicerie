'use server';

import { apiFetch } from '@/lib/api';
import {
  setSessionCookie,
  clearSessionCookie,
  getSession,
  SessionPayload,
} from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';

export async function loginAction(formData: {
  identifier: string;
  password: string;
}) {
  try {
    const res = await apiFetch<any>('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify(formData),
    });

    if (!res.success || !res.user) {
      return { success: false, error: res.error || 'Gagal memproses login.' };
    }

    const sessionPayload: SessionPayload = {
      id_user: Number(res.user.id_user),
      nama: res.user.nama,
      username: res.user.username,
      role: res.user.role,
      normalizedRole: res.user.normalizedRole,
      permissions: res.user.permissions,
      no_hp: res.user.no_hp,
      foto_profil: res.user.foto_profil,
    };

    await setSessionCookie(sessionPayload);

    // Determine redirect destination based on permissions
    let redirectTo = '/';
    if (hasPermission(res.user.role, 'reports:daily') || isStaff(res.user.role)) {
      redirectTo = '/admin';
    } else if (hasPermission(res.user.role, 'pos:access')) {
      redirectTo = '/admin/kiosk';
    } else if (hasPermission(res.user.role, 'deliveries:read_assigned')) {
      redirectTo = '/kurir';
    }

    revalidatePath('/');
    return { success: true, redirectTo, user: sessionPayload };
  } catch (error: any) {
    console.error('Login action error via Express API:', error);
    return { success: false, error: error.message || 'Gagal memproses login.' };
  }
}

export async function registerAction(formData: {
  nama: string;
  username: string;
  no_hp: string;
  pin_keamanan: string;
  password: string;
  password_confirmation: string;
}) {
  try {
    const res = await apiFetch<any>('/api/auth/register', {
      method: 'POST',
      body: JSON.stringify(formData),
    });

    if (!res.success || !res.user) {
      return { success: false, error: res.error || 'Gagal mendaftar akun baru.' };
    }

    const sessionPayload: SessionPayload = {
      id_user: Number(res.user.id_user),
      nama: res.user.nama,
      username: res.user.username,
      role: res.user.role || 'pelanggan',
      no_hp: res.user.no_hp,
      foto_profil: null,
    };

    await setSessionCookie(sessionPayload);
    revalidatePath('/');

    return { success: true, user: sessionPayload };
  } catch (error: any) {
    console.error('Register action error via Express API:', error);
    return { success: false, error: error.message || 'Gagal mendaftar akun baru.' };
  }
}

export async function logoutAction() {
  try {
    await apiFetch('/api/auth/logout', { method: 'POST' });
  } catch (err) {
    // ignore
  }
  await clearSessionCookie();
  revalidatePath('/');
  redirect('/login');
}

export async function getCurrentUser() {
  const session = await getSession();
  if (!session) return null;

  try {
    // If logged in, fetch live membership and profile details from Express API
    const res = await apiFetch<any>('/api/auth/me');
    if (res.success && res.user) {
      return res.user;
    }

    // Fallback to session
    return {
      id_user: session.id_user,
      nama: session.nama,
      username: session.username,
      role: session.role,
      no_hp: session.no_hp,
      foto_profil: session.foto_profil,
      membership: 'Classic',
      discountPercent: 0,
      badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
    };
  } catch (err) {
    return {
      id_user: session.id_user,
      nama: session.nama,
      username: session.username,
      role: session.role,
      no_hp: session.no_hp,
      foto_profil: session.foto_profil,
      membership: 'Classic',
      discountPercent: 0,
      badgeColor: 'bg-slate-100 text-slate-700 border-slate-200',
    };
  }
}

export async function verifyForgotPinAction(formData: {
  username: string;
  no_hp: string;
  pin_keamanan: string;
}): Promise<{ success: boolean; error?: string; id_user: number | null; nama: string }> {
  try {
    const res = await apiFetch<any>('/api/auth/verify-pin', {
      method: 'POST',
      body: JSON.stringify(formData),
    });

    if (!res.success || !res.id_user) {
      return {
        success: false,
        error: res.error || 'Verifikasi gagal.',
        id_user: null,
        nama: '',
      };
    }

    return {
      success: true,
      id_user: Number(res.id_user),
      nama: String(res.nama || ''),
    };
  } catch (error: any) {
    return {
      success: false,
      error: error.message || 'Gagal memverifikasi data.',
      id_user: null,
      nama: '',
    };
  }
}

export async function resetPasswordAction(formData: {
  id_user: number;
  password: string;
  password_confirmation: string;
}) {
  try {
    const res = await apiFetch<any>('/api/auth/reset-password', {
      method: 'POST',
      body: JSON.stringify(formData),
    });

    if (!res.success) {
      return { success: false, error: res.error || 'Gagal mengubah password.' };
    }

    return { success: true };
  } catch (error: any) {
    return { success: false, error: error.message || 'Gagal mengubah password.' };
  }
}

