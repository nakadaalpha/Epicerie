'use client';

import React from 'react';
import { X, QrCode } from 'lucide-react';

interface MemberCardModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser?: any;
}

export const MemberCardModal: React.FC<MemberCardModalProps> = ({
  isOpen,
  onClose,
  currentUser,
}) => {
  if (!isOpen || !currentUser) return null;

  const membership = currentUser.membership || 'Classic';
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${currentUser.id_user}`;

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/90 backdrop-blur-md p-4 transition-opacity duration-300">
      <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md relative overflow-hidden animate-in zoom-in-95 duration-200">
        {/* Header Modal */}
        <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-600 to-indigo-600">
          <div>
            <h3 className="font-extrabold text-white text-lg">Kartu Member Digital</h3>
          </div>
          <button
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white hover:bg-white hover:text-indigo-600 transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Body: Tampilan Kartu 342px x 216px */}
        <div className="p-8 bg-gray-50 flex justify-center items-center">
          <div className="relative w-[342px] h-[216px] rounded-xl shadow-2xl overflow-hidden shrink-0 select-none bg-[#050505] text-white transition-transform hover:scale-[1.02] duration-500">
            {/* Background Gradient & Geometric Shapes */}
            <div className="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 z-0">
              <div className="absolute top-[20px] left-[15px] w-[30px] h-[6px] bg-emerald-500/80 rounded-full"></div>
              <div className="absolute top-[80px] right-[20px] w-[40px] h-[40px] border border-blue-500/30 rounded-full"></div>
              <div className="absolute bottom-[20px] left-[30px] w-[60px] h-[60px] bg-blue-600/10 rounded-full blur-xl"></div>
            </div>

            {/* Layer Konten Teks & QR */}
            <div className="relative z-10 w-full h-full p-5 flex flex-col justify-between">
              {/* Top Row: Brand & Membership Tier */}
              <div className="flex items-center justify-between">
                <h1 className="font-extrabold text-2xl tracking-widest uppercase text-white drop-shadow-md">
                  ÉPICERIE
                </h1>
                <div className="border border-[#2dd4bf] bg-black/40 backdrop-blur-sm rounded px-2.5 py-1">
                  <p className="text-[9px] font-bold text-[#2dd4bf] tracking-widest uppercase">
                    {membership} MEMBER
                  </p>
                </div>
              </div>

              {/* Bottom Row: User Info & QR Code */}
              <div className="flex items-end justify-between">
                <div>
                  <p className="font-bold text-base uppercase leading-tight text-white drop-shadow-md truncate max-w-[180px]">
                    {currentUser.nama || 'Pelanggan'}
                  </p>
                  <p className="text-[10px] text-[#94a3b8] font-mono tracking-widest mt-0.5">
                    @{currentUser.username || 'member'}
                  </p>
                  <p className="text-[9px] text-[#64748b] font-mono mt-1">
                    ID: {currentUser.id_user}
                  </p>
                </div>

                <div className="p-1.5 rounded-lg shadow-lg bg-white shrink-0">
                  <img
                    src={qrUrl}
                    alt={`QR Code ${currentUser.id_user}`}
                    className="w-[52px] h-[52px] object-contain"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Footer info */}
        <div className="px-6 py-3 bg-white border-t border-gray-100 text-center text-xs text-gray-500">
          <p>Tunjukkan QR ini ke kasir saat berbelanja di Épicerie Store.</p>
        </div>
      </div>
    </div>
  );
};
