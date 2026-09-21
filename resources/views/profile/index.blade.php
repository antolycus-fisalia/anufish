<x-layouts.app title="Profil Pengguna">

    <div class="mb-6">

        <h2 class="text-3xl font-bold">
            Profil Pengguna
        </h2>

        <p class="mt-1 text-anufish-muted">
            Lihat dan ubah informasi akun Anda.
        </p>

    </div>

    <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">

        <x-profile.summary :user="$user" />

        <x-profile.form :user="$user" />

    </div>

</x-layouts.app>