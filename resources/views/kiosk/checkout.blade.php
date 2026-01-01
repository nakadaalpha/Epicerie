<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keranjang Belanja - Épicerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
        body { font-family: 'Nunito', sans-serif; }
        .custom-checkbox { accent-color: #2563eb; width: 1.2rem; height: 1.2rem; cursor: pointer; border-radius: 4px; }
        .payment-option { transition: all 0.2s; border: 1px solid #e5e7eb; }
        .payment-option:hover { border-color: #3b82f6; background-color: #eff6ff; }
        .payment-option input:checked+div { border-color: #2563eb; background-color: #eff6ff; }
        .payment-option input:checked+div .check-icon { display: block; }
        @keyframes slideInDown { from { transform: translate(-50%, -100%); opacity: 0; } to { transform: translate(-50%, 0); opacity: 1; } }
        .toast-enter { animation: slideInDown 0.4s ease-out forwards; }
    </style>
</head>

<body class="bg-gray-50 text-gray-700 pb-20">

    @include('partials.navbar-kiosk')

    <div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[999] hidden flex items-center w-full max-w-sm p-4 space-x-4 text-gray-500 bg-white rounded-xl shadow-2xl border-l-4 transition-all duration-300" role="alert">
        <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
            <i id="toast-icon" class="fa-solid text-lg"></i>
        </div>
        <div class="ml-3 text-sm font-bold text-gray-800" id="toast-message">Pesan Notifikasi</div>
    </div>

    <div id="confirm-modal" class="fixed inset-0 z-[999] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 transition-transform">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-50 mb-4">
                    <i class="fa-solid fa-trash-can text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Kosongkan Keranjang?</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">Semua barang akan dihapus. Tindakan ini tidak bisa dibatalkan.</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeModal()" class="w-full justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="button" id="confirm-btn" class="w-full justify-center rounded-lg shadow-sm px-4 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 transition">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <div class="max-w-[1100px] mx-auto px-4 py-8">
        <h1 class="font-bold text-3xl mb-5">Keranjang</h1>
        <form id="paymentForm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                            <span class="font-bold text-gray-700 text-base">Daftar Barang <span class="text-gray-400 font-normal">({{ count($keranjang) }})</span></span>

                            @if(count($keranjang) > 0)
                            <button type="button" onclick="openDeleteModal('{{ route('kiosk.empty') }}')" class="text-red-500 hover:text-red-700 text-sm font-bold transition flex items-center gap-1">
                                <i class="fa-regular fa-trash-can"></i> Hapus Semua
                            </button>
                            @endif
                        </div>

                        <div class="p-6 space-y-8">
                            @foreach($keranjang as $item)
                            <div class="flex gap-4 items-start group relative">
                                <div class="w-24 h-24 bg-white rounded-lg border border-gray-200 flex items-center justify-center shrink-0 p-1">
                                    @if($item->produk->gambar) <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-contain"> @else <i class="fa-solid fa-box text-gray-300 text-2xl"></i> @endif
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-between h-full">
                                    <div class="flex justify-between items-start gap-4">
                                        <h3 class="text-gray-700 font-medium text-sm line-clamp-2 pt-1">{{ $item->produk->nama_produk }}</h3>
                                        <span class="block font-bold text-gray-900 text-base">Rp{{ number_format($item->produk->harga_produk, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="h-4"></div>
                                    <div class="flex justify-end items-center gap-4 mt-auto">
                                        <a href="{{ route('kiosk.remove', $item->id_produk) }}"
                                            class="text-gray-400 hover:text-red-500 transition p-2"
                                            onclick="return confirm('Hapus item ini?')">
                                            <i class="fa-regular fa-trash-can text-lg"></i>
                                        </a>

                                        <div class="flex items-center border border-gray-300 rounded-full h-9 w-[120px] bg-white overflow-hidden">
                                            <a href="{{ route('kiosk.decrease', $item->id_produk) }}"
                                                class="w-10 h-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-blue-600 transition border-r border-gray-100">
                                                <i class="fa-solid fa-minus text-xs"></i>
                                            </a>
                                            <input type="text" value="{{ $item->jumlah }}"
                                                class="flex-1 w-full text-center text-sm font-bold text-gray-700 border-none focus:ring-0 bg-transparent p-0 cursor-default"
                                                readonly>
                                            <a href="{{ route('kiosk.increase', $item->id_produk) }}"
                                                class="w-10 h-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-blue-600 transition border-l border-gray-100">
                                                <i class="fa-solid fa-plus text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!$loop->last) <div class="border-b border-gray-100 w-full"></div> @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-white p-6 rounded-xl sticky top-24 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-5 text-base">Ringkasan belanja</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex gap-3 items-start">
                                <div class="mt-0.5"><i class="fa-solid fa-motorcycle text-blue-600"></i></div>
                                <div class="text-sm text-blue-800">
                                    <span class="font-bold">Info Pengiriman Kurir Toko:</span>
                                    <ul class="list-disc list-inside mt-1 space-y-1 text-xs">
                                        <li>Tarif Flat <span class="font-bold">Rp 5.000</span>.</li>
                                        <li>Jarak maks <span class="font-bold">3 KM</span> dari toko.</li>
                                        <li>Min. Belanja <span class="font-bold">Rp 30.000</span>.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-yellow-800 mb-2 font-bold">⚠️ Wajib Cek Sebelum Bayar</p>
                            <a id="btn-cek-jarak" href="#" target="_blank" class="flex items-center justify-center gap-2 bg-white border border-yellow-300 text-yellow-700 text-xs font-bold py-2 px-4 rounded-lg hover:bg-yellow-100 transition shadow-sm">
                                <i class="fa-solid fa-map-location-dot"></i> Lihat Jarak di Google Maps
                            </a>
                            <p class="text-[10px] text-red-500 mt-2 leading-tight">
                                *Jika jarak di peta menunjukkan lebih dari <b>3 KM</b>, mohon jangan lakukan pembayaran karena pesanan akan ditolak.
                            </p>
                        </div>

                        <div class="space-y-2 mb-5">
                            <label class="block cursor-pointer payment-option rounded-lg relative">
                                <input type="radio" name="metode_pembayaran" value="Tunai" class="peer sr-only" checked>
                                <div class="p-3 rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-2"><i class="fa-solid fa-money-bill text-green-500"></i><span class="text-sm font-bold text-gray-700">Tunai (COD)</span></div><i class="fa-solid fa-circle-check text-blue-600 hidden check-icon"></i>
                                </div>
                            </label>
                            <label class="block cursor-pointer payment-option rounded-lg relative">
                                <input type="radio" name="metode_pembayaran" value="QRIS" class="peer sr-only">
                                <div class="p-3 rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-2"><i class="fa-solid fa-credit-card text-blue-500"></i><span class="text-sm font-bold text-gray-700">QRIS/Transfer Bank</span></div><i class="fa-solid fa-circle-check text-blue-600 hidden check-icon"></i>
                                </div>
                            </label>
                        </div>
                        
                        <input type="hidden" name="pengiriman" value="0">
                        
                        <div class="space-y-2 mb-4 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal Produk</span>
                                <span class="font-bold">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Biaya Pengiriman</span>
                                <span class="font-bold text-gray-800">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 my-4"></div>

                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-lg text-gray-800">Total Bayar</span>
                            <span class="font-extrabold text-xl text-blue-600">Rp{{ number_format($totalBayar, 0, ',', '.') }}</span>
                        </div>
                        
                        @if($subtotal < $minBelanja)
                            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-xs font-bold text-center mb-3 border border-red-200">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                Minimal belanja Rp{{ number_format($minBelanja, 0, ',', '.') }} <br>
                                (Kurang Rp{{ number_format($minBelanja - $subtotal, 0, ',', '.') }})
                            </div>
                            <button type="button" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3.5 rounded-xl cursor-not-allowed flex justify-center items-center gap-2">
                                <i class="fa-solid fa-lock"></i> Bayar Sekarang
                            </button>
                        @else
                            <button type="button" id="pay-button" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-blue-700 transition flex justify-center items-center gap-2">
                                <span>Bayar Sekarang</span>
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const payButton = document.getElementById('pay-button');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Ambil Data Alamat dari Controller
        const userAddresses = @json($daftarAlamat);

        // --- FUNGSI UPDATE TOMBOL CEK JARAK ---
        function updateDistanceButton() {
            const btn = document.getElementById('btn-cek-jarak');
            const navbarLabelElement = document.getElementById('current-address-label');
            let selectedAddress = null;

            // 1. Ambil Alamat yang Dipilih di Navbar
            if (navbarLabelElement) {
                const navbarText = navbarLabelElement.innerText.trim();
                // Cari data alamat yang cocok dengan teks di navbar
                userAddresses.forEach(addr => {
                    const formatLabel = addr.label + ' (' + addr.penerima + ')';
                    if (navbarText === formatLabel) {
                        selectedAddress = addr;
                    }
                });
            }

            // Kalau tidak ada yang dipilih (atau baru pertama buka), ambil alamat pertama (default)
            if (!selectedAddress && userAddresses.length > 0) {
                selectedAddress = userAddresses[0];
            }

            // 2. Generate Link Google Maps
            if (selectedAddress) {
                const tokoLat = "-7.73326"; 
                const tokoLng = "110.33121";
                let destinasi = "";

                if(selectedAddress.plus_code) {
                    destinasi = encodeURIComponent(selectedAddress.plus_code); // Pakai Kode Plus kalau ada
                } else {
                    destinasi = encodeURIComponent(selectedAddress.detail_alamat + " Sleman"); // Fallback ke teks
                }

                // Format Link Direction Google Maps
                // Origin = Toko, Destination = Alamat Pelanggan
                btn.href = `https://www.google.com/maps/dir/?api=1&origin=${tokoLat},${tokoLng}&destination=${destinasi}&travelmode=driving`;
                btn.classList.remove('opacity-50', 'pointer-events-none');
                btn.innerHTML = `<i class="fa-solid fa-map-location-dot"></i> Cek Jarak (${selectedAddress.label})`;
            } else {
                // Kalau user belum punya alamat sama sekali
                btn.href = "#";
                btn.classList.add('opacity-50', 'pointer-events-none');
                btn.innerText = "Belum ada alamat dipilih";
            }
        }

        // Jalankan saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateDistanceButton();
            
            // Tambahkan listener manual untuk mendeteksi perubahan alamat di navbar
            // Karena fungsi selectAddress ada di file navbar (layout terpisah), kita pantau perubahannya via interval singkat atau event klik
            const addressBtn = document.getElementById('address-dropdown'); // Dropdown container
            if(addressBtn) {
                addressBtn.addEventListener('click', function() {
                    setTimeout(updateDistanceButton, 500); // Tunggu sebentar sampai teks navbar berubah
                });
            }
        });


        // --- FUNGSI MODAL & TOAST (BAWAAN) ---
        const modal = document.getElementById('confirm-modal');
        const confirmBtn = document.getElementById('confirm-btn');

        function openDeleteModal(url) {
            modal.classList.remove('hidden');
            confirmBtn.onclick = function() {
                window.location.href = url;
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msg = document.getElementById('toast-message');
            const iconContainer = document.getElementById('toast-icon-container');
            const icon = document.getElementById('toast-icon');

            msg.innerText = message;
            toast.classList.remove('hidden', 'border-green-500', 'border-red-500');
            iconContainer.classList.remove('bg-green-100', 'text-green-500', 'bg-red-100', 'text-red-500');
            icon.classList.remove('fa-circle-check', 'fa-circle-xmark');

            if (type === 'success') {
                toast.classList.add('border-green-500');
                iconContainer.classList.add('bg-green-100', 'text-green-500');
                icon.classList.add('fa-circle-check');
            } else {
                toast.classList.add('border-red-500');
                iconContainer.classList.add('bg-red-100', 'text-red-500');
                icon.classList.add('fa-circle-xmark');
            }

            toast.classList.add('toast-enter');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // --- LOGIKA PEMBAYARAN (SAMA SEPERTI SEBELUMNYA) ---
        if(payButton) {
            payButton.addEventListener('click', async function(e) {
                e.preventDefault();
                
                // Cari ID Alamat yang dipilih
                const navbarLabelElement = document.getElementById('current-address-label');
                let selectedAddressId = null;

                if (navbarLabelElement) {
                    const navbarText = navbarLabelElement.innerText.trim(); 
                    userAddresses.forEach(addr => {
                        const formatLabel = addr.label + ' (' + addr.penerima + ')';
                        if (navbarText === formatLabel) {
                            selectedAddressId = addr.id_alamat;
                        }
                    });
                }
                
                // Fallback: Kalau tidak ketemu (misal default), ambil ID alamat pertama
                if (!selectedAddressId && userAddresses.length > 0) {
                    selectedAddressId = userAddresses[0].id_alamat;
                }

                const metode = document.querySelector('input[name="metode_pembayaran"]:checked').value;
                payButton.innerText = "Memproses...";
                payButton.disabled = true;

                try {
                    const response = await fetch("{{ route('kiosk.pay') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken
                        },
                        body: JSON.stringify({
                            metode_pembayaran: metode,
                            pengiriman: 0,
                            id_alamat: selectedAddressId 
                        })
                    });
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.error || "Gagal");

                    if (metode === 'Tunai') {
                        showToast("Pembayaran Tunai Berhasil!", "success");
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1500);
                    } else {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                payButton.innerText = "Menyimpan Data...";
                                fetch("{{ route('kiosk.midtrans.success') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": csrfToken
                                    },
                                    body: JSON.stringify({
                                        result_data: result,
                                        id_alamat: selectedAddressId 
                                    })
                                }).then(async res => {
                                    const finalData = await res.json();

                                    if (res.ok && finalData.status === 'success') {
                                        showToast("Sukses! Keranjang Kosong.", "success");
                                        setTimeout(() => {
                                            let successUrl = "{{ route('kiosk.success', ':id') }}";
                                            successUrl = successUrl.replace(':id', finalData.id_transaksi);
                                            window.location.href = successUrl;
                                        }, 1500);
                                    } else {
                                        showToast("DB ERROR: " + finalData.message, "error");
                                        payButton.disabled = false;
                                    }
                                });
                            },
                            onPending: function() {
                                showToast("Menunggu pembayaran...", "success");
                                setTimeout(() => window.location.reload(), 2000);
                            },
                            onError: function() {
                                showToast("Pembayaran Gagal!", "error");
                                setTimeout(() => window.location.reload(), 2000);
                            },
                            onClose: function() {
                                payButton.innerText = "Bayar Sekarang";
                                payButton.disabled = false;
                            }
                        });
                    }
                } catch (err) {
                    showToast("Error: " + err.message, "error");
                    payButton.disabled = false;
                    payButton.innerText = "Bayar Sekarang";
                }
            });
        }
    </script>
</body>

</html>