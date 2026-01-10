@extends('layouts.admin')

@section('title', 'Kasir Point of Sale')
@section('header_title', 'Kasir')

@section('content')

{{-- 1. Load Library QR Scanner --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="h-[calc(100vh-140px)] flex flex-col md:flex-row gap-6">

    <div class="w-full md:w-2/3 flex flex-col h-full">
        {{-- ... (Code Bagian Kiri Sama Seperti Sebelumnya) ... --}}

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-4 flex gap-4">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari produk..."
                    class="w-full pl-10 p-3 rounded-xl bg-gray-50 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>
            <select id="categoryFilter" class="p-3 rounded-xl bg-gray-50 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="all">Semua Kategori</option>
                @foreach($kategori as $kat)
                <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 overflow-y-auto no-scrollbar pr-2">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="productGrid">
                @foreach($produk as $p)
                <div class="product-card bg-white p-3 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-300 transition cursor-pointer group h-full flex flex-col"
                    data-id="{{ $p->id_produk }}"
                    data-name="{{ $p->nama_produk }}"
                    data-price="{{ $p->harga_produk }}"
                    data-image="{{ $p->gambar ? asset('storage/'.$p->gambar) : '' }}"
                    data-stock="{{ $p->stok }}"
                    data-category="{{ $p->id_kategori }}"
                    onclick="addToCart(this)">

                    <div class="relative w-full aspect-square bg-gray-50 rounded-xl mb-3 overflow-hidden flex items-center justify-center">
                        @if($p->gambar)
                        <img src="{{ asset('storage/'.$p->gambar) }}" class="object-cover w-full h-full group-hover:scale-110 transition duration-500">
                        @else
                        <i class="fa-solid fa-box-open text-3xl text-gray-300"></i>
                        @endif
                        <div class="absolute top-2 right-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded-full backdrop-blur-sm">
                            Stok: {{ $p->stok }}
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-800 text-sm line-clamp-2 mb-1 flex-1">{{ $p->nama_produk }}</h4>
                    <p class="text-blue-600 font-extrabold text-sm">Rp {{ number_format($p->harga_produk, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/3 bg-white rounded-[2rem] shadow-xl border border-gray-100 flex flex-col h-full overflow-hidden relative">

        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-extrabold text-xl text-gray-800">Keranjang</h3>
                <span class="text-xs text-gray-400 font-mono">{{ $kode_transaksi }}</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cari Pelanggan (No. HP)</label>

                {{-- Input Group --}}
                <div class="flex gap-2 mb-2" id="searchContainer">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-phone text-gray-400 text-sm"></i>
                        </span>
                        <input type="text" id="phoneInput"
                            class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 font-bold"
                            placeholder="Contoh: 0812..."
                            onkeypress="handleEnterSearch(event)">
                    </div>

                    <button type="button" onclick="searchByPhone()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-xl font-bold text-sm transition shadow-md">
                        Cari
                    </button>

                    {{-- TOMBOL SCAN QR --}}
                    <button type="button" onclick="openScanner()" class="bg-gray-800 hover:bg-gray-900 text-white px-3 rounded-xl transition shadow-md" title="Scan QR Member">
                        <i class="fa-solid fa-qrcode"></i>
                    </button>
                </div>

                {{-- Kartu Hasil Pencarian (Hidden by Default) --}}
                <div id="memberResultCard" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-3 flex justify-between items-center animate-fade-in">
                    <div>
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-0.5">Member Ditemukan</p>
                        <h4 id="resultName" class="font-bold text-gray-800 text-sm">Nama Pelanggan</h4>
                        <p id="resultPhone" class="text-xs text-gray-500">0812xxxx</p>
                    </div>
                    <div class="text-right">
                        <div id="memberBadge" class="text-[10px] font-bold px-2 py-1 rounded border bg-white mb-1">tier</div>
                        <button onclick="resetMember()" class="text-xs text-red-500 hover:text-red-700 font-bold underline">Hapus</button>
                    </div>
                </div>

                {{-- Pesan Error (Hidden by Default) --}}
                <p id="searchError" class="hidden text-xs text-red-500 font-bold mt-1 ml-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Data tidak ditemukan!
                </p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" id="cartItems">
            <div id="emptyCart" class="h-full flex flex-col items-center justify-center text-gray-300 opacity-50">
                <i class="fa-solid fa-basket-shopping text-6xl mb-4"></i>
                <p class="text-sm font-bold">Keranjang Kosong</p>
            </div>
        </div>

        <div class="p-6 bg-white border-t border-gray-100 shadow-[0_-5px_20px_rgba(0,0,0,0.03)] z-10">
            <div class="space-y-2 mb-4 border-b border-gray-100 pb-4">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-bold text-gray-700" id="subtotalDisplay">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm text-green-600 hidden" id="discountRow">
                    <span>Diskon Member (<span id="discountRate">0%</span>)</span>
                    <span class="font-bold" id="discountDisplay">-Rp 0</span>
                </div>
                <div class="flex justify-between text-lg font-black text-gray-800 pt-2">
                    <span>Total</span>
                    <span class="text-blue-600" id="totalDisplay">Rp 0</span>
                </div>
            </div>

            <form action="{{ route('transaksi.store') }}" method="POST" id="checkoutForm">
                @csrf
                <input type="hidden" name="kode_transaksi" value="{{ $kode_transaksi }}">
                <input type="hidden" name="cart_data" id="cartDataInput">
                <input type="hidden" name="total_bayar" id="totalBayarInput">
                <input type="hidden" name="id_user" id="idUserInput">

                <div class="mb-4" id="manualCustomerInput">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nama Pelanggan (Umum)</label>
                    <input type="text" name="nama_pelanggan" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Masukkan nama...">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Uang Diterima</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500 text-sm font-bold">Rp</span>
                        <input type="number" name="bayar_diterima" id="bayarInput" class="w-full pl-9 bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0" required>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs font-bold text-gray-500 mb-4 px-1">
                    <span id="labelKembalian">Kembalian:</span>
                    <span class="text-green-600 text-base" id="kembalianDisplay">Rp 0</span>
                </div>

                <button type="submit" onclick="return validateCheckout()" class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition transform active:scale-95 flex justify-center items-center gap-2">
                    <i class="fa-solid fa-print"></i> Proses Transaksi
                </button>
            </form>
        </div>
    </div>
</div>

{{-- MODAL SCANNER QR --}}
<div id="scannerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900/80 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-3xl shadow-2xl w-full max-w-md relative">
        <button onclick="closeScanner()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500">
            <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Scan QR Member</h3>

        <div id="reader" class="w-full rounded-xl overflow-hidden bg-black"></div>

        <p class="text-center text-sm text-gray-400 mt-4">Arahkan kamera ke QR Code pelanggan.</p>
    </div>
</div>

<script>
    // --- 1. SETUP VARIABLES & FORMATTER ---
    const DISCOUNTS = {
        'Gold': 0.10,
        'Silver': 0.10,
        'Bronze': 0.05,
        'Classic': 0
    };

    let cart = [];
    let currentDiscountRate = 0;
    const allCustomers = @json($pelanggan);

    // DOM Elements
    const cartItemsContainer = document.getElementById('cartItems');
    const emptyCartMsg = document.getElementById('emptyCart');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    const discountRow = document.getElementById('discountRow');
    const discountRateEl = document.getElementById('discountRate');
    const discountDisplay = document.getElementById('discountDisplay');

    // Checkout Elements
    const idUserInput = document.getElementById('idUserInput');
    const manualCustomerInput = document.getElementById('manualCustomerInput');
    const bayarInput = document.getElementById('bayarInput');

    // Display Kembalian / Kurang (UPDATED)
    const kembalianDisplay = document.getElementById('kembalianDisplay');
    const labelKembalian = document.getElementById('labelKembalian');

    const cartDataInput = document.getElementById('cartDataInput');
    const totalBayarInput = document.getElementById('totalBayarInput');

    // Search Elements
    const phoneInput = document.getElementById('phoneInput');
    const memberResultCard = document.getElementById('memberResultCard');
    const searchContainer = document.getElementById('searchContainer');
    const searchError = document.getElementById('searchError');
    const resultName = document.getElementById('resultName');
    const resultPhone = document.getElementById('resultPhone');
    const memberBadge = document.getElementById('memberBadge');

    // Formatter Rupiah
    const formatRupiah = (number) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);


    // --- 2. LOGIC PENCARIAN MEMBER ---

    function selectMember(user) {
        const tier = user.membership;
        currentDiscountRate = DISCOUNTS[tier] || 0;

        resultName.innerText = user.nama;
        resultPhone.innerText = user.no_hp || '-';
        memberBadge.className = `text-[10px] font-bold px-2 py-1 rounded border ${user.membership_color || 'bg-gray-100'}`;
        memberBadge.innerText = `${tier} (${(currentDiscountRate * 100)}%)`;

        searchContainer.classList.add('hidden');
        memberResultCard.classList.remove('hidden');
        manualCustomerInput.classList.add('hidden');
        searchError.classList.add('hidden');

        idUserInput.value = user.id_user;
        renderCart();
    }

    function resetMember() {
        currentDiscountRate = 0;
        idUserInput.value = '';

        searchContainer.classList.remove('hidden');
        memberResultCard.classList.add('hidden');
        manualCustomerInput.classList.remove('hidden');
        searchError.classList.add('hidden');
        if (phoneInput) phoneInput.value = '';

        renderCart();
    }

    function searchByPhone() {
        const query = phoneInput.value.trim();
        if (!query) return;
        const found = allCustomers.find(c => c.no_hp === query || (c.no_hp && c.no_hp.includes(query)));

        if (found) {
            selectMember(found);
        } else {
            searchError.classList.remove('hidden');
            setTimeout(() => searchError.classList.add('hidden'), 3000);
        }
    }

    function handleEnterSearch(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchByPhone();
        }
    }


    // --- 3. LOGIC CART & KALKULASI ---

    window.addToCart = (el) => {
        const id = el.dataset.id;
        const name = el.dataset.name;
        const price = parseInt(el.dataset.price);
        const stock = parseInt(el.dataset.stock);
        const image = el.dataset.image;

        if (stock <= 0) {
            alert('Stok Habis!');
            return;
        }

        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            if (existingItem.qty < stock) existingItem.qty++;
            else alert('Maksimal stok tercapai!');
        } else {
            cart.push({
                id,
                name,
                price,
                qty: 1,
                stock,
                image
            });
        }
        renderCart();
    }

    window.updateQty = (id, change) => {
        const item = cart.find(item => item.id === id);
        if (item) {
            const newQty = item.qty + change;
            if (newQty > 0 && newQty <= item.stock) item.qty = newQty;
        }
        renderCart();
    }

    window.removeFromCart = (id) => {
        cart = cart.filter(item => item.id !== id);
        renderCart();
    }

    const renderCart = () => {
        cartItemsContainer.innerHTML = '';
        let subtotal = 0;

        if (cart.length === 0) {
            cartItemsContainer.appendChild(emptyCartMsg);
            emptyCartMsg.classList.remove('hidden');
        } else {
            emptyCartMsg.classList.add('hidden');
            cart.forEach(item => {
                subtotal += item.price * item.qty;
                const itemEl = document.createElement('div');
                itemEl.className = 'flex items-center gap-3 bg-gray-50 p-2 rounded-xl border border-gray-100';
                itemEl.innerHTML = `
                    <div class="w-12 h-12 bg-white rounded-lg border border-gray-200 overflow-hidden flex-shrink-0">
                        ${item.image ? `<img src="${item.image}" class="w-full h-full object-cover">` : `<div class="w-full h-full flex items-center text-gray-300"><i class="fa-solid fa-box"></i></div>`}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-bold text-gray-800 text-sm truncate">${item.name}</h5>
                        <p class="text-xs text-blue-600 font-bold">${formatRupiah(item.price)}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQty('${item.id}', -1)" class="w-6 h-6 rounded-full bg-white border text-gray-500 hover:text-red-500 flex items-center justify-center"><i class="fa-solid fa-minus text-[10px]"></i></button>
                        <span class="text-sm font-bold w-4 text-center">${item.qty}</span>
                        <button onclick="updateQty('${item.id}', 1)" class="w-6 h-6 rounded-full bg-white border text-gray-500 hover:text-blue-500 flex items-center justify-center"><i class="fa-solid fa-plus text-[10px]"></i></button>
                    </div>
                    <button onclick="removeFromCart('${item.id}')" class="text-gray-300 hover:text-red-500 ml-1"><i class="fa-solid fa-trash text-xs"></i></button>
                `;
                cartItemsContainer.appendChild(itemEl);
            });
        }

        // Kalkulasi Total
        const discountAmount = subtotal * currentDiscountRate;
        const finalTotal = subtotal - discountAmount;

        subtotalDisplay.innerText = formatRupiah(subtotal);
        if (currentDiscountRate > 0) {
            discountRow.classList.remove('hidden');
            discountRateEl.innerText = (currentDiscountRate * 100) + '%';
            discountDisplay.innerText = '-' + formatRupiah(discountAmount);
        } else {
            discountRow.classList.add('hidden');
        }
        totalDisplay.innerText = formatRupiah(finalTotal);

        cartDataInput.value = JSON.stringify(cart);
        totalBayarInput.value = finalTotal;

        calculateChange(finalTotal);
    }

    // --- FITUR BARU: KALKULASI MINUS/KEMBALIAN ---
    const calculateChange = (total) => {
        const bayar = parseInt(bayarInput.value || 0);

        // Jika belum ada item di keranjang
        if (total === 0) {
            kembalianDisplay.innerText = 'Rp 0';
            kembalianDisplay.classList.remove('text-red-500', 'text-green-600');
            return;
        }

        const selisih = bayar - total;

        // Tampilkan nominal (Format rupiah otomatis menangani minus "-Rp 5.000")
        kembalianDisplay.innerText = formatRupiah(selisih);

        if (selisih >= 0) {
            // UANG CUKUP / LEBIH
            labelKembalian.innerText = "Kembalian:";
            labelKembalian.classList.remove('text-red-500');

            kembalianDisplay.classList.remove('text-red-500');
            kembalianDisplay.classList.add('text-green-600');
        } else {
            // UANG KURANG
            labelKembalian.innerText = "Kurang:";
            labelKembalian.classList.add('text-red-500'); // Label jadi merah agar warning

            kembalianDisplay.classList.remove('text-green-600');
            kembalianDisplay.classList.add('text-red-500'); // Nominal jadi merah
        }
    }

    bayarInput.addEventListener('input', () => calculateChange(parseInt(totalBayarInput.value || 0)));

    window.validateCheckout = () => {
        if (cart.length === 0) {
            alert('Keranjang kosong!');
            return false;
        }
        const total = parseInt(totalBayarInput.value);
        const bayar = parseInt(bayarInput.value || 0);

        if (bayar < total) {
            const kurang = formatRupiah(total - bayar);
            alert(`Uang pembayaran kurang ${kurang}!`); // Alert lebih informatif
            return false;
        }
        return confirm('Proses transaksi?');
    }


    // --- 4. LOGIC QR CODE SCANNER ---

    let html5QrcodeScanner = null;

    function openScanner() {
        document.getElementById('scannerModal').classList.remove('hidden');
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("reader");
        }
        html5QrcodeScanner.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            },
            (decodedText, decodedResult) => {
                console.log(`Scan result: ${decodedText}`);
                handleScanSuccess(decodedText);
            },
            (errorMessage) => {}
        ).catch(err => {
            console.log("Error starting scanner", err);
            alert("Gagal membuka kamera.");
        });
    }

    function closeScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('scannerModal').classList.add('hidden');
            }).catch(err => {
                document.getElementById('scannerModal').classList.add('hidden');
            });
        } else {
            document.getElementById('scannerModal').classList.add('hidden');
        }
    }

    function handleScanSuccess(userId) {
        const found = allCustomers.find(c => c.id_user == userId);
        if (found) {
            selectMember(found);
            closeScanner();
        } else {
            alert("QR Code tidak terdaftar!");
        }
    }

    // --- 5. SEARCH PRODUCT ---
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');

    const filterProducts = () => {
        const keyword = searchInput.value.toLowerCase();
        const category = categoryFilter.value;
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const catId = card.dataset.category;
            const matchSearch = name.includes(keyword);
            const matchCat = category === 'all' || category === catId;
            if (matchSearch && matchCat) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
</script>
@endsection