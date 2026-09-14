import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { Produk, CartItem } from '@/types';

interface CartState {
  items: CartItem[];
  customerName: string;
  orderType: 'dine_in' | 'takeaway';
  
  // Actions
  addItem: (produk: Produk, qty?: number) => void;
  removeItem: (id_produk: number) => void;
  updateQuantity: (id_produk: number, jumlah: number) => void;
  clearCart: () => void;
  setCustomerName: (name: string) => void;
  setOrderType: (type: 'dine_in' | 'takeaway') => void;
  
  // Computed
  getTotalPrice: () => number;
  getTotalItems: () => number;
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      customerName: '',
      orderType: 'takeaway',

      addItem: (produk: Produk, qty = 1) => {
        const { items } = get();
        const existing = items.find((i) => i.produk.id_produk === produk.id_produk);

        if (existing) {
          const newQty = existing.jumlah + qty;
          if (newQty > produk.stok) return; // Prevent exceeding stock
          set({
            items: items.map((i) =>
              i.produk.id_produk === produk.id_produk
                ? { ...i, jumlah: newQty }
                : i
            ),
          });
        } else {
          if (qty > produk.stok) return;
          set({ items: [...items, { produk, jumlah: qty }] });
        }
      },

      removeItem: (id_produk: number) => {
        set({ items: get().items.filter((i) => i.produk.id_produk !== id_produk) });
      },

      updateQuantity: (id_produk: number, jumlah: number) => {
        if (jumlah <= 0) {
          get().removeItem(id_produk);
          return;
        }
        set({
          items: get().items.map((i) => {
            if (i.produk.id_produk === id_produk) {
              const safeQty = Math.min(jumlah, i.produk.stok);
              return { ...i, jumlah: safeQty };
            }
            return i;
          }),
        });
      },

      clearCart: () => {
        set({ items: [], customerName: '' });
      },

      setCustomerName: (name: string) => {
        set({ customerName: name });
      },

      setOrderType: (type: 'dine_in' | 'takeaway') => {
        set({ orderType: type });
      },

      getTotalPrice: () => {
        return get().items.reduce(
          (sum, item) => sum + item.produk.harga * item.jumlah,
          0
        );
      },

      getTotalItems: () => {
        return get().items.reduce((sum, item) => sum + item.jumlah, 0);
      },
    }),
    {
      name: 'epicerie_cart_storage',
    }
  )
);
