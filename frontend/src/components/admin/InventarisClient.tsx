'use client';

import React, { useState, useTransition } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import Image from 'next/image';
import {
  Menu,
  Package,
  Search,
  Filter,
  ArrowUpDown,
  Plus,
  Edit2,
  Trash2,
  X,
  Check,
  AlertTriangle,
  Image as ImageIcon,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { formatRupiah } from '@/lib/utils';
import { Kategori, Produk } from '@/types';
import {
  createProductAction,
  updateProductAction,
  deleteProductAction,
} from '@/app/actions/shop';

interface InventarisClientProps {
  initialProducts: Produk[];
  categories: Kategori[];
  currentUser: any;
  pendingCardCount: number;
}

export function InventarisClient({
  initialProducts,
  categories,
  currentUser,
  pendingCardCount,
}: InventarisClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
  const [categoryFilter, setCategoryFilter] = useState(searchParams.get('kategori') || '');
  const [sortFilter, setSortFilter] = useState(searchParams.get('sort') || 'terbaru');
  const [isPending, startTransition] = useTransition();

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [currentId, setCurrentId] = useState<number | null>(null);
  const [formData, setFormData] = useState({
    nama_produk: '',
    id_kategori: categories[0]?.id_kategori || 1,
    harga_produk: 0,
    stok: 0,
    deskripsi_produk: '',
    gambar: '',
  });
  const [formError, setFormError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleFilterChange = (newCat?: string, newSort?: string, newSearch?: string) => {
    const params = new URLSearchParams();
    const c = newCat !== undefined ? newCat : categoryFilter;
    const so = newSort !== undefined ? newSort : sortFilter;
    const q = newSearch !== undefined ? newSearch : searchTerm;

    if (c) params.set('kategori', c);
    if (so) params.set('sort', so);
    if (q) params.set('search', q);

    startTransition(() => {
      router.push(`/admin/inventaris?${params.toString()}`);
    });
  };

  const openCreateModal = () => {
    setModalMode('create');
    setCurrentId(null);
    setFormData({
      nama_produk: '',
      id_kategori: categories[0]?.id_kategori || 1,
      harga_produk: 0,
      stok: 0,
      deskripsi_produk: '',
      gambar: '',
    });
    setFormError('');
    setIsModalOpen(true);
  };

  const openEditModal = (p: Produk) => {
    setModalMode('edit');
    setCurrentId(p.id_produk);
    setFormData({
      nama_produk: p.nama_produk,
      id_kategori: p.id_kategori,
      harga_produk: Number(p.harga_produk || p.harga || 0),
      stok: Number(p.stok || 0),
      deskripsi_produk: p.deskripsi_produk || p.deskripsi || '',
      gambar: p.gambar || '',
    });
    setFormError('');
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError('');
    setIsSubmitting(true);

    if (!formData.nama_produk || formData.harga_produk < 0 || formData.stok < 0) {
      setFormError('Pastikan nama produk, harga, dan stok valid.');
      setIsSubmitting(false);
      return;
    }

    try {
      if (modalMode === 'create') {
        const res = await createProductAction(formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal menambahkan produk.');
          setIsSubmitting(false);
          return;
        }
      } else if (currentId) {
        const res = await updateProductAction(currentId, formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal mengubah data produk.');
          setIsSubmitting(false);
          return;
        }
      }

      setIsModalOpen(false);
      startTransition(() => {
        router.refresh();
      });
    } catch (err: any) {
      setFormError(err.message || 'Terjadi kesalahan sistem.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (id: number, nama: string) => {
    if (!confirm(`Apakah Anda yakin ingin menghapus produk "${nama}"?`)) return;
    const res = await deleteProductAction(id);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal menghapus produk.');
    }
  };

  return (
    <div className="bg-gradient-to-br from-blue-500 to-teal-400 h-screen flex overflow-hidden font-sans select-none">
      <AdminSidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        currentUser={currentUser}
        pendingCardCount={pendingCardCount}
      />

      <main className="flex-1 flex flex-col h-screen overflow-hidden relative">
        {/* Mobile Header */}
        <header className="md:hidden h-16 flex items-center justify-between px-6 shrink-0 z-30">
          <span className="font-extrabold text-white text-lg drop-shadow-md">
            Inventaris Produk
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Scrollable Body */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Page Header */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Manajemen Inventaris
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Kelola katalog produk, stok etalase, dan penetapan harga toko.
                </p>
              </div>
              <button
                type="button"
                onClick={openCreateModal}
                className="bg-white text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-2xl font-bold shadow-lg transition flex items-center gap-2 cursor-pointer group"
              >
                <Plus className="w-5 h-5 group-hover:scale-110 transition-transform" />
                <span>Tambah Produk</span>
              </button>
            </div>

            {/* Filter Controls (Glassmorphism) */}
            <div className="flex flex-col md:flex-row gap-4">
              {/* Search Bar */}
              <div className="relative flex-1">
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && handleFilterChange(undefined, undefined, searchTerm)}
                  placeholder="Cari Nama Produk..."
                  className="w-full p-3.5 pl-12 pr-10 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40 font-medium"
                />
                <Search className="absolute left-4 top-3.5 w-5 h-5 text-blue-500" />
                {searchTerm && (
                  <button
                    type="button"
                    onClick={() => {
                      setSearchTerm('');
                      handleFilterChange(undefined, undefined, '');
                    }}
                    className="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition cursor-pointer"
                  >
                    <X className="w-5 h-5" />
                  </button>
                )}
              </div>

              {/* Category Filter */}
              <div className="relative min-w-[180px]">
                <select
                  value={categoryFilter}
                  onChange={(e) => {
                    setCategoryFilter(e.target.value);
                    handleFilterChange(e.target.value, undefined, undefined);
                  }}
                  className="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer font-bold appearance-none"
                >
                  <option value="">Semua Kategori</option>
                  {categories.map((c) => (
                    <option key={c.id_kategori} value={c.id_kategori}>
                      {c.nama_kategori}
                    </option>
                  ))}
                </select>
                <Filter className="absolute left-3.5 top-3.5 w-5 h-5 text-blue-500 pointer-events-none" />
              </div>

              {/* Sort Filter */}
              <div className="relative min-w-[180px]">
                <select
                  value={sortFilter}
                  onChange={(e) => {
                    setSortFilter(e.target.value);
                    handleFilterChange(undefined, e.target.value, undefined);
                  }}
                  className="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer font-bold appearance-none"
                >
                  <option value="terbaru">Terbaru Ditambahkan</option>
                  <option value="nama_asc">Nama (A-Z)</option>
                  <option value="nama_desc">Nama (Z-A)</option>
                  <option value="stok_sedikit">Stok Paling Sedikit</option>
                  <option value="stok_banyak">Stok Paling Banyak</option>
                  <option value="termurah">Harga Terendah</option>
                  <option value="termahal">Harga Tertinggi</option>
                </select>
                <ArrowUpDown className="absolute left-3.5 top-3.5 w-5 h-5 text-blue-500 pointer-events-none" />
              </div>
            </div>

            {/* Inventory Card Table */}
            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[550px] relative border border-white/40">
              <div className="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                  <h2 className="text-gray-800 font-extrabold text-2xl tracking-tight">
                    Katalog Inventaris
                  </h2>
                  <p className="text-gray-400 text-sm mt-1">
                    Daftar seluruh item barang yang terdaftar di etalase Épicerie.
                  </p>
                </div>
                <span className="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-xs border border-blue-100">
                  Total: {initialProducts.length} Produk
                </span>
              </div>

              {/* Desktop Header */}
              <div className="hidden md:flex px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
                <div className="w-20">Foto</div>
                <div className="flex-1">Nama Produk</div>
                <div className="w-36">Kategori</div>
                <div className="w-32">Harga</div>
                <div className="w-28 text-center">Stok</div>
                <div className="w-28 text-right">Aksi</div>
              </div>

              {/* Product List */}
              {initialProducts.length === 0 ? (
                <div className="py-24 text-center">
                  <div className="w-20 h-20 bg-blue-50 text-blue-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-sm">
                    <Package className="w-10 h-10" />
                  </div>
                  <h3 className="font-bold text-gray-700 text-lg">Tidak ada produk ditemukan</h3>
                  <p className="text-gray-400 text-sm max-w-sm mx-auto mt-1">
                    Silakan tambahkan produk baru atau ubah kata kunci pencarian Anda.
                  </p>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  {initialProducts.map((p) => {
                    const price = Number(p.harga_produk || p.harga || 0);
                    const stock = Number(p.stok || 0);
                    const isLowStock = stock <= 10;

                    return (
                      <div
                        key={p.id_produk}
                        className="group flex flex-col md:flex-row md:items-center p-3 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/20 transition-all duration-200 relative overflow-hidden"
                      >
                        <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />

                        {/* Image */}
                        <div className="flex items-center w-full md:w-20 mb-3 md:mb-0">
                          <div className="relative w-14 h-14 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 shrink-0">
                            {p.gambar ? (
                              <img
                                src={p.gambar}
                                alt={p.nama_produk}
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            ) : (
                              <div className="w-full h-full flex items-center justify-center text-gray-300">
                                <ImageIcon className="w-6 h-6" />
                              </div>
                            )}
                          </div>
                          <div className="md:hidden ml-3">
                            <h3 className="font-bold text-gray-800 text-sm">{p.nama_produk}</h3>
                            <p className="text-xs text-blue-600 font-bold">{formatRupiah(price)}</p>
                          </div>
                        </div>

                        {/* Name & Desc */}
                        <div className="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                          <h3 className="hidden md:block font-bold text-gray-800 text-sm truncate">
                            {p.nama_produk}
                          </h3>
                          <p className="text-xs text-gray-400 truncate">
                            {p.deskripsi_produk || p.deskripsi || 'Tidak ada deskripsi.'}
                          </p>
                        </div>

                        {/* Category */}
                        <div className="w-full md:w-36 mb-2 md:mb-0">
                          <span className="text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-lg border border-gray-200 inline-block">
                            {p.nama_kategori || 'Umum'}
                          </span>
                        </div>

                        {/* Price */}
                        <div className="w-full md:w-32 flex items-center mb-2 md:mb-0">
                          <span className="font-extrabold text-blue-600 text-sm">
                            {formatRupiah(price)}
                          </span>
                        </div>

                        {/* Stock */}
                        <div className="w-full md:w-28 flex md:justify-center items-center mb-2 md:mb-0">
                          <span
                            className={`text-xs font-bold px-3 py-1 rounded-full border shadow-xs ${
                              isLowStock
                                ? 'bg-red-50 text-red-600 border-red-200 animate-pulse'
                                : 'bg-green-50 text-green-700 border-green-200'
                            }`}
                          >
                            {stock} unit
                          </span>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center justify-end w-full md:w-28 gap-2">
                          <button
                            type="button"
                            onClick={() => openEditModal(p)}
                            className="bg-white text-gray-600 hover:text-blue-600 p-2 rounded-xl border border-gray-200 hover:border-blue-300 shadow-xs transition cursor-pointer"
                            title="Edit Produk"
                          >
                            <Edit2 className="w-4 h-4" />
                          </button>
                          <button
                            type="button"
                            onClick={() => handleDelete(p.id_produk, p.nama_produk)}
                            className="bg-white text-gray-600 hover:text-red-600 p-2 rounded-xl border border-gray-200 hover:border-red-300 shadow-xs transition cursor-pointer"
                            title="Hapus Produk"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      </main>

      {/* CREATE / EDIT MODAL */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
              <h3 className="font-extrabold text-gray-800 text-lg">
                {modalMode === 'create' ? 'Tambah Produk Baru' : 'Edit Data Produk'}
              </h3>
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              {formError && (
                <div className="bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl text-xs font-semibold flex items-center gap-2">
                  <AlertTriangle className="w-4 h-4 shrink-0" />
                  <span>{formError}</span>
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Nama Produk *
                </label>
                <input
                  type="text"
                  required
                  value={formData.nama_produk}
                  onChange={(e) => setFormData({ ...formData, nama_produk: e.target.value })}
                  placeholder="Contoh: Apel Fuji Segar"
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Kategori *
                  </label>
                  <select
                    value={formData.id_kategori}
                    onChange={(e) => setFormData({ ...formData, id_kategori: Number(e.target.value) })}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold bg-white"
                  >
                    {categories.map((c) => (
                      <option key={c.id_kategori} value={c.id_kategori}>
                        {c.nama_kategori}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Stok Barang *
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={formData.stok}
                    onChange={(e) => setFormData({ ...formData, stok: Number(e.target.value) })}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Harga Satuan (Rp) *
                </label>
                <input
                  type="number"
                  min="0"
                  step="100"
                  required
                  value={formData.harga_produk}
                  onChange={(e) => setFormData({ ...formData, harga_produk: Number(e.target.value) })}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-blue-600"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  URL Foto Produk
                </label>
                <input
                  type="url"
                  value={formData.gambar}
                  onChange={(e) => setFormData({ ...formData, gambar: e.target.value })}
                  placeholder="https://images.unsplash.com/..."
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Deskripsi Produk
                </label>
                <textarea
                  rows={3}
                  value={formData.deskripsi_produk}
                  onChange={(e) => setFormData({ ...formData, deskripsi_produk: e.target.value })}
                  placeholder="Keterangan isi kemasan, berat, kualitas..."
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
              </div>

              <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-gray-100 font-bold text-sm transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md transition disabled:opacity-50 cursor-pointer"
                >
                  {isSubmitting ? 'Menyimpan...' : 'Simpan Produk'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
