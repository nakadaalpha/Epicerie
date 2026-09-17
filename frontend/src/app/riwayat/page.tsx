import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { getMyOrdersAction } from '@/app/actions/shop';
import { RiwayatClient } from '@/components/riwayat/RiwayatClient';

export const dynamic = 'force-dynamic';

interface RiwayatPageProps {
  searchParams: Promise<{ status?: string }>;
}

export default async function RiwayatPage({ searchParams }: RiwayatPageProps) {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/riwayat');
  }

  const { status } = await searchParams;
  const orders = await getMyOrdersAction(status);

  return <RiwayatClient initialOrders={orders} currentStatus={status || 'semua'} />;
}
