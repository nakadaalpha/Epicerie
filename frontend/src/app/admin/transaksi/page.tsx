import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getAdminTransactionsAction, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { TransaksiClient } from '@/components/admin/TransaksiClient';

export const dynamic = 'force-dynamic';

interface PageProps {
  searchParams: Promise<{
    status?: string;
    search?: string;
    sort?: string;
  }>;
}

export default async function AdminTransaksiPage({ searchParams }: PageProps) {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/transaksi');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'orders:read_all')) {
    redirect('/');
  }

  const resolvedParams = await searchParams;
  const [transactions, stats, currentUser] = await Promise.all([
    getAdminTransactionsAction({
      status: resolvedParams.status,
      search: resolvedParams.search,
      sort: resolvedParams.sort,
    }),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <TransaksiClient
      initialTransactions={transactions}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
