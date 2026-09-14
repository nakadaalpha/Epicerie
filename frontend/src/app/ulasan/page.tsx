import React from 'react';
import { redirect } from 'next/navigation';
import { Navbar } from '@/components/Navbar';
import { getCurrentUser } from '@/app/actions/auth';
import {
  getPendingReviewsAction,
  getUserReviewsAction,
} from '@/app/actions/review';
import { UlasanClient } from './UlasanClient';

export const dynamic = 'force-dynamic';

export default async function UlasanPage() {
  const currentUser = await getCurrentUser();

  if (!currentUser) {
    redirect('/login');
  }

  const [pendingReviews, userReviews] = await Promise.all([
    getPendingReviewsAction(),
    getUserReviewsAction(),
  ]);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Navbar currentUser={currentUser} />
      <UlasanClient
        initialPending={pendingReviews}
        initialHistory={userReviews}
        currentUser={currentUser}
      />
    </div>
  );
}
