@props([
    'user',
])

<x-ui.card>

    <h3 class="mb-5 text-xl font-bold">
        Edit Profil
    </h3>

    <form
        method="POST"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        class="space-y-5"
        x-data="{
            preview: null,

            previewImage(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.preview = null;
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran gambar maksimal 5 MB.');
                    event.target.value = '';
                    this.preview = null;
                    return;
                }

                this.preview = URL.createObjectURL(file);
            }
        }"
    >

        @csrf
        @method('PUT')

        <x-ui.input
            name="nama"
            label="Nama Lengkap"
            :value="old('nama', $user->nama)"
            required
        />

        <x-ui.input
            name="email"
            type="email"
            label="Email"
            :value="old('email', $user->email)"
            required
        />

        <div>
            <label class="mb-2 block text-sm font-semibold">
                Foto Profil
            </label>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                <div
                    class="
                        grid
                        h-20
                        w-20
                        place-items-center
                        overflow-hidden
                        rounded-2xl
                        bg-cyan-50
                        font-bold
                    "
                >
                    <template x-if="preview">
                        <img
                            :src="preview"
                            alt="Preview foto profil"
                            class="h-full w-full object-cover"
                        >
                    </template>

                    <template x-if="!preview">
                        <span>
                            {{ collect(explode(' ', $user->nama))
                                ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                ->take(2)
                                ->join('') }}
                        </span>
                    </template>
                </div>

                <div>
                    <input
                        type="file"
                        name="photo"
                        accept="image/png,image/jpeg,image/jpg"
                        @change="previewImage"
                        class="
                            block
                            w-full
                            text-sm
                            text-slate-600

                            file:mr-4
                            file:rounded-lg
                            file:border-0
                            file:bg-cyan-50
                            file:px-4
                            file:py-2
                            file:font-semibold
                            file:text-anufish-blue
                            hover:file:bg-cyan-100
                        "
                    >

                    <p class="mt-2 text-xs text-anufish-muted">
                        JPG, JPEG, PNG • Maksimal 5 MB
                    </p>
                </div>

            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <x-ui.button
                type="reset"
                variant="secondary"
            >
                Batal
            </x-ui.button>

            <x-ui.button type="submit">
                Simpan Perubahan
            </x-ui.button>

        </div>

    </form>

</x-ui.card>
