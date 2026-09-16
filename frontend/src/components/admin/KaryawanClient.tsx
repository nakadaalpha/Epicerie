'use client';

import React, { useState } from 'react';
import {
  Users,
  UserPlus,
  Search,
  Edit2,
  Trash2,
  X,
  Loader2,
  Check,
  Shield,
  Menu,
  Phone,
  Lock,
  User,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import {
  createEmployeeAction,
  updateEmployeeAction,
  deleteEmployeeAction,
} from '@/app/actions/employee';

interface Employee {
  id_user: number;
  nama: string;
  username: string;
  role: string;
  email?: string | null;
  no_hp?: string | null;
  foto_profil?: string | null;
  created_at: string;
}

interface KaryawanClientProps {
  initialEmployees: Employee[];
  currentUser?: any;
  pendingCardCount?: number;
}

export const KaryawanClient: React.FC<KaryawanClientProps> = ({
  initialEmployees,
  currentUser,
  pendingCardCount = 0,
}) => {
  const [employees, setEmployees] = useState<Employee[]>(initialEmployees);
  const [searchQuery, setSearchQuery] = useState('');
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingEmployee, setEditingEmployee] = useState<Employee | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Form State
  const [formData, setFormData] = useState({
    nama: '',
    username: '',
    password: '',
    role: 'karyawan',
    no_hp: '',
  });
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  const showNotification = (msg: string, isError = false) => {
    if (isError) {
      setErrorMsg(msg);
      setTimeout(() => setErrorMsg(null), 4000);
    } else {
      setSuccessMsg(msg);
      setTimeout(() => setSuccessMsg(null), 4000);
    }
  };

  const handleOpenCreateModal = () => {
    setEditingEmployee(null);
    setFormData({
      nama: '',
      username: '',
      password: '',
      role: 'karyawan',
      no_hp: '',
    });
    setErrorMsg(null);
    setIsModalOpen(true);
  };

  const handleOpenEditModal = (emp: Employee) => {
    setEditingEmployee(emp);
    setFormData({
      nama: emp.nama,
      username: emp.username,
      password: '',
      role: emp.role?.toLowerCase() || 'karyawan',
      no_hp: emp.no_hp || '',
    });
    setErrorMsg(null);
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);

    if (!formData.nama.trim() || !formData.username.trim()) {
      setErrorMsg('Nama dan username wajib diisi.');
      return;
    }

    if (!editingEmployee && !formData.password.trim()) {
      setErrorMsg('Password wajib diisi untuk karyawan baru.');
      return;
    }

    setIsSubmitting(true);
    try {
      if (editingEmployee) {
        const res = await updateEmployeeAction(editingEmployee.id_user, formData);
        if (res.success && res.data) {
          setEmployees((prev) =>
            prev.map((e) => (e.id_user === editingEmployee.id_user ? res.data : e))
          );
          showNotification('Data karyawan berhasil diperbarui!');
          setIsModalOpen(false);
        } else {
          setErrorMsg(res.error || 'Gagal memperbarui karyawan.');
        }
      } else {
        const res = await createEmployeeAction(formData);
        if (res.success && res.data) {
          setEmployees((prev) => [res.data, ...prev]);
          showNotification('Karyawan baru berhasil ditambahkan!');
          setIsModalOpen(false);
        } else {
          setErrorMsg(res.error || 'Gagal menambahkan karyawan.');
        }
      }
    } catch (err: any) {
      setErrorMsg(err.message || 'Terjadi kesalahan sistem.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDelete = async (id: number, nama: string) => {
    if (!confirm(`Hapus akun karyawan "${nama}"? Tindakan ini tidak dapat dibatalkan.`)) {
      return;
    }

    try {
      const res = await deleteEmployeeAction(id);
      if (res.success) {
        setEmployees((prev) => prev.filter((e) => e.id_user !== id));
        showNotification(`Akun ${nama} berhasil dihapus.`);
      } else {
        showNotification(res.error || 'Gagal menghapus karyawan.', true);
      }
    } catch (err: any) {
      showNotification(err.message || 'Terjadi kesalahan sistem.', true);
    }
  };

  const filteredEmployees = employees.filter((emp) => {
    const q = searchQuery.toLowerCase().trim();
    if (!q) return true;
    return (
      emp.nama.toLowerCase().includes(q) ||
      emp.username.toLowerCase().includes(q) ||
      (emp.role && emp.role.toLowerCase().includes(q))
    );
  });

  const getRoleBadge = (role: string) => {
    const r = role.toLowerCase();
    if (r === 'pemilik') {
      return <span className="px-2.5 py-1 rounded-lg text-[11px] font-black bg-purple-50 text-purple-700 border border-purple-200">PEMILIK</span>;
    }
    if (r === 'admin' || r === 'manajer') {
      return <span className="px-2.5 py-1 rounded-lg text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">MANAJER</span>;
    }
    if (r === 'kasir') {
      return <span className="px-2.5 py-1 rounded-lg text-[11px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">KASIR</span>;
    }
    if (r === 'kurir') {
      return <span className="px-2.5 py-1 rounded-lg text-[11px] font-black bg-sky-50 text-sky-700 border border-sky-200">KURIR</span>;
    }
    return <span className="px-2.5 py-1 rounded-lg text-[11px] font-black bg-blue-50 text-blue-700 border border-blue-200">KARYAWAN</span>;
  };

  return (
    <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
      {/* Sidebar */}
      <AdminSidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        currentUser={currentUser}
        pendingCardCount={pendingCardCount}
      />

      {/* Main Container */}
      <div className="flex-1 flex flex-col h-full overflow-hidden">
        {/* Top Navbar */}
        <header className="h-16 bg-white border-b border-gray-200/80 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-xs">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsSidebarOpen(true)}
              className="md:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition"
            >
              <Menu className="w-5 h-5" />
            </button>
            <div className="flex items-center gap-2.5">
              <div className="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <Users className="w-5 h-5" />
              </div>
              <h1 className="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                Manajemen Karyawan
              </h1>
            </div>
          </div>

          <button
            onClick={handleOpenCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer"
          >
            <UserPlus className="w-4 h-4" />
            <span className="hidden sm:inline">Tambah Karyawan</span>
            <span className="sm:hidden">Tambah</span>
          </button>
        </header>

        {/* Notifications */}
        {successMsg && (
          <div className="mx-4 sm:mx-8 mt-4 bg-emerald-500 text-white font-bold text-xs p-3 rounded-xl shadow-md flex items-center gap-2">
            <Check className="w-4 h-4" />
            <span>{successMsg}</span>
          </div>
        )}

        {errorMsg && (
          <div className="mx-4 sm:mx-8 mt-4 bg-rose-500 text-white font-bold text-xs p-3 rounded-xl shadow-md flex items-center gap-2">
            <span>{errorMsg}</span>
          </div>
        )}

        {/* Content Area */}
        <main className="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6">
          {/* Header Controls: Search & Stats */}
          <div className="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <div className="relative flex-1 max-w-md">
              <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Cari nama, username, atau peran..."
                className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
              />
            </div>
            <div className="text-xs font-bold text-gray-500 self-end sm:self-center">
              Total Staf Terdaftar:{' '}
              <span className="text-blue-600 font-extrabold">{employees.length}</span>
            </div>
          </div>

          {/* Employee Table */}
          <div className="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-gray-50/80 border-b border-gray-200/80 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                  <tr>
                    <th className="py-3.5 px-6">Nama Karyawan</th>
                    <th className="py-3.5 px-6">Username</th>
                    <th className="py-3.5 px-6">Peran (Role)</th>
                    <th className="py-3.5 px-6">No. Handphone</th>
                    <th className="py-3.5 px-6">Tanggal Dibuat</th>
                    <th className="py-3.5 px-6 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 font-medium">
                  {filteredEmployees.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="py-12 text-center text-gray-400 font-bold">
                        Tidak ada data karyawan ditemukan.
                      </td>
                    </tr>
                  ) : (
                    filteredEmployees.map((emp) => (
                      <tr key={emp.id_user} className="hover:bg-blue-50/30 transition">
                        <td className="py-4 px-6">
                          <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs">
                              {emp.nama.charAt(0).toUpperCase()}
                            </div>
                            <div>
                              <p className="font-extrabold text-gray-900 text-xs">
                                {emp.nama}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="py-4 px-6 text-gray-600 font-semibold">
                          @{emp.username}
                        </td>
                        <td className="py-4 px-6">{getRoleBadge(emp.role)}</td>
                        <td className="py-4 px-6 text-gray-500 font-medium">
                          {emp.no_hp || '-'}
                        </td>
                        <td className="py-4 px-6 text-gray-400 text-[11px]">
                          {new Date(emp.created_at).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                          })}
                        </td>
                        <td className="py-4 px-6 text-right">
                          <div className="flex items-center justify-end gap-2">
                            <button
                              onClick={() => handleOpenEditModal(emp)}
                              className="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                              title="Edit Karyawan"
                            >
                              <Edit2 className="w-4 h-4" />
                            </button>
                            <button
                              onClick={() => handleDelete(emp.id_user, emp.nama)}
                              className="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                              title="Hapus Karyawan"
                            >
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>

      {/* Modal Form: Create / Edit Employee */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
            <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
              <h3 className="font-extrabold text-sm text-gray-900">
                {editingEmployee ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru'}
              </h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg transition"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              {errorMsg && (
                <div className="bg-rose-50 border border-rose-200 text-rose-600 text-xs p-3 rounded-xl font-bold">
                  {errorMsg}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Nama Lengkap
                </label>
                <div className="relative">
                  <User className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    required
                    value={formData.nama}
                    onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                    placeholder="Contoh: Budi Santoso"
                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Username
                </label>
                <div className="relative">
                  <span className="text-gray-400 font-bold absolute left-3.5 top-1/2 -translate-y-1/2 text-xs">
                    @
                  </span>
                  <input
                    type="text"
                    required
                    value={formData.username}
                    onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                    placeholder="budisantoso"
                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Peran / Jabatan
                </label>
                <div className="relative">
                  <Shield className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <select
                    value={formData.role}
                    onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  >
                    <option value="karyawan">Karyawan Toko</option>
                    <option value="kasir">Kasir (POS)</option>
                    <option value="kurir">Kurir Delivery</option>
                    <option value="gudang">Staf Gudang & Inventaris</option>
                    <option value="manajer">Manajer Operasional</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  No. Handphone (Opsional)
                </label>
                <div className="relative">
                  <Phone className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    value={formData.no_hp}
                    onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                    placeholder="08123456789"
                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  {editingEmployee
                    ? 'Password Baru (Kosongkan jika tidak ingin diubah)'
                    : 'Password Akun'}
                </label>
                <div className="relative">
                  <Lock className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="password"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    placeholder={editingEmployee ? '••••••••' : 'Minimal 4 karakter'}
                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </div>
              </div>

              <div className="pt-4 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer"
                >
                  {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
                  <span>{editingEmployee ? 'Simpan Perubahan' : 'Tambah Karyawan'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
