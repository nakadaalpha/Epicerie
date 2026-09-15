import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getCategories, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { KategoriClient } from '@/components/admin/KategoriClient';

export const dynamic = 'force-dynamic';

export default async function AdminKategoriPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/kategori');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'categories:manage')) {
    redirect('/');
  }

  const [categories, stats, currentUser] = await Promise.all([
    getCategories(),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <KategoriClient
      categories={categories}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
