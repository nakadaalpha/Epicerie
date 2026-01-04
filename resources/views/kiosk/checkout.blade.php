<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
        body { font-family: 'Nunito', sans-serif; }
        
        .shipping-radio:checked + div { border-color: #2563eb; background-color: #eff6ff; }
        .shipping-radio:checked + div .check-icon { display: block; }
        
        .payment-radio:checked + div { border-color: #2563eb; background-color: #eff6ff; }
        .payment-radio:checked + div .check-icon { display: block; }
        
        @keyframes slideInDown { from { transform: translate(-50%, -100%); opacity: 0; } to { transform: translate(-50%, 0); opacity: 1; } }
        .toast-enter { animation: slideInDown 0.4s ease-out forwards; }
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-32">

    @include('partials.navbar-kiosk')

    <div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[999] hidden flex items-center w-full max-w-sm p-4 space-x-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300" role="alert">
        <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"></div>
        <div class="ml-3 text-sm font-bold text-gray-800" id="toast-message"></div>
    </div>

    <div class="max-w-[1000px] mx-auto px-4 py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('kiosk.cart') }}" class="w-8 h-8 flex items-center justify-center bg-white rounded-full shadow hover:bg-gray-100"><i class="fa-solid fa-arrow-left"></i></a>
            <h1 class="font-bold text-2xl text-gray-800">Pengiriman & Pembayaran</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-truck-fast text-blue-600"></i> Metode Pengiriman</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer group">
                            <input type="radio" name="tipe_pengiriman" value="delivery" class="peer sr-only shipping-radio" checked>
                            <div class="p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition h-full relative">
                                <i class="fa-solid fa-circle-check text-blue-600 absolute top-3 right-3 hidden check-icon text-lg"></i>
                                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center mb-2 text-blue-600"><i class="fa-solid fa-motorcycle"></i></div>
                                <h4 class="font-bold text-sm text-gray-800">Diantar Kurir</h4>
                                <p class="text-xs text-gray-500 mt-1">Dikirim ke alamat Anda (Max 3KM)</p>
                            </div>
                        </label>

                        <label class="cursor-pointer group">
                            <input type="radio" name="tipe_pengiriman" value="pickup" class="peer sr-only shipping-radio">
                            <div class="p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition h-full relative">
                                <i class="fa-solid fa-circle-check text-blue-600 absolute top-3 right-3 hidden check-icon text-lg"></i>
                                <div class="w-10 h-10 bg-orange-50 rounded-full flex items-center justify-center mb-2 text-orange-600"><i class="fa-solid fa-store"></i></div>
                                <h4 class="font-bold text-sm text-gray-800">Ambil di Tempat</h4>
                                <p class="text-xs text-gray-500 mt-1">Ambil pesanan di kasir toko</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="address-section" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-location-dot text-red-500"></i> Alamat Pengiriman</h3>
                        <button type="button" class="text-xs font-bold text-blue-600 hover:underline" onclick="try{document.getElementById('address-dropdown').click()}catch(e){alert('Silakan ganti alamat lewat menu profil')}">Ganti Alamat</button>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="font-bold text-gray-800 text-sm" id="selected-address-label">Memuat alamat...</p>
                        <p class="text-xs text-gray-500 mt-1" id="selected-address-detail">...</p>
                        <p class="text-[10px] text-red-500 mt-2 italic">*Pesanan akan ditolak jika jarak > 3 KM.</p>
                    </div>
                </div>

                <div id="pickup-section" class="hidden bg-white p-5 rounded-xl shadow-sm border border-gray-100 transition-all duration-300">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-store text-orange-500"></i> Lokasi Toko</h3>
                    </div>

                    <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                        <div class="flex items-start gap-3">
                            <div class="mt-1"><i class="fa-solid fa-map-pin text-orange-600"></i></div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Épicerie Store (Pusat)</p>
                                <p class="text-xs text-gray-600 mt-1">Jl. Ki Ageng Gribig, Klaten Utara, Jawa Tengah.</p>
                                <p class="text-[10px] text-orange-600 mt-2 italic">*Tunjukkan kode pesanan kepada kasir saat pengambilan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-wallet text-gray-600"></i> Pembayaran</h3>
                    <div class="space-y-3">
                        <label class="block cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="Tunai" class="peer sr-only payment-radio" checked>
                            <div class="p-3 rounded-lg border border-gray-200 flex items-center justify-between hover:border-blue-300 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-100 rounded text-green-600 flex items-center justify-center"><i class="fa-solid fa-money-bill-wave"></i></div>
                                    <span class="font-bold text-sm text-gray-700">Tunai (COD / Di Kasir)</span>
                                </div>
                                <i class="fa-solid fa-circle-check text-blue-600 hidden check-icon"></i>
                            </div>
                        </label>
                        <label class="block cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="QRIS" class="peer sr-only payment-radio">
                            <div class="p-3 rounded-lg border border-gray-200 flex items-center justify-between hover:border-blue-300 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded text-blue-600 flex items-center justify-center"><i class="fa-solid fa-qrcode"></i></div>
                                    <span class="font-bold text-sm text-gray-700">QRIS / Transfer Bank</span>
                                </div>
                                <i class="fa-solid fa-circle-check text-blue-600 hidden check-icon"></i>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 sticky top-24">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Ringkasan Belanja</h3>
                    
                    <div class="max-h-60 overflow-y-auto mb-4 pr-2 space-y-3 custom-scrollbar">
                        @foreach($keranjang as $item)
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-gray-600 w-2/3 truncate">{{ $item->jumlah }}x {{ $item->produk->nama_produk }}</span>
                            <span class="font-bold text-gray-800">Rp{{ number_format($item->produk->harga_final * $item->jumlah, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t border-dashed border-gray-200 my-4"></div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Total Harga</span>
                            <span class="font-bold text-gray-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 items-center">
                            <span>Ongkos Kirim</span>
                            <div class="text-right">
                                <span id="ongkir-display-asli" class="text-xs text-gray-400 line-through mr-1 hidden">Rp5.000</span>
                                <span id="ongkir-display-final" class="font-bold text-gray-800">Rp{{ number_format($ongkirKurir, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div id="diskon-badge" class="hidden justify-between text-green-600 text-xs mt-1 bg-green-50 p-1.5 rounded">
                            <span class="font-bold"><i class="fa-solid fa-star mr-1"></i>Gratis Ongkir (Gold)</span>
                            <span class="font-bold">-Rp5.000</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 my-4"></div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="font-bold text-lg text-gray-800">Total Tagihan</span>
                        <span class="font-extrabold text-2xl text-blue-600" id="total-tagihan">Rp{{ number_format($subtotal + $ongkirKurir, 0, ',', '.') }}</span>
                    </div>

                    <button id="pay-button" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-blue-700 transition active:scale-95 flex justify-center items-center gap-2">
                        <span>Bayar Sekarang</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- VARIABEL DARI LARAVEL ---
            const subtotal = {{ $subtotal }};
            const ongkirKurirSetting = {{ $ongkirKurir }}; // 0 (Gold) atau 5000
            const isGoldMember = {{ Auth::user()->membership == 'Gold' ? 'true' : 'false' }};
            const userAddresses = @json($daftarAlamat ?? []); 
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // --- ELEMEN DOM ---
            const addressSection = document.getElementById('address-section');
            const pickupSection = document.getElementById('pickup-section'); // ELEMEN BARU
            const ongkirDisplayFinal = document.getElementById('ongkir-display-final');
            const totalTagihanEl = document.getElementById('total-tagihan');
            const payButton = document.getElementById('pay-button');

            // --- FUNGSI FORMAT RUPIAH ---
            function formatRupiah(angka) {
                return 'Rp' + new Intl.NumberFormat('id-ID').format(angka);
            }

            // --- FUNGSI TOAST ---
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast-notification');
                const msg = document.getElementById('toast-message');
                const iconContainer = document.getElementById('toast-icon-container');
                if(!toast) return;
                msg.innerText = message;
                toast.classList.remove('hidden', 'border-green-500', 'border-red-500');
                if (type === 'success') {
                    toast.classList.add('border-green-500');
                    if(iconContainer) iconContainer.innerHTML = '<i class="fa-solid fa-check text-green-500 text-xl"></i>';
                } else {
                    toast.classList.add('border-red-500');
                    if(iconContainer) iconContainer.innerHTML = '<i class="fa-solid fa-xmark text-red-500 text-xl"></i>';
                }
                toast.classList.add('toast-enter');
                setTimeout(() => toast.classList.add('hidden'), 3000);
            }

            // --- UPDATE ADDRESS UI ---
            function getSelectedAddress() {
                const navbarLabelElement = document.getElementById('current-address-label');
                let foundAddr = null;
                if (navbarLabelElement && userAddresses.length > 0) {
                    const navbarText = navbarLabelElement.innerText.trim();
                    foundAddr = userAddresses.find(addr => (addr.label + ' (' + addr.penerima + ')') === navbarText);
                }
                if (!foundAddr && userAddresses.length > 0) foundAddr = userAddresses[0];
                return foundAddr;
            }

            function renderAddressUI() {
                const addr = getSelectedAddress();
                const lbl = document.getElementById('selected-address-label');
                const det = document.getElementById('selected-address-detail');
                if (addr) {
                    lbl.innerText = addr.label + ' (' + addr.penerima + ')';
                    det.innerText = addr.detail_alamat + ', ' + addr.no_hp_penerima;
                } else {
                    lbl.innerText = "Belum ada alamat";
                    det.innerText = "Silakan tambah alamat di menu profil";
                }
            }

            // --- FUNGSI GANTI PENGIRIMAN (UI STABIL) ---
            function updateShippingUI(type) {
                let ongkirSaatIni = 0;
                const ongkirAsliEl = document.getElementById('ongkir-display-asli');
                const diskonBadge = document.getElementById('diskon-badge');

                if (type === 'delivery') {
                    // Tampilkan Box Alamat, Sembunyikan Box Pickup
                    addressSection.classList.remove('hidden');
                    pickupSection.classList.add('hidden');
                    
                    ongkirSaatIni = ongkirKurirSetting;

                    // UI Diskon Gold
                    if (isGoldMember) {
                        if(ongkirAsliEl) { ongkirAsliEl.classList.remove('hidden'); ongkirAsliEl.innerText = "Rp5.000"; }
                        if(diskonBadge) { diskonBadge.classList.remove('hidden'); diskonBadge.classList.add('flex'); }
                    } else {
                        if(ongkirAsliEl) ongkirAsliEl.classList.add('hidden');
                        if(diskonBadge) { diskonBadge.classList.add('hidden'); diskonBadge.classList.remove('flex'); }
                    }
                } else {
                    // Sembunyikan Box Alamat, Tampilkan Box Pickup (MENGISI RUANG KOSONG)
                    addressSection.classList.add('hidden');
                    pickupSection.classList.remove('hidden');
                    
                    ongkirSaatIni = 0;

                    if(ongkirAsliEl) ongkirAsliEl.classList.add('hidden');
                    if(diskonBadge) { diskonBadge.classList.add('hidden'); diskonBadge.classList.remove('flex'); }
                }

                // Update Total
                ongkirDisplayFinal.innerText = (ongkirSaatIni === 0) ? 'Gratis' : formatRupiah(ongkirSaatIni);
                totalTagihanEl.innerText = formatRupiah(subtotal + ongkirSaatIni);
            }

            // --- EVENT LISTENERS ---
            const radioButtons = document.querySelectorAll('input[name="tipe_pengiriman"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateShippingUI(this.value);
                });
            });

            // Init
            renderAddressUI();
            const checkedRadio = document.querySelector('input[name="tipe_pengiriman"]:checked');
            if(checkedRadio) updateShippingUI(checkedRadio.value);
            else updateShippingUI('delivery');

            const addressBtn = document.getElementById('address-dropdown');
            if(addressBtn) addressBtn.addEventListener('click', () => setTimeout(renderAddressUI, 500));

            // --- LOGIKA TOMBOL BAYAR ---
            if(payButton) {
                payButton.addEventListener('click', async function(e) {
                    e.preventDefault();

                    const tipePengirimanEl = document.querySelector('input[name="tipe_pengiriman"]:checked');
                    const metodePembayaranEl = document.querySelector('input[name="metode_pembayaran"]:checked');

                    if(!tipePengirimanEl || !metodePembayaranEl) {
                        showToast("Pilih pengiriman & pembayaran!", "error"); return;
                    }

                    const tipePengiriman = tipePengirimanEl.value;
                    const metodePembayaran = metodePembayaranEl.value;
                    let idAlamatFinal = null;

                    if (tipePengiriman === 'delivery') {
                        const addr = getSelectedAddress();
                        if (!addr) { showToast("Alamat pengiriman belum dipilih!", "error"); return; }
                        idAlamatFinal = addr.id_alamat;
                    } else {
                        idAlamatFinal = null;
                    }

                    const originalText = payButton.innerHTML;
                    payButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
                    payButton.disabled = true;

                    try {
                        const response = await fetch("{{ route('kiosk.pay') }}", {
                            method: "POST",
                            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                            body: JSON.stringify({
                                metode_pembayaran: metodePembayaran,
                                tipe_pengiriman: tipePengiriman,
                                id_alamat: idAlamatFinal
                            })
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.error || "Gagal memproses pesanan.");

                        if (metodePembayaran === 'Tunai') {
                            showToast("Pesanan Berhasil!", "success");
                            setTimeout(() => window.location.href = data.redirect_url, 1500);
                        } else {
                            window.snap.pay(data.snap_token, {
                                onSuccess: function(result) {
                                    fetch("{{ route('kiosk.midtrans.success') }}", {
                                        method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                                        body: JSON.stringify({ result_data: result, id_alamat: idAlamatFinal })
                                    }).then(async res => {
                                        const finalData = await res.json();
                                        if (res.ok) window.location.href = "{{ route('kiosk.success', ':id') }}".replace(':id', finalData.id_transaksi);
                                    });
                                },
                                onPending: () => { showToast("Menunggu pembayaran...", "success"); setTimeout(() => window.location.reload(), 2000); },
                                onError: () => { showToast("Pembayaran Gagal!", "error"); setTimeout(() => window.location.reload(), 2000); },
                                onClose: () => { payButton.innerHTML = originalText; payButton.disabled = false; }
                            });
                        }
                    } catch (err) {
                        console.error(err);
                        showToast(err.message, "error");
                        payButton.innerHTML = originalText;
                        payButton.disabled = false;
                    }
                });
            }
        });
    </script>
</body>
</html>