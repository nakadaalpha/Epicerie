'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Menu,
  Tags,
  Plus,
  Edit2,
  Trash2,
  X,
  AlertTriangle,
  FolderOpen,
  Image as ImageIcon,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { Kategori } from '@/types';
import {
  createCategoryAction,
  updateCategoryAction,
  deleteCategoryAction,
} from '@/app/actions/shop';

interface KategoriClientProps {
  categories: any[];
  currentUser: any;
  pendingCardCount: number;
}

export function KategoriClient({
  categories,
  currentUser,
  pendingCardCount,
}: KategoriClientProps) {
  const router = useRouter();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [isPending, startTransition] = useTransition();

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [currentId, setCurrentId] = useState<number | null>(null);
  const [formData, setFormData] = useState({
    nama_kategori: '',
    gambar: '',
  });
  const [formError, setFormError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const filteredCategories = categories.filter((c) =>
    c.nama_kategori.toLowerCase().includes(searchTerm.toLowerCase().trim())
  );

  const openCreateModal = () => {
    setModalMode('create');
    setCurrentId(null);
    setFormData({ nama_kategori: '', gambar: '' });
    setFormError('');
    setIsModalOpen(true);
  };

  const openEditModal = (cat: any) => {
    setModalMode('edit');
    setCurrentId(cat.id_kategori);
    setFormData({
      nama_kategori: cat.nama_kategori,
      gambar: cat.gambar || '',
    });
    setFormError('');
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError('');
    setIsSubmitting(true);

    if (!formData.nama_kategori.trim()) {
      setFormError('Nama kategori wajib diisi.');
      setIsSubmitting(false);
      return;
    }

    try {
      if (modalMode === 'create') {
        const res = await createCategoryAction(formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal menambahkan kategori.');
          setIsSubmitting(false);
          return;
        }
      } else if (currentId) {
        const res = await updateCategoryAction(currentId, formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal memperbarui kategori.');
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
    if (!confirm(`Apakah Anda yakin ingin menghapus kategori "${nama}"? Semua produk terkait mungkin terpengaruh.`)) {
      return;
    }
    const res = await deleteCategoryAction(id);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal menghapus kategori.');
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
            Kategori Produk
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
            {/* Page Title */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Manajemen Kategori
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Kelompokkan etalase produk agar pelanggan mudah menjelajah toko.
                </p>
              </div>
              <button
                type="button"
                onClick={openCreateModal}
                className="bg-white text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-2xl font-bold shadow-lg transition flex items-center gap-2 cursor-pointer group"
              >
                <Plus className="w-5 h-5 group-hover:scale-110 transition-transform" />
                <span>Tambah Kategori</span>
              </button>
            </div>

            {/* Main Content Table Card */}
            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[550px] relative border border-white/40">
              <div className="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                  <h2 className="text-gray-800 font-extrabold text-2xl tracking-tight">
                    Daftar Kategori
                  </h2>
                  <p className="text-gray-400 text-sm mt-1">
                    Atur nama dan gambar representatif setiap grup produk.
                  </p>
                </div>
                <span className="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-xs border border-blue-100">
                  Total: {categories.length} Kategori
                </span>
              </div>

              {/* Desktop Header */}
              <div className="hidden md:flex px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
                <div className="w-20">Ikon</div>
                <div className="flex-1">Nama Kategori</div>
                <div className="w-36 text-center">Jumlah Produk</div>
                <div className="w-28 text-right">Aksi</div>
              </div>

              {/* Category List */}
              {filteredCategories.length === 0 ? (
                <div className="py-24 text-center">
                  <div className="w-20 h-20 bg-blue-50 text-blue-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-sm">
                    <FolderOpen className="w-10 h-10" />
                  </div>
                  <h3 className="font-bold text-gray-700 text-lg">Belum ada kategori</h3>
                  <p className="text-gray-400 text-sm max-w-sm mx-auto mt-1">
                    Silakan klik tombol &quot;Tambah Kategori&quot; untuk menambahkan kelompok barang baru.
                  </p>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  {filteredCategories.map((c) => (
                    <div
                      key={c.id_kategori}
                      className="group flex flex-col md:flex-row md:items-center p-3.5 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/20 transition-all duration-200 relative overflow-hidden"
                    >
                      <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />

                      {/* Icon / Image */}
                      <div className="flex items-center w-full md:w-20 mb-3 md:mb-0">
                        <div className="relative w-12 h-12 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 shrink-0 flex items-center justify-center font-bold text-blue-600 shadow-xs">
                          {c.gambar ? (
                            <img
                              src={c.gambar}
                              alt={c.nama_kategori}
                              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                          ) : (
                            <span className="text-lg uppercase">
                              {c.nama_kategori.substring(0, 1)}
                            </span>
                          )}
                        </div>
                      </div>

                      {/* Name */}
                      <div className="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                        <h3 className="font-bold text-gray-800 text-base group-hover:text-blue-600 transition">
                          {c.nama_kategori}
                        </h3>
                        <p className="text-xs text-gray-400">ID Kategori: #{c.id_kategori}</p>
                      </div>

                      {/* Products Count */}
                      <div className="w-full md:w-36 flex md:justify-center items-center mb-2 md:mb-0">
                        <span className="text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                          {c.produk_count || 0} Produk
                        </span>
                      </div>

                      {/* Actions */}
                      <div className="flex items-center justify-end w-full md:w-28 gap-2">
                        <button
                          type="button"
                          onClick={() => openEditModal(c)}
                          className="bg-white text-gray-600 hover:text-blue-600 p-2 rounded-xl border border-gray-200 hover:border-blue-300 shadow-xs transition cursor-pointer"
                          title="Edit Kategori"
                        >
                          <Edit2 className="w-4 h-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleDelete(c.id_kategori, c.nama_kategori)}
                          className="bg-white text-gray-600 hover:text-red-600 p-2 rounded-xl border border-gray-200 hover:border-red-300 shadow-xs transition cursor-pointer"
                          title="Hapus Kategori"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </main>

      {/* CREATE / EDIT MODAL */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
              <h3 className="font-extrabold text-gray-800 text-lg">
                {modalMode === 'create' ? 'Tambah Kategori Baru' : 'Edit Kategori'}
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
                  Nama Kategori *
                </label>
                <input
                  type="text"
                  required
                  value={formData.nama_kategori}
                  onChange={(e) => setFormData({ ...formData, nama_kategori: e.target.value })}
                  placeholder="Contoh: Buah-buahan Segar"
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  URL Ikon / Gambar
                </label>
                <input
                  type="url"
                  value={formData.gambar}
                  onChange={(e) => setFormData({ ...formData, gambar: e.target.value })}
                  placeholder="https://..."
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
                  {isSubmitting ? 'Menyimpan...' : 'Simpan Kategori'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
