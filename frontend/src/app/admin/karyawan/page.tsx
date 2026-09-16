import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { isStaff } from '@/lib/permissions';
import { getEmployeesAction } from '@/app/actions/employee';
import { getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { KaryawanClient } from '@/components/admin/KaryawanClient';

export const dynamic = 'force-dynamic';

export default async function AdminKaryawanPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/karyawan');
  }

  // Only manager, admin, or owner can manage staff
  const role = (session.role || '').toLowerCase();
  if (role !== 'pemilik' && role !== 'admin' && role !== 'manajer') {
    redirect('/admin');
  }

  const [employees, stats, currentUser] = await Promise.all([
    getEmployeesAction(),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <KaryawanClient
      initialEmployees={employees}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
