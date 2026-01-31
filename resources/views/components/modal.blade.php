<div x-show="showModal" x-cloak class="fixed inset-0 z-9999 flex items-center justify-center bg-black/50"
    style="display: none;">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg" @click.outside="showModal = false">
        <h2 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus</h2>
        <p class="mt-2 text-sm text-gray-600">
            Apakah Anda yakin ingin menghapus data ini? Setelah dihapus, data tidak dapat dikembalikan.
        </p>

        <div class="mt-6 flex justify-end gap-3">
            <button @click="showModal = false"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <form :action="deleteUrl" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
