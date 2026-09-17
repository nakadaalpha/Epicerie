import React from 'react';
import { redirect } from 'next/navigation';
import { getSession } from '@/lib/auth';
import { getCurrentUser } from '@/app/actions/auth';
import { getUserAddressesAction } from '@/app/actions/shop';
import { ProfileClient } from '@/components/profile/ProfileClient';

export const dynamic = 'force-dynamic';

export default async function ProfilePage() {
  const session = await getSession();
  if (!session) {
    redirect('/login?callbackUrl=/profile');
  }

  const [user, addresses] = await Promise.all([
    getCurrentUser(),
    getUserAddressesAction(),
  ]);

  return <ProfileClient user={user} addresses={addresses || []} />;
}
