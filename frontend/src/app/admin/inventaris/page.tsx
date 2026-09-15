import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { hasPermission, isStaff } from '@/lib/permissions';
import { getProducts, getCategories, getDashboardStats } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { InventarisClient } from '@/components/admin/InventarisClient';

export const dynamic = 'force-dynamic';

interface PageProps {
  searchParams: Promise<{
    kategori?: string;
    search?: string;
    sort?: string;
  }>;
}

export default async function AdminInventarisPage({ searchParams }: PageProps) {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/admin/inventaris');
  }

  if (!isStaff(session.role) && !hasPermission(session.role, 'inventory:*')) {
    redirect('/');
  }

  const resolvedParams = await searchParams;
  const categoryId = resolvedParams.kategori ? Number(resolvedParams.kategori) : undefined;

  const [products, categories, stats, currentUser] = await Promise.all([
    getProducts(categoryId, resolvedParams.search),
    getCategories(),
    getDashboardStats(),
    getCurrentUser(),
  ]);

  return (
    <InventarisClient
      initialProducts={products}
      categories={categories}
      currentUser={currentUser}
      pendingCardCount={stats.pendingCardCount}
    />
  );
}
