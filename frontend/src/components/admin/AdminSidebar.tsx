'use client';

import React, { useState, useRef, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
  Store,
  X,
  Plus,
  PieChart,
  Receipt,
  Package,
  Tags,
  Printer,
  Palette,
  Images,
  FileText,
  Tablet,
  LogOut,
  ChevronDown,
  User as UserIcon,
} from 'lucide-react';
import { logoutAction } from '@/app/actions/auth';

interface AdminSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser?: {
    nama: string;
    role: string;
    foto_profil?: string | null;
  } | null;
  pendingCardCount?: number;
}

export const AdminSidebar: React.FC<AdminSidebarProps> = ({
  isOpen,
  onClose,
  currentUser,
  pendingCardCount = 0,
}) => {
  const pathname = usePathname();
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const userMenuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (userMenuRef.current && !userMenuRef.current.contains(event.target as Node)) {
        setIsUserMenuOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  const isRouteActive = (route: string) => {
    if (route === '/admin') {
      return pathname === '/admin';
    }
    return pathname.startsWith(route);
  };

  return (
    <>
      <aside
        id="sidebar"
        className={`bg-white w-64 flex-shrink-0 flex flex-col transition-transform duration-300 transform fixed md:relative z-50 h-full shadow-[4px_0_24px_rgba(0,0,0,0.05)] border-r border-white/20 ${
          isOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        }`}
      >
        {/* LOGO */}
        <div className="h-20 flex items-center px-6 border-b border-gray-100 shrink-0">
          <Link
            href="/"
            className="text-2xl font-extrabold text-blue-600 tracking-widest flex items-center gap-2 select-none"
            style={{ fontFamily: "'Nunito', sans-serif" }}
          >
            <Store className="w-6 h-6 text-blue-600" />
            <span>ÈPICERIE</span>
          </Link>
          <button
            onClick={onClose}
            className="md:hidden ml-auto text-gray-400 hover:text-red-500 transition p-1"
            title="Tutup Menu"
          >
            <X className="w-6 h-6" />
          </button>
        </div>

        {/* NAVIGATION */}
        <nav className="flex-1 overflow-y-auto p-4 space-y-1.5 scrollbar-thin">
          {/* Tombol Aksi Utama: Pesanan Baru */}
          <Link
            href="/kiosk"
            className="flex items-center justify-center gap-2 w-full px-4 py-3 mb-6 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 rounded-xl shadow-lg shadow-blue-200 transition-all group transform hover:scale-[1.02]"
          >
            <Plus className="w-4 h-4 transition-transform group-hover:rotate-90" />
            <span>Pesanan Baru</span>
          </Link>

          {/* MENU UTAMA */}
          <p className="px-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2 mt-2">
            Menu Utama
          </p>

          <Link
            href="/admin"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              isRouteActive('/admin')
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <PieChart
              className={`w-5 h-5 text-center ${
                isRouteActive('/admin') ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Dashboard</span>
          </Link>

          <Link
            href="/admin/transaksi"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/transaksi'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Receipt
              className={`w-5 h-5 text-center ${
                pathname === '/admin/transaksi' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Transaksi</span>
          </Link>

          <Link
            href="/admin/inventaris"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/inventaris'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Package
              className={`w-5 h-5 text-center ${
                pathname === '/admin/inventaris' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Inventaris</span>
          </Link>

          <Link
            href="/admin/kategori"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/kategori'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Tags
              className={`w-5 h-5 text-center ${
                pathname === '/admin/kategori' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Kategori</span>
          </Link>

          {/* MEMBERSHIP */}
          <p className="px-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2 mt-4">
            Membership
          </p>

          <Link
            href="/admin/card"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/card'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Printer
              className={`w-5 h-5 text-center ${
                pathname === '/admin/card' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Antrian Cetak</span>
            {pendingCardCount > 0 && (
              <span className="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                {pendingCardCount}
              </span>
            )}
          </Link>

          <Link
            href="/admin/card/settings"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/card/settings'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Palette
              className={`w-5 h-5 text-center ${
                pathname === '/admin/card/settings' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Desain Kartu</span>
          </Link>

          {/* LAINNYA */}
          <p className="px-2 text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-2 mt-4">
            Lainnya
          </p>

          <Link
            href="/admin/slider"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/slider'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <Images
              className={`w-5 h-5 text-center ${
                pathname === '/admin/slider' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Slider</span>
          </Link>

          <Link
            href="/admin/laporan"
            className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all ${
              pathname === '/admin/laporan'
                ? 'bg-blue-50 text-blue-600 shadow-xs'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600'
            }`}
          >
            <FileText
              className={`w-5 h-5 text-center ${
                pathname === '/admin/laporan' ? 'text-blue-600' : 'text-gray-400'
              }`}
            />
            <span>Laporan</span>
          </Link>
        </nav>

        {/* FOOTER USER MENU */}
        <div ref={userMenuRef} className="border-t border-gray-100 p-4 shrink-0 relative bg-white z-50">
          <button
            type="button"
            onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
            className="flex items-center gap-3 w-full px-2 py-2 rounded-xl hover:bg-gray-50/70 hover:shadow-xs hover:border-gray-200 border border-transparent transition-all text-left group cursor-pointer"
          >
            {currentUser?.foto_profil ? (
              <img
                src={currentUser.foto_profil}
                alt={currentUser.nama}
                className="w-10 h-10 rounded-full object-cover border-2 border-white shadow-xs"
              />
            ) : (
              <div className="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border-2 border-white shadow-xs shrink-0">
                {currentUser?.nama ? currentUser.nama.charAt(0).toUpperCase() : 'A'}
              </div>
            )}

            <div className="flex-1 overflow-hidden min-w-0">
              <p className="text-sm font-bold text-gray-800 truncate">{currentUser?.nama || 'Admin'}</p>
              <p className="text-[10px] text-gray-500 font-bold uppercase tracking-wide truncate">
                {currentUser?.role || 'Admin'}
              </p>
            </div>
            <ChevronDown
              className={`w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-transform ${
                isUserMenuOpen ? 'rotate-180 text-blue-600' : ''
              }`}
            />
          </button>

          {/* User Popover Menu */}
          {isUserMenuOpen && (
            <div className="absolute bottom-full left-4 right-4 mb-2 bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden z-[60] transform transition-all duration-200 origin-bottom">
              <Link
                href="/"
                onClick={() => setIsUserMenuOpen(false)}
                className="flex items-center gap-3 px-5 py-3 text-sm font-bold text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition"
              >
                <Store className="w-5 h-5 text-gray-400" />
                <span>Lihat Toko</span>
              </Link>
              <Link
                href="/kiosk"
                onClick={() => setIsUserMenuOpen(false)}
                className="flex items-center gap-3 px-5 py-3 text-sm font-bold text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition"
              >
                <Tablet className="w-5 h-5 text-gray-400" />
                <span>Menu Kasir</span>
              </Link>
              <div className="border-t border-gray-50 my-0.5" />
              <form action={logoutAction}>
                <button
                  type="submit"
                  className="w-full text-left px-5 py-3 text-sm font-bold text-red-500 hover:bg-red-50 transition flex items-center gap-2 cursor-pointer"
                >
                  <LogOut className="w-4 h-4 mr-1 text-red-500" />
                  <span>Keluar</span>
                </button>
              </form>
            </div>
          )}
        </div>
      </aside>

      {/* OVERLAY FOR MOBILE */}
      {isOpen && (
        <div
          onClick={onClose}
          className="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 md:hidden transition-opacity"
        />
      )}
    </>
  );
};
