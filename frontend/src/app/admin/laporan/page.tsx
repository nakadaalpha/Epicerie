import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getFinancialReportAction, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { LaporanClient } from '@/components/admin/LaporanClient';

export const dynamic = 'force-dynamic';

interface PageProps {
  searchParams: Promise<{
    range?: string;
  }>;
}

export default async function AdminLaporanPage({ searchParams }: PageProps) {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/laporan');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'reports:daily')) {
    redirect('/');
  }

  const resolvedParams = await searchParams;
  const [reportData, stats, currentUser] = await Promise.all([
    getFinancialReportAction(resolvedParams.range || 'hari_ini'),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <LaporanClient
      reportData={reportData}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
