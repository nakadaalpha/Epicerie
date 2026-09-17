'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import {
  Layers,
  Search,
  ShoppingCart,
  QrCode,
  Store,
  Tablet,
  MapPin,
  ChevronDown,
  User,
  ShoppingBag,
  LogOut,
  Star,
  Home,
  Check,
} from 'lucide-react';
import { useCartStore } from '@/store/useCartStore';
import { logoutAction } from '@/app/actions/auth';
import { CategoryModal } from './CategoryModal';
import { AddressModal } from './AddressModal';
import { MemberCardModal } from './MemberCardModal';
import { Kategori } from '@/types';
import { hasPermission, isStaff, Permission } from '@/lib/permissions';

interface NavbarProps {
  currentUser?: {
    id_user: number;
    nama: string;
    username: string;
    role: string;
    permissions?: Permission[];
    membership?: string;
    discountPercent?: number;
    badgeColor?: string;
    foto_profil?: string | null;
  } | null;
  categories?: Kategori[];
  onOpenMobileCart?: () => void;
  searchQuery?: string;
  onSearchChange?: (val: string) => void;
  onSelectCategory?: (id: number) => void;
}

export const Navbar: React.FC<NavbarProps> = ({
  currentUser,
  categories = [],
  onOpenMobileCart,
  searchQuery,
  onSearchChange,
  onSelectCategory,
}) => {
  const pathname = usePathname();
  const router = useRouter();
  const totalItems = useCartStore((state) => state.getTotalItems());

  const [isCategoryOpen, setIsCategoryOpen] = useState(false);
  const [isAddressOpen, setIsAddressOpen] = useState(false);
  const [isMemberCardOpen, setIsMemberCardOpen] = useState(false);
  const [isMobileSearchOpen, setIsMobileSearchOpen] = useState(false);
  const [selectedAddressLabel, setSelectedAddressLabel] = useState<string>('Alamat Utama (Rumah)');

  const canAccessPos = hasPermission(currentUser, 'pos:access');
  const canAccessAdmin = isStaff(currentUser) || hasPermission(currentUser, 'reports:daily');

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery && searchQuery.trim().length > 0) {
      if (pathname !== '/') {
        router.push(`/?search=${encodeURIComponent(searchQuery.trim())}`);
      }
    }
  };

  return (
    <>
      <nav className="bg-white sticky top-0 w-full z-40 shadow-xs border-b border-gray-100 font-sans transition-all duration-300">
        {/* BARIS 1: LOGO, DESKTOP SEARCH, ICONS */}
        <div className="max-w-[1280px] mx-auto px-4 py-3 md:py-0 md:h-[70px] flex items-center justify-between gap-4">
          {/* A. LOGO & DESKTOP KATEGORI */}
          <div className="flex items-center gap-4 lg:gap-6 shrink-0">
            <Link
              href="/"
              className="text-2xl md:text-3xl font-extrabold text-blue-600 tracking-tight leading-none select-none"
              style={{ fontFamily: "'Nunito', sans-serif" }}
            >
              Épicerie
            </Link>

            {/* Kategori Desktop (Membuka Modal) */}
            <div
              className="hidden md:flex items-center h-10 cursor-pointer ml-1"
              onClick={() => setIsCategoryOpen(true)}
            >
              <div className="h-full flex items-center px-3.5 hover:bg-gray-50 transition gap-1.5 rounded-lg group">
                <Layers className="w-4 h-4 text-blue-600 group-hover:scale-110 transition-transform" />
                <span className="text-sm text-gray-600 font-semibold group-hover:text-blue-600 transition">
                  Kategori
                </span>
              </div>
            </div>
          </div>

          {/* B. DESKTOP SEARCH BAR */}
          <div className="flex-1 px-2 lg:px-5 hidden md:block">
            <form onSubmit={handleSearchSubmit} className="w-full">
              <div className="relative group w-full">
                <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                  <Search className="w-4 h-4 text-gray-400 group-focus-within:text-blue-600 transition" />
                </span>
                <input
                  type="text"
                  value={searchQuery || ''}
                  onChange={(e) => onSearchChange && onSearchChange(e.target.value)}
                  placeholder="Cari di Épicerie"
                  className="w-full bg-white text-sm text-gray-700 border border-gray-200 rounded-lg pl-10 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition shadow-xs placeholder:text-gray-400"
                />
              </div>
            </form>
          </div>

          {/* C. ICON GROUP & AUTH */}
          <div className="flex items-center gap-1.5 shrink-0">
            <div className="flex items-center gap-1 text-gray-500 pr-1 md:pr-3 md:border-r border-gray-200">
              {/* Menu Kasir POS (Requires pos:access permission) */}
              {canAccessPos && (
                <Link
                  href="/admin/kiosk"
                  className="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition"
                  title="Menu Kasir POS"
                >
                  <Tablet className="w-5 h-5 text-gray-600 hover:text-blue-600" />
                </Link>
              )}

              {/* Admin Dashboard (Requires staff / reports permission) */}
              {canAccessAdmin && (
                <Link
                  href="/admin"
                  className="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition hidden sm:flex"
                  title="Dasbor Admin"
                >
                  <Store className="w-5 h-5 text-gray-600 hover:text-blue-600" />
                </Link>
              )}

              {/* Tombol QR Member (Customer Login) */}
              {currentUser && (
                <button
                  type="button"
                  onClick={() => setIsMemberCardOpen(true)}
                  className="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group cursor-pointer"
                  title="QR Member Saya"
                >
                  <QrCode className="w-5 h-5 text-gray-600 group-hover:scale-110 transition-transform" />
                </button>
              )}

              {/* Tombol Keranjang (Desktop) */}
              <button
                type="button"
                onClick={() => {
                  if (onOpenMobileCart) {
                    onOpenMobileCart();
                  } else {
                    router.push('/kiosk');
                  }
                }}
                className="relative h-9 w-9 md:h-10 md:w-10 flex items-center justify-center rounded-lg hover:bg-gray-50 hover:text-blue-600 transition group cursor-pointer"
                title="Keranjang Belanja"
              >
                <ShoppingCart className="w-5 h-5 text-gray-600 group-hover:text-blue-600 transition" />
                {totalItems > 0 && (
                  <span className="absolute top-1 right-1 bg-red-600 text-white text-[10px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white shadow-xs">
                    {totalItems}
                  </span>
                )}
              </button>

              {/* Search Icon Toggle (Mobile) */}
              <button
                type="button"
                onClick={() => setIsMobileSearchOpen(!isMobileSearchOpen)}
                className="h-9 w-9 flex md:hidden items-center justify-center rounded-lg hover:bg-gray-50 text-gray-600 transition"
              >
                <Search className="w-5 h-5" />
              </button>
            </div>

            {/* D. PROFILE DROPDOWN / MASUK BUTTON */}
            <div className="pl-1 md:pl-2 relative group">
              {currentUser ? (
                <div className="relative">
                  <div className="flex items-center gap-2 cursor-pointer h-10 px-2 rounded-lg hover:bg-gray-50 transition">
                    <div className="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold border border-gray-200 shadow-xs">
                      {currentUser.nama ? currentUser.nama.charAt(0).toUpperCase() : 'U'}
                    </div>
                    <div className="hidden xl:block text-left leading-tight">
                      <div className="flex items-center gap-1.5">
                        <p className="text-xs font-bold text-gray-700 truncate max-w-[100px]">
                          {currentUser.nama}
                        </p>
                        {currentUser.membership && currentUser.membership !== 'Classic' && (
                          <span className="text-[9px] px-1.5 py-0.5 rounded border border-amber-300 bg-amber-50 text-amber-800 font-bold uppercase tracking-wider">
                            {currentUser.membership}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Dropdown Menu on Hover */}
                  <div className="hidden group-hover:block absolute top-full right-0 pt-2 w-60 z-50">
                    <div className="bg-white rounded-xl shadow-xl border border-gray-100 p-1.5 space-y-0.5">
                      <Link
                        href="/profile"
                        className="flex items-center w-full px-3 py-2 text-xs font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition"
                      >
                        <User className="w-4 h-4 mr-2.5 text-blue-600" />
                        <span>Profil Saya</span>
                      </Link>
                      <Link
                        href="/riwayat"
                        className="flex items-center w-full px-3 py-2 text-xs font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition"
                      >
                        <ShoppingBag className="w-4 h-4 mr-2.5 text-emerald-600" />
                        <span>Pesanan Saya</span>
                      </Link>
                      <Link
                        href="/ulasan"
                        className="flex items-center w-full px-3 py-2 text-xs font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition"
                      >
                        <Star className="w-4 h-4 mr-2.5 text-amber-500" />
                        <span>Ulasan Saya</span>
                      </Link>
                      <button
                        onClick={() => setIsMemberCardOpen(true)}
                        className="flex items-center w-full text-left px-3 py-2 text-xs font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition cursor-pointer"
                      >
                        <QrCode className="w-4 h-4 mr-2.5 text-blue-600" />
                        <span>Kartu Member Digital</span>
                      </button>
                      <div className="h-px bg-gray-100 my-1" />
                      <form action={logoutAction}>
                        <button
                          type="submit"
                          className="flex items-center w-full text-left px-3 py-2 text-xs font-bold text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer"
                        >
                          <LogOut className="w-4 h-4 mr-2.5 text-red-500" />
                          <span>Keluar</span>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              ) : (
                <Link
                  href="/login"
                  className="flex items-center gap-2 cursor-pointer h-10 px-5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs font-bold text-sm ml-1 select-none"
                >
                  Masuk
                </Link>
              )}
            </div>
          </div>
        </div>

        {/* BARIS 2: MOBILE SEARCH (Dropdown on toggle) */}
        {isMobileSearchOpen && (
          <div className="md:hidden px-4 pb-3">
            <form onSubmit={handleSearchSubmit} className="w-full">
              <div className="relative group w-full">
                <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search className="w-4 h-4 text-gray-400 group-focus-within:text-blue-600" />
                </span>
                <input
                  type="text"
                  value={searchQuery || ''}
                  onChange={(e) => onSearchChange && onSearchChange(e.target.value)}
                  className="w-full bg-gray-50 text-sm text-gray-700 border border-gray-200 rounded-lg pl-9 pr-4 h-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 transition placeholder:text-gray-400"
                  placeholder="Cari barang..."
                />
              </div>
            </form>
          </div>
        )}

        {/* BARIS 3: ADDRESS BAR */}
        <div className="w-full bg-white pb-2 border-b border-gray-50/50">
          <div className="max-w-[1280px] mx-auto px-4 flex justify-end relative">
            {currentUser ? (
              <button
                type="button"
                onClick={() => setIsAddressOpen(true)}
                className="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group focus:outline-none w-full md:w-auto justify-between md:justify-start"
              >
                <div className="flex items-center truncate">
                  <MapPin className="w-3.5 h-3.5 text-blue-600 mr-1.5 shrink-0" />
                  <span className="text-gray-500 mr-1">Dikirim ke</span>
                  <span className="font-bold text-gray-800 group-hover:text-blue-600 transition max-w-[180px] truncate">
                    {selectedAddressLabel}
                  </span>
                </div>
                <ChevronDown className="w-3 h-3 text-gray-400 ml-1.5 transition-transform duration-200 group-hover:rotate-180" />
              </button>
            ) : (
              <Link
                href="/login"
                className="flex items-center gap-1 text-xs cursor-pointer hover:bg-gray-50 px-3 py-1.5 rounded-md transition group w-full md:w-auto justify-between md:justify-start"
              >
                <div className="flex items-center">
                  <MapPin className="w-3.5 h-3.5 text-gray-400 mr-1.5" />
                  <span className="text-gray-500 mr-1">Alamat Pengiriman</span>
                  <span className="font-bold text-gray-800 group-hover:text-blue-600 transition">
                    Login untuk pilih
                  </span>
                </div>
              </Link>
            )}
          </div>
        </div>
      </nav>

      {/* BOTTOM NAVIGATION BAR (MOBILE ONLY) */}
      <div className="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 shadow-lg pb-safe pt-1">
        <div className="flex justify-around items-end h-[60px] pb-1 relative">
          <Link
            href="/"
            className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${
              pathname === '/' ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600'
            }`}
          >
            <Home className="w-5 h-5 mb-0.5" />
            <span className="text-[10px] font-bold">Beranda</span>
          </Link>

          <button
            type="button"
            onClick={() => setIsCategoryOpen(true)}
            className="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-400 hover:text-blue-600 cursor-pointer"
          >
            <Layers className="w-5 h-5 mb-0.5" />
            <span className="text-[10px] font-bold">Kategori</span>
          </button>

          <button
            type="button"
            onClick={() => {
              if (onOpenMobileCart) onOpenMobileCart();
              else router.push('/kiosk');
            }}
            className="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-400 hover:text-blue-600 relative cursor-pointer"
          >
            <div className="relative">
              <ShoppingCart className="w-5 h-5 mb-0.5" />
              {totalItems > 0 && (
                <span className="absolute -top-1.5 -right-2.5 bg-red-600 text-white text-[9px] font-bold px-1 min-w-[16px] h-[16px] flex items-center justify-center rounded-full border border-white">
                  {totalItems}
                </span>
              )}
            </div>
            <span className="text-[10px] font-bold">Keranjang</span>
          </button>

          {currentUser ? (
            <Link
              href="/ulasan"
              className={`flex flex-col items-center justify-center w-full h-full space-y-1 ${
                pathname === '/ulasan' ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600'
              }`}
            >
              <Star className="w-5 h-5 mb-0.5" />
              <span className="text-[10px] font-bold">Ulasan</span>
            </Link>
          ) : (
            <Link
              href="/login"
              className="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-400 hover:text-blue-600"
            >
              <User className="w-5 h-5 mb-0.5" />
              <span className="text-[10px] font-bold">Masuk</span>
            </Link>
          )}
        </div>
      </div>

      {/* POPUP MODALS */}
      <CategoryModal
        isOpen={isCategoryOpen}
        onClose={() => setIsCategoryOpen(false)}
        categories={categories}
        onSelectCategory={onSelectCategory}
      />

      <AddressModal
        isOpen={isAddressOpen}
        onClose={() => setIsAddressOpen(false)}
        currentUser={currentUser}
        onSelectAddress={(addr) => setSelectedAddressLabel(`${addr.label} (${addr.penerima})`)}
      />

      <MemberCardModal
        isOpen={isMemberCardOpen}
        onClose={() => setIsMemberCardOpen(false)}
        currentUser={currentUser}
      />
    </>
  );
};
