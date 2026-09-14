import React from 'react';
import { notFound } from 'next/navigation';
import { Navbar } from '@/components/Navbar';
import { getProductDetail } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { ProductDetailClient } from './ProductDetailClient';

export const dynamic = 'force-dynamic';

interface PageProps {
  params: Promise<{ id: string }>;
}

export default async function ProductDetailPage({ params }: PageProps) {
  const { id } = await params;
  const productId = Number(id);

  if (!productId || isNaN(productId)) {
    notFound();
  }

  const [product, currentUser] = await Promise.all([
    getProductDetail(productId),
    getCurrentUser(),
  ]);

  if (!product) {
    notFound();
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Navbar currentUser={currentUser} />
      <ProductDetailClient product={product} currentUser={currentUser} />
    </div>
  );
}
