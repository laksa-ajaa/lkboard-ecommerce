@extends('layouts.account')

@section('title', 'Alamat Saya | ' . config('app.name'))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Alamat Saya</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola alamat pengiriman Anda</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
            + Tambah Alamat
        </button>
    </div>

    {{-- Addresses List --}}
    @if($addresses->count() > 0)
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($addresses as $address)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative" x-data="{ showEdit: false }">
            @if($address->is_default)
            <div class="absolute top-4 right-4">
                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">Default</span>
            </div>
            @endif

            <div class="pr-20">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $address->name }}</h3>
                <div class="space-y-1 text-sm text-gray-600">
                    <p>{{ $address->email }}</p>
                    <p>{{ $address->phone }}</p>
                    <p class="mt-2">{{ $address->address }}</p>
                    <p>{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                </div>
            </div>

            <div class="mt-4 flex gap-2 pt-4 border-t border-gray-200">
                @if(!$address->is_default)
                <form method="POST" action="{{ route('account.address.set-default', $address->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                        Set Default
                    </button>
                </form>
                @endif
                <button @click="openEdit(@js($address))" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-700">
                    Edit
                </button>
                <form method="POST" action="{{ route('account.address.destroy', $address->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <p class="mt-4 text-sm text-gray-600">Belum ada alamat tersimpan</p>
        <button @click="showAddModal = true" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
            Tambah Alamat Pertama
        </button>
    </div>
    @endif

    {{-- Add/Edit Address Modal --}}
    <div x-data="{ 
        showAddModal: false,
        showEditModal: false,
        editAddress: null,
        formData: {
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            province: '',
            postal_code: '',
            is_default: false
        },
        resetForm() {
            this.formData = {
                name: '',
                email: '',
                phone: '',
                address: '',
                city: '',
                province: '',
                postal_code: '',
                is_default: false
            };
            this.editAddress = null;
        },
        openEdit(address) {
            this.editAddress = address;
            this.formData = { ...address };
            this.showEditModal = true;
        }
    }" x-init="
        $watch('showAddModal', value => { if (value) resetForm(); });
        $watch('showEditModal', value => { if (value && editAddress) formData = {...editAddress}; });
    ">
        {{-- Add Modal --}}
        <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto" @click.away="showAddModal = false">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAddModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Tambah Alamat Baru</h2>
                    <form method="POST" action="{{ route('account.address.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                                <input type="text" name="name" x-model="formData.name" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" x-model="formData.email" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                                <input type="text" name="phone" x-model="formData.phone" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kota *</label>
                                <input type="text" name="city" x-model="formData.city" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi *</label>
                                <input type="text" name="province" x-model="formData.province" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos *</label>
                                <input type="text" name="postal_code" x-model="formData.postal_code" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                                <textarea name="address" x-model="formData.address" rows="3" required
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="is_default" value="1" x-model="formData.is_default"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700">Jadikan sebagai alamat default</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-2 justify-end">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                                Simpan Alamat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto" @click.away="showEditModal = false">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="showEditModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Edit Alamat</h2>
                    <template x-if="editAddress">
                        <form method="POST" :action="`{{ url('/account/address') }}/${editAddress.id}`">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                                    <input type="text" name="name" x-model="formData.name" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" name="email" x-model="formData.email" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                                    <input type="text" name="phone" x-model="formData.phone" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota *</label>
                                    <input type="text" name="city" x-model="formData.city" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi *</label>
                                    <input type="text" name="province" x-model="formData.province" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos *</label>
                                    <input type="text" name="postal_code" x-model="formData.postal_code" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                                    <textarea name="address" x-model="formData.address" rows="3" required
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="is_default" value="1" x-model="formData.is_default"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Jadikan sebagai alamat default</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-6 flex gap-2 justify-end">
                                <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                                    Update Alamat
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

