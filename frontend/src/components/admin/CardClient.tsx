'use client';

import React, { useState, useTransition } from 'react';
import { useRouter } from 'next/navigation';
import {
  Menu,
  Printer,
  CheckCircle,
  Clock,
  ExternalLink,
  Check,
  FolderOpen,
  User,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { completeCardPrintAction } from '@/app/actions/shop';

interface CardClientProps {
  requests: any[];
  currentUser: any;
  pendingCardCount: number;
}

export function CardClient({
  requests,
  currentUser,
  pendingCardCount,
}: CardClientProps) {
  const router = useRouter();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [isPending, startTransition] = useTransition();

  const handleComplete = async (idUser: number) => {
    if (!confirm('Tandai kartu member ini sudah selesai dicetak dan dikirim?')) return;
    const res = await completeCardPrintAction(idUser);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal memperbarui status cetak kartu');
    }
  };

  const handlePrint = (member: any) => {
    // Generate printable card window (ISO/IEC 7810 ID-1 standard: 85.6mm x 53.98mm)
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=${encodeURIComponent(String(member.id_user))}`;

    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Kartu Member - ${member.nama}</title>
        <style>
          @page {
            size: 85.6mm 53.98mm;
            margin: 0;
          }
          * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            gap: 10mm;
          }
          .card-container {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 3.18mm;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #0f172a;
          }
          .card-front {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%), url('/images/card_bg.png') center/cover no-repeat;
            background-blend-mode: overlay;
            padding: 5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
          }
          .card-back {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%), url('/images/card_bg_back.png') center/cover no-repeat;
            background-blend-mode: overlay;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
          }
          .mag-stripe {
            width: 100%;
            height: 9mm;
            background: #000000;
            margin-top: 3mm;
          }
          .back-content {
            padding: 4mm 5mm 5mm 5mm;
            font-size: 6pt;
            line-height: 1.4;
            color: #cbd5e1;
          }
          .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
          }
          .brand-title {
            font-size: 11pt;
            font-weight: 900;
            letter-spacing: 2px;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.4);
          }
          .tier-badge {
            background: #f59e0b;
            color: #1e293b;
            font-size: 6.5pt;
            font-weight: 800;
            text-transform: uppercase;
            padding: 1.5px 6px;
            border-radius: 20px;
            letter-spacing: 0.5px;
          }
          .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
          }
          .member-info {
            max-width: 58mm;
          }
          .member-label {
            font-size: 5.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.75);
            margin-bottom: 1px;
          }
          .member-name {
            font-size: 9.5pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
          }
          .member-id {
            font-size: 6.5pt;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.85);
            margin-top: 2px;
          }
          .qr-box {
            width: 13mm;
            height: 13mm;
            background: #ffffff;
            border-radius: 1.5mm;
            padding: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
          }
          .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
          }
          @media print {
            body {
              background: transparent;
              gap: 0;
            }
            .card-container {
              box-shadow: none;
              page-break-after: always;
            }
            .no-print {
              display: none;
            }
          }
        </style>
      </head>
      <body>
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
          <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 13px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            🖨️ Cetak / Simpan PDF
          </button>
        </div>

        <!-- SISI DEPAN -->
        <div class="card-container">
          <div class="card-front">
            <div class="header">
              <div class="brand-title">ÉPICERIE</div>
              <div class="tier-badge">${member.membership || 'GOLD'}</div>
            </div>
            <div class="footer">
              <div class="member-info">
                <div class="member-label">NAMA KEANGGOTAAN</div>
                <div class="member-name">${member.nama.toUpperCase()}</div>
                <div class="member-id">NO: ${String(member.id_user).padStart(8, '0')}</div>
              </div>
              <div class="qr-box">
                <img src="${qrUrl}" alt="QR" />
              </div>
            </div>
          </div>
        </div>

        <!-- SISI BELAKANG -->
        <div class="card-container">
          <div class="card-back">
            <div class="mag-stripe"></div>
            <div class="back-content">
              <p style="margin: 0 0 4px 0; font-weight: bold;">SYARAT & KETENTUAN KARTU MEMBER</p>
              <p style="margin: 0 0 3px 0;">1. Kartu ini merupakan properti resmi dari Épicerie Gourmet Store.</p>
              <p style="margin: 0 0 3px 0;">2. Tunjukkan kartu ini atau scan barcode kasir pada saat berbelanja untuk mendapatkan poin & diskon khusus.</p>
              <p style="margin: 0;">3. Jika menemukan kartu ini, mohon kembalikan ke gerai Épicerie terdekat.</p>
            </div>
            <div style="padding: 0 5mm 4mm 5mm; display: flex; justify-content: space-between; font-size: 5pt; color: #94a3b8;">
              <span>CS: support@epicerie.local</span>
              <span>www.epicerie.local</span>
            </div>
          </div>
        </div>

        <script>
          window.onload = function() {
            setTimeout(function() {
              window.print();
            }, 300);
          }
        </script>
      </body>
      </html>
    `);
    printWindow.document.close();
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
            Antrian Cetak Kartu
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
            <div>
              <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                Antrean Cetak Kartu Member
              </h1>
              <p className="text-white/80 text-sm mt-0.5">
                Daftar permintaan pencetakan fisik kartu keanggotaan pelanggan.
              </p>
            </div>

            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[550px] relative border border-white/40">
              <div className="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                  <h2 className="text-gray-800 font-extrabold text-2xl tracking-tight">
                    Permintaan Cetak Aktif
                  </h2>
                  <p className="text-gray-400 text-sm mt-1">
                    Cetak format PDF fisik atau tandai sudah selesai dicetak.
                  </p>
                </div>
                <span className="bg-blue-100 text-blue-800 text-xs font-bold px-4 py-2 rounded-full">
                  {requests.length} Permintaan
                </span>
              </div>

              {requests.length === 0 ? (
                <div className="text-center py-24 text-gray-400">
                  <div className="w-20 h-20 bg-blue-50 text-blue-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-sm">
                    <Printer className="w-10 h-10" />
                  </div>
                  <h3 className="font-bold text-gray-700 text-lg">Tidak ada antrean cetak</h3>
                  <p className="text-gray-400 text-sm max-w-sm mx-auto mt-1">
                    Semua permintaan kartu anggota saat ini telah selesai diproses.
                  </p>
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th className="p-4">Tanggal Request</th>
                        <th className="p-4">Member</th>
                        <th className="p-4">Level</th>
                        <th className="p-4 text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {requests.map((req) => (
                        <tr key={req.id_user} className="hover:bg-gray-50/70 transition">
                          <td className="p-4 text-sm text-gray-500">
                            {new Date(req.updated_at).toLocaleDateString('id-ID', {
                              day: 'numeric',
                              month: 'short',
                              year: 'numeric',
                              hour: '2-digit',
                              minute: '2-digit',
                            })}
                          </td>
                          <td className="p-4">
                            <div className="flex items-center gap-3">
                              <div className="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                {req.nama ? req.nama.substring(0, 1).toUpperCase() : 'U'}
                              </div>
                              <div>
                                <p className="text-sm font-bold text-gray-800">{req.nama}</p>
                                <p className="text-xs text-gray-400">@{req.username}</p>
                              </div>
                            </div>
                          </td>
                          <td className="p-4">
                            <span className="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-200">
                              {req.membership || 'Gold'}
                            </span>
                          </td>
                          <td className="p-4">
                            <div className="flex justify-center items-center gap-2">
                              <button
                                type="button"
                                onClick={() => handlePrint(req)}
                                className="bg-blue-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 transition flex items-center gap-1.5 shadow-xs cursor-pointer"
                              >
                                <Printer className="w-4 h-4" /> Cetak PDF
                              </button>
                              <button
                                type="button"
                                onClick={() => handleComplete(req.id_user)}
                                className="bg-green-100 text-green-800 border border-green-200 px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-green-200 transition flex items-center gap-1.5 cursor-pointer"
                              >
                                <Check className="w-4 h-4" /> Selesai
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
