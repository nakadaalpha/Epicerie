import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { getOrderByIdAction } from '@/app/actions/shop';
import { TrackingClient } from '@/components/tracking/TrackingClient';

export const dynamic = 'force-dynamic';

interface TrackingPageProps {
  params: Promise<{ id: string }>;
}

export default async function TrackingPage({ params }: TrackingPageProps) {
  const session = await getSession();
  const { id } = await params;

  if (!session) {
    redirect(`/login?callbackUrl=/tracking/${id}`);
  }

  const orderId = Number(id);
  if (!orderId || isNaN(orderId)) {
    redirect('/riwayat');
  }

  const order = await getOrderByIdAction(orderId);
  if (!order) {
    redirect('/riwayat');
  }

  return <TrackingClient initialOrder={order} />;
}
