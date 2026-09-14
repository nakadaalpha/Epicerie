import { getProducts, getCategories, getSliders } from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { HomeClient } from '@/components/HomeClient';

export const dynamic = 'force-dynamic';

export default async function HomePage() {
  const [products, categories, sliders, currentUser] = await Promise.all([
    getProducts(),
    getCategories(),
    getSliders(),
    getCurrentUser(),
  ]);

  return (
    <HomeClient
      initialProducts={products}
      categories={categories}
      sliders={sliders}
      currentUser={currentUser}
    />
  );
}
