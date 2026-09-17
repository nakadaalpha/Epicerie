'use client';

import React, { useState, useEffect } from 'react';
import { useCartStore } from '@/store/useCartStore';
import { formatRupiah } from '@/lib/utils';
import {
  Banknote,
  QrCode,
  CreditCard,
  X,
  Loader2,
  MapPin,
  Truck,
  Store,
  CheckCircle2,
  Plus,
  AlertTriangle,
  Sparkles,
} from 'lucide-react';
import {
  createTransaction,
  getUserAddressesAction,
  getMidtransSnapTokenAction,
} from '@/app/actions/shop';
import { getCurrentUser } from '@/app/actions/auth';
import { AddressModal } from '@/components/AddressModal';
import { AlamatPengiriman } from '@/types';

interface CheckoutModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (trxResult: any) => void;
}

export const CheckoutModal: React.FC<CheckoutModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const { items, customerName, getTotalPrice, clearCart } = useCartStore();
  const subtotal = getTotalPrice();

  const [orderType, setOrderType] = useState<'pickup' | 'delivery'>('pickup');
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [addresses, setAddresses] = useState<AlamatPengiriman[]>([]);
  const [selectedAddress, setSelectedAddress] = useState<AlamatPengiriman | null>(null);
  const [isAddressModalOpen, setIsAddressModalOpen] = useState<boolean>(false);

  const [paymentMethod, setPaymentMethod] = useState<'Tunai' | 'QRIS' | 'Transfer'>('Tunai');
  const [cashGiven, setCashGiven] = useState<number>(subtotal);
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string>('');

  // Fetch current user and addresses when modal opens
  useEffect(() => {
    if (!isOpen) return;

    getCurrentUser()
      .then((user) => {
        if (user) {
          setCurrentUser(user);
          getUserAddressesAction()
            .then((addrList) => {
              if (addrList && addrList.length > 0) {
                setAddresses(addrList);
                const primary = addrList.find((a: any) => a.is_primary || a.is_utama) || addrList[0];
                setSelectedAddress(primary);
              }
            })
            .catch(() => {});
        }
      })
      .catch(() => {});
  }, [isOpen]);

  if (!isOpen) return null;

  const isGoldMember =
    (currentUser?.membership || '').toLowerCase() === 'gold';

  // Shipping Fee Logic: 5000 flat, but Free (0) for Gold Member
  const shippingFee = orderType === 'delivery' ? (isGoldMember ? 0 : 5000) : 0;
  const grandTotal = subtotal + shippingFee;

  // Delivery Minimum Spend Validation: Rp 30.000
  const isDeliveryUnderMinimum = orderType === 'delivery' && subtotal < 30000;

  const kembalian = Math.max(0, cashGiven - grandTotal);
  const quickAmounts = [grandTotal, 10000, 20000, 50000, 100000].filter(
    (v, i, a) => a.indexOf(v) === i && v >= grandTotal
  );

  const handleCheckout = async () => {
    if (orderType === 'delivery') {
      if (isDeliveryUnderMinimum) {
        setErrorMsg('Minimum belanja untuk pengiriman adalah Rp 30.000.');
        return;
      }
      if (!selectedAddress) {
        setErrorMsg('Silakan pilih atau tambahkan alamat pengiriman terlebih dahulu.');
        return;
      }
    }

    if (paymentMethod === 'Tunai' && cashGiven < grandTotal) {
      setErrorMsg('Uang yang diberikan kurang dari total belanja.');
      return;
    }

    setLoading(true);
    setErrorMsg('');

    try {
      // 1. If Midtrans (Transfer) is chosen, fetch Snap Token
      if (paymentMethod === 'Transfer') {
        const snapRes = await getMidtransSnapTokenAction({
          total_bayar: grandTotal,
          customer_details: {
            nama: currentUser?.nama || customerName || 'Pelanggan Épicerie',
            email: currentUser?.email || 'customer@epicerie.local',
            no_hp: currentUser?.no_hp || '08123456789',
          },
        });

        if (snapRes.success && snapRes.data?.token) {
          const snapToken = snapRes.data.token;

          // If client has window.snap available
          if (typeof window !== 'undefined' && (window as any).snap) {
            (window as any).snap.pay(snapToken, {
              onSuccess: async () => {
                await executeCreateTransaction('Midtrans');
              },
              onPending: async () => {
                await executeCreateTransaction('Midtrans');
              },
              onError: () => {
                setErrorMsg('Pembayaran Midtrans gagal atau dibatalkan.');
                setLoading(false);
              },
              onClose: () => {
                setLoading(false);
              },
            });
            return;
          }
        }
      }

      await executeCreateTransaction(paymentMethod);
    } catch (err: any) {
      setErrorMsg(err.message || 'Gagal terhubung ke server.');
      setLoading(false);
    }
  };

  const executeCreateTransaction = async (method: string) => {
    const payload = {
      items: items.map((i) => ({
        id_produk: i.produk.id_produk,
        jumlah: i.jumlah,
        harga: i.produk.harga,
      })),
      total_bayar: grandTotal,
      ongkir: shippingFee,
      metode_pembayaran: method,
      tipe_pengiriman: orderType,
      id_alamat: orderType === 'delivery' ? selectedAddress?.id_alamat : null,
      nama_pelanggan_hold: customerName || currentUser?.nama || 'Pelanggan Walk-in',
    };

    const res = await createTransaction(payload);

    if (!res.success) {
      setErrorMsg(res.error || 'Terjadi kesalahan saat memproses pesanan.');
      setLoading(false);
      return;
    }

    // Pass result to parent for receipt
    onSuccess({
      ...res,
      items: [...items],
      metodePembayaran: method,
      namaPelanggan: customerName || currentUser?.nama || 'Walk-in',
      uangDiterima: method === 'Tunai' ? cashGiven : undefined,
      kembalian: method === 'Tunai' ? kembalian : undefined,
      ongkir: shippingFee,
      tipePengiriman: orderType,
      alamat: selectedAddress,
    });

    clearCart();
    onClose();
  };

  return (
    <>
      <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div className="bg-white rounded-3xl w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
          {/* Header */}
          <div className="p-5 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
              <h2 className="text-lg font-extrabold text-gray-900">Pembayaran Kasir</h2>
              <p className="text-xs text-gray-500">Pilih opsi pengiriman dan metode pembayaran</p>
            </div>
            <button
              onClick={onClose}
              className="p-2 rounded-xl text-gray-400 hover:bg-gray-100 transition cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="p-6 overflow-y-auto space-y-5 scrollbar-thin">
            {/* Delivery / Pickup Tabs */}
            <div>
              <label className="text-xs font-bold text-gray-700 block mb-2">
                Opsi Pengambilan & Pengiriman
              </label>
              <div className="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => {
                    setOrderType('pickup');
                    setErrorMsg('');
                  }}
                  className={`p-3 rounded-2xl border flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer ${
                    orderType === 'pickup'
                      ? 'border-blue-600 bg-blue-50 text-blue-700 shadow-sm'
                      : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  <Store className="w-4 h-4" />
                  <span>Ambil di Toko (Pickup)</span>
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setOrderType('delivery');
                    setErrorMsg('');
                  }}
                  className={`p-3 rounded-2xl border flex items-center justify-center gap-2 text-xs font-bold transition cursor-pointer ${
                    orderType === 'delivery'
                      ? 'border-blue-600 bg-blue-50 text-blue-700 shadow-sm'
                      : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  <Truck className="w-4 h-4" />
                  <span>Kirim ke Alamat (Delivery)</span>
                </button>
              </div>
            </div>

            {/* Address Selection (Delivery only) */}
            {orderType === 'delivery' && (
              <div className="space-y-3 p-4 bg-slate-50 border border-slate-200/80 rounded-2xl">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                    <MapPin className="w-4 h-4 text-blue-600" /> Alamat Pengiriman
                  </span>
                  <button
                    type="button"
                    onClick={() => setIsAddressModalOpen(true)}
                    className="text-xs text-blue-600 font-bold hover:underline flex items-center gap-1 cursor-pointer"
                  >
                    <Plus className="w-3.5 h-3.5" /> Ubah / Tambah Alamat
                  </button>
                </div>

                {selectedAddress ? (
                  <div className="p-3 bg-white rounded-xl border border-slate-200 text-xs space-y-1 shadow-2xs">
                    <div className="flex items-center gap-2">
                      <span className="font-extrabold text-slate-900">
                        {selectedAddress.label}
                      </span>
                      <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                        {selectedAddress.penerima} ({selectedAddress.no_hp_penerima})
                      </span>
                    </div>
                    <p className="text-slate-600 leading-relaxed">
                      {selectedAddress.detail_alamat}
                    </p>
                  </div>
                ) : (
                  <div className="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center justify-between">
                    <span>Belum ada alamat pengiriman terpilih.</span>
                    <button
                      type="button"
                      onClick={() => setIsAddressModalOpen(true)}
                      className="font-bold underline cursor-pointer"
                    >
                      Pilih Sekarang
                    </button>
                  </div>
                )}

                {/* Min Order Warning */}
                {isDeliveryUnderMinimum && (
                  <div className="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center gap-2">
                    <AlertTriangle className="w-4 h-4 shrink-0 text-red-600" />
                    <span>
                      Minimum belanja untuk pengiriman adalah <strong>Rp 30.000</strong>. Kurang{' '}
                      <strong>{formatRupiah(30000 - subtotal)}</strong>.
                    </span>
                  </div>
                )}

                {/* Membership Gold Free Shipping Alert */}
                {isGoldMember && (
                  <div className="p-2.5 bg-amber-50 border border-amber-200/80 rounded-xl text-xs text-amber-800 flex items-center gap-2 font-medium">
                    <Sparkles className="w-4 h-4 text-amber-600 shrink-0" />
                    <span>
                      Member Gold Terdeteksi: <strong>Gratis Biaya Ongkir</strong> (Hemat Rp 5.000)!
                    </span>
                  </div>
                )}
              </div>
            )}

            {/* Total Box */}
            <div className="bg-blue-50/70 border border-blue-100 rounded-2xl p-4 space-y-2">
              <div className="flex justify-between text-xs text-blue-900/80">
                <span>Subtotal ({items.length} item)</span>
                <span className="font-semibold">{formatRupiah(subtotal)}</span>
              </div>
              {orderType === 'delivery' && (
                <div className="flex justify-between text-xs text-blue-900/80">
                  <span>Ongkos Kirim</span>
                  <span className="font-semibold">
                    {shippingFee === 0 ? (
                      <span className="text-emerald-600 font-bold">GRATIS (Gold)</span>
                    ) : (
                      formatRupiah(shippingFee)
                    )}
                  </span>
                </div>
              )}
              <div className="border-t border-blue-200/60 pt-2 flex justify-between items-baseline">
                <span className="text-xs font-extrabold text-blue-900">Total Tagihan</span>
                <span className="text-2xl font-black text-blue-900 tracking-tight">
                  {formatRupiah(grandTotal)}
                </span>
              </div>
            </div>

            {/* Payment Method Selector */}
            <div>
              <label className="text-xs font-bold text-gray-700 block mb-2">
                Metode Pembayaran
              </label>
              <div className="grid grid-cols-3 gap-2">
                {[
                  { id: 'Tunai', label: 'Tunai', icon: Banknote },
                  { id: 'QRIS', label: 'QRIS', icon: QrCode },
                  { id: 'Transfer', label: 'Midtrans / Online', icon: CreditCard },
                ].map((m) => {
                  const Icon = m.icon;
                  const isSelected = paymentMethod === m.id;
                  return (
                    <button
                      key={m.id}
                      type="button"
                      onClick={() => {
                        setPaymentMethod(m.id as any);
                        setErrorMsg('');
                      }}
                      className={`p-3 rounded-2xl border flex flex-col items-center gap-1.5 transition text-xs font-bold cursor-pointer ${
                        isSelected
                          ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-500/20'
                          : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                      }`}
                    >
                      <Icon className="w-5 h-5" />
                      <span>{m.label}</span>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Cash Input & Quick Amounts */}
            {paymentMethod === 'Tunai' && (
              <div className="space-y-3 pt-2">
                <div>
                  <label className="text-xs font-bold text-gray-700 block mb-1">
                    Uang Diterima (Rp)
                  </label>
                  <input
                    type="number"
                    value={cashGiven || ''}
                    onChange={(e) => setCashGiven(Number(e.target.value) || 0)}
                    className="w-full text-xl font-black px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 text-gray-900"
                    placeholder="0"
                  />
                </div>

                {/* Quick Cash Buttons */}
                <div className="flex flex-wrap gap-1.5">
                  {quickAmounts.map((amt) => (
                    <button
                      key={amt}
                      type="button"
                      onClick={() => setCashGiven(amt)}
                      className="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition active:scale-95 cursor-pointer"
                    >
                      {amt === grandTotal ? 'Uang Pas' : formatRupiah(amt)}
                    </button>
                  ))}
                </div>

                {/* Kembalian Box */}
                <div className="flex items-center justify-between p-3.5 bg-emerald-50 rounded-2xl border border-emerald-100">
                  <span className="text-xs font-bold text-emerald-800">Kembalian:</span>
                  <span className="text-lg font-black text-emerald-700">
                    {formatRupiah(kembalian)}
                  </span>
                </div>
              </div>
            )}

            {/* QRIS Display */}
            {paymentMethod === 'QRIS' && (
              <div className="p-4 bg-gray-50 border border-gray-200 rounded-2xl text-center space-y-2">
                <div className="w-40 h-40 mx-auto bg-white p-2 rounded-xl border shadow-xs flex items-center justify-center">
                  <QrCode className="w-32 h-32 text-gray-800" />
                </div>
                <p className="text-xs font-semibold text-gray-700">
                  Scan kode QRIS di atas dengan m-Banking atau e-Wallet
                </p>
                <p className="text-[11px] text-gray-400">
                  NMID: ID1029384756 (Épicerie Kiosk)
                </p>
              </div>
            )}

            {/* Midtrans Snap Info */}
            {paymentMethod === 'Transfer' && (
              <div className="p-4 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-900 space-y-1.5">
                <p className="font-extrabold flex items-center gap-1.5">
                  <CreditCard className="w-4 h-4 text-blue-600" /> Pembayaran Midtrans Snap Gateway
                </p>
                <p className="text-blue-700 leading-relaxed text-[11px]">
                  Setelah menekan tombol konfirmasi, popup pembayaran Midtrans akan terbuka untuk
                  Virtual Account (BCA, Mandiri, BRI, BNI), GoPay, ShopeePay, dan QRIS.
                </p>
              </div>
            )}

            {errorMsg && (
              <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
                {errorMsg}
              </div>
            )}

            {/* Submit Button */}
            <button
              onClick={handleCheckout}
              disabled={loading || grandTotal <= 0 || isDeliveryUnderMinimum}
              className="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-base shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 transition active:scale-98 disabled:opacity-50 disabled:pointer-events-none cursor-pointer"
            >
              {loading ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin" />
                  <span>Memproses Pembayaran...</span>
                </>
              ) : (
                <span>Konfirmasi & Selesaikan Transaksi</span>
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Address Selection Modal */}
      {isAddressModalOpen && (
        <AddressModal
          isOpen={isAddressModalOpen}
          onClose={() => setIsAddressModalOpen(false)}
          currentUser={currentUser}
          addresses={addresses}
          selectedAddressId={selectedAddress?.id_alamat}
          onSelectAddress={(addr) => {
            setSelectedAddress(addr);
            setIsAddressModalOpen(false);
          }}
        />
      )}
    </>
  );
};
