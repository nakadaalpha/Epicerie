import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission } from '@/lib/permissions';
import { getProducts, getCategories } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { KioskClient } from '@/components/KioskClient';

export const dynamic = 'force-dynamic';

export default async function AdminKioskPage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/kiosk');
  }

  if (!hasPermission(session.role, 'pos:access')) {
    redirect('/admin');
  }

  const [products, categories, currentUser] = await Promise.all([
    getProducts(),
    getCategories(),
    getCurrentUser(),
  ]);

  return (
    <KioskClient
      products={products}
      categories={categories}
      currentUser={currentUser}
    />
  );
}
