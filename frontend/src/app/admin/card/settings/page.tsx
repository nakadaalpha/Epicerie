import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { CardSettingsClient } from '@/components/admin/CardSettingsClient';

export const dynamic = 'force-dynamic';

export default async function AdminCardSettingsPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/card/settings');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'reports:daily')) {
    redirect('/');
  }

  const [stats, currentUser] = await Promise.all([
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <CardSettingsClient
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
