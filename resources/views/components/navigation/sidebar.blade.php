<aside
    class="
        hidden
        border-r
        border-slate-200
        bg-white
        p-5
        lg:block
    ">
    <div class="mb-8 text-2xl font-bold text-anufish-blue">
        Anufish
    </div>

    <nav class="space-y-1">

        <a href="#"
            class="block rounded-xl px-4 py-3 font-semibold text-slate-500 hover:bg-cyan-50 hover:text-anufish-blue">
            Dashboard
        </a>

        <a href="#"
            class="block rounded-xl px-4 py-3 font-semibold text-slate-500 hover:bg-cyan-50 hover:text-anufish-blue">
            Scan Ikan
        </a>

        <a href="#"
            class="block rounded-xl px-4 py-3 font-semibold text-slate-500 hover:bg-cyan-50 hover:text-anufish-blue">
            Riwayat Scan
        </a>

        <a href="#"
            class="block rounded-xl px-4 py-3 font-semibold text-slate-500 hover:bg-cyan-50 hover:text-anufish-blue">
            Artikel Saya
        </a>

        <a href="#" class="block rounded-xl bg-cyan-50 px-4 py-3 font-semibold text-anufish-blue">
            Profil
        </a>

        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="mt-5 block rounded-xl px-4 py-3 font-semibold text-red-600 hover:bg-red-50">
            Logout
        </a>

        <!-- Form Tersembunyi yang akan dikirim via POST -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

    </nav>
</aside>
