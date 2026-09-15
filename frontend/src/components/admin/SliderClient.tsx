'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Menu,
  Images,
  Plus,
  Edit2,
  Trash2,
  X,
  AlertTriangle,
  CheckCircle,
  Eye,
  EyeOff,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { Slider } from '@/types';
import {
  createSliderAction,
  updateSliderAction,
  deleteSliderAction,
} from '@/app/actions/shop';

interface SliderClientProps {
  sliders: Slider[];
  currentUser: any;
  pendingCardCount: number;
}

export function SliderClient({
  sliders,
  currentUser,
  pendingCardCount,
}: SliderClientProps) {
  const router = useRouter();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [isPending, startTransition] = useTransition();

  // Modal state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [currentId, setCurrentId] = useState<number | null>(null);
  const [formData, setFormData] = useState({
    judul: '',
    deskripsi: '',
    gambar: '',
    urutan: 0,
    is_active: true,
  });
  const [formError, setFormError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const openCreateModal = () => {
    setModalMode('create');
    setCurrentId(null);
    setFormData({
      judul: '',
      deskripsi: '',
      gambar: '',
      urutan: sliders.length + 1,
      is_active: true,
    });
    setFormError('');
    setIsModalOpen(true);
  };

  const openEditModal = (slider: Slider) => {
    setModalMode('edit');
    setCurrentId(slider.id_slider);
    setFormData({
      judul: slider.judul || '',
      deskripsi: slider.deskripsi || '',
      gambar: slider.gambar || '',
      urutan: slider.urutan || 0,
      is_active: Boolean(slider.is_active),
    });
    setFormError('');
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError('');
    setIsSubmitting(true);

    if (!formData.judul.trim() || !formData.gambar.trim()) {
      setFormError('Judul dan URL gambar banner wajib diisi.');
      setIsSubmitting(false);
      return;
    }

    try {
      if (modalMode === 'create') {
        const res = await createSliderAction(formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal menambahkan banner.');
          setIsSubmitting(false);
          return;
        }
      } else if (currentId) {
        const res = await updateSliderAction(currentId, formData);
        if (!res.success) {
          setFormError(res.error || 'Gagal mengubah data banner.');
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

  const handleDelete = async (id: number, judul: string) => {
    if (!confirm(`Hapus banner promosi "${judul}"?`)) return;
    const res = await deleteSliderAction(id);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal menghapus banner slider.');
    }
  };

  const toggleStatus = async (slider: Slider) => {
    const res = await updateSliderAction(slider.id_slider, {
      is_active: !slider.is_active,
    });
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal mengubah status banner.');
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
            Kelola Slider
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-6">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Kelola Banner Promo
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Atur gambar carousel yang ditampilkan di halaman beranda toko.
                </p>
              </div>
              <button
                type="button"
                onClick={openCreateModal}
                className="bg-white text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-2xl font-bold shadow-lg transition flex items-center gap-2 cursor-pointer group"
              >
                <Plus className="w-5 h-5 group-hover:scale-110 transition-transform" />
                <span>Tambah Banner</span>
              </button>
            </div>

            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[550px] relative border border-white/40">
              <div className="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                  <h2 className="text-gray-800 font-extrabold text-2xl tracking-tight">
                    Daftar Banner Promo
                  </h2>
                  <p className="text-gray-400 text-sm mt-1">
                    Aktifkan atau ubah susunan urutan banner secara dinamis.
                  </p>
                </div>
                <span className="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-xs border border-blue-100">
                  Total: {sliders.length} Banner
                </span>
              </div>

              {/* Desktop Header */}
              <div className="hidden md:flex px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
                <div className="w-36">Preview</div>
                <div className="flex-1">Info Promo</div>
                <div className="w-24 text-center">Urutan</div>
                <div className="w-32 text-center">Status</div>
                <div className="w-28 text-right">Aksi</div>
              </div>

              {/* List */}
              {sliders.length === 0 ? (
                <div className="py-24 text-center">
                  <div className="w-20 h-20 bg-blue-50 text-blue-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-sm">
                    <Images className="w-10 h-10" />
                  </div>
                  <h3 className="font-bold text-gray-700 text-lg">Belum ada banner promo</h3>
                  <p className="text-gray-400 text-sm max-w-sm mx-auto mt-1">
                    Tambahkan gambar promo menarik untuk memikat pelanggan berbelanja.
                  </p>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  {sliders.map((s) => (
                    <div
                      key={s.id_slider}
                      className="group flex flex-col md:flex-row md:items-center p-3.5 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/20 transition-all duration-200 relative overflow-hidden"
                    >
                      <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />

                      {/* Image Preview */}
                      <div className="w-full md:w-36 mb-3 md:mb-0 pr-3">
                        <div className="relative w-full h-20 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-xs group-hover:shadow-md transition">
                          <img
                            src={s.gambar}
                            alt={s.judul || 'Banner'}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                          />
                        </div>
                      </div>

                      {/* Info */}
                      <div className="flex-1 min-w-0 pr-4 mb-2 md:mb-0">
                        <h3 className="font-bold text-gray-800 text-base truncate group-hover:text-blue-600 transition">
                          {s.judul || 'Banner Promo'}
                        </h3>
                        <p className="text-xs text-gray-400 mt-0.5 truncate max-w-md">
                          {s.deskripsi || 'Tidak ada deskripsi tambahan.'}
                        </p>
                      </div>

                      {/* Order */}
                      <div className="w-full md:w-24 flex md:justify-center items-center mb-2 md:mb-0">
                        <span className="text-xs font-bold text-gray-600 bg-gray-100 border border-gray-200 px-3 py-1 rounded-lg">
                          #{s.urutan || 0}
                        </span>
                      </div>

                      {/* Status Toggle */}
                      <div className="w-full md:w-32 flex md:justify-center items-center mb-2 md:mb-0">
                        <button
                          type="button"
                          onClick={() => toggleStatus(s)}
                          className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border shadow-xs transition cursor-pointer ${
                            s.is_active
                              ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'
                              : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200'
                          }`}
                        >
                          {s.is_active ? (
                            <>
                              <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse" /> Aktif
                            </>
                          ) : (
                            <>
                              <EyeOff className="w-3 h-3" /> Nonaktif
                            </>
                          )}
                        </button>
                      </div>

                      {/* Action */}
                      <div className="flex items-center justify-end w-full md:w-28 gap-2">
                        <button
                          type="button"
                          onClick={() => openEditModal(s)}
                          className="bg-white text-gray-600 hover:text-blue-600 p-2 rounded-xl border border-gray-200 hover:border-blue-300 shadow-xs transition cursor-pointer"
                          title="Edit Banner"
                        >
                          <Edit2 className="w-4 h-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => handleDelete(s.id_slider, s.judul || 'Banner')}
                          className="bg-white text-gray-600 hover:text-red-600 p-2 rounded-xl border border-gray-200 hover:border-red-300 shadow-xs transition cursor-pointer"
                          title="Hapus Banner"
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
          <div className="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
              <h3 className="font-extrabold text-gray-800 text-lg">
                {modalMode === 'create' ? 'Tambah Banner Promo' : 'Edit Banner Promo'}
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
                  Judul Promo *
                </label>
                <input
                  type="text"
                  required
                  value={formData.judul}
                  onChange={(e) => setFormData({ ...formData, judul: e.target.value })}
                  placeholder="Contoh: Diskon Akhir Pekan 20%"
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  URL Gambar Banner *
                </label>
                <input
                  type="url"
                  required
                  value={formData.gambar}
                  onChange={(e) => setFormData({ ...formData, gambar: e.target.value })}
                  placeholder="https://..."
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                />
                <p className="text-[11px] text-gray-400 mt-1">
                  Disarankan format landscape (1200 x 400 px) resolusi tinggi.
                </p>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Urutan Tampil
                  </label>
                  <input
                    type="number"
                    min="0"
                    value={formData.urutan}
                    onChange={(e) => setFormData({ ...formData, urutan: Number(e.target.value) })}
                    className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold"
                  />
                </div>
                <div className="flex flex-col justify-end">
                  <label className="flex items-center gap-2 text-sm font-bold text-gray-700 cursor-pointer p-2.5 border border-gray-200 rounded-xl">
                    <input
                      type="checkbox"
                      checked={formData.is_active}
                      onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                      className="w-4 h-4 text-blue-600 rounded"
                    />
                    <span>Status Aktif</span>
                  </label>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Deskripsi Singkat
                </label>
                <textarea
                  rows={2}
                  value={formData.deskripsi}
                  onChange={(e) => setFormData({ ...formData, deskripsi: e.target.value })}
                  placeholder="Keterangan syarat & ketentuan atau periode promo..."
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
                  {isSubmitting ? 'Menyimpan...' : 'Simpan Banner'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
