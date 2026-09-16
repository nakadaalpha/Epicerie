import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission } from '@/lib/permissions';
import { getCourierTasksAction } from '@/app/actions/courier';
import { getCurrentUser } from '@/app/actions/auth';
import { KurirDashboardClient } from '@/components/kurir/KurirDashboardClient';

export const dynamic = 'force-dynamic';

export default async function KurirPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/kurir');
  }

  // Allow couriers, staff, managers, owners
  if (
    !hasPermission(session.role, 'deliveries:read_assigned') &&
    !hasPermission(session.role, 'orders:read_all')
  ) {
    redirect('/');
  }

  const [tasks, currentUser] = await Promise.all([
    getCourierTasksAction(),
    getCurrentUser(),
  ]);

  return <KurirDashboardClient initialTasks={tasks} currentUser={currentUser} />;
}
