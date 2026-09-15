import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getCardQueueAction, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { CardClient } from '@/components/admin/CardClient';

export const dynamic = 'force-dynamic';

export default async function AdminCardPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/card');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'reports:daily')) {
    redirect('/');
  }

  const [queue, stats, currentUser] = await Promise.all([
    getCardQueueAction(),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <CardClient
      requests={queue}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
