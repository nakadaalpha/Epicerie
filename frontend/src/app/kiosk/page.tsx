import { getProducts, getCategories } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { KioskClient } from '@/components/KioskClient';

export const dynamic = 'force-dynamic';

export default async function KioskPage() {
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
