import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getAdminSlidersAction, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { SliderClient } from '@/components/admin/SliderClient';

export const dynamic = 'force-dynamic';

export default async function AdminSliderPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/slider');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'sliders:manage')) {
    redirect('/');
  }

  const [sliders, stats, currentUser] = await Promise.all([
    getAdminSlidersAction(),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <SliderClient
      sliders={sliders}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
