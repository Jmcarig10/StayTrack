<nav class="bg-white border-b border-slate-200">

    <div class="max-w-7xl mx-auto px-6">

        <div class="flex items-center justify-between h-16">

            {{-- LOGO --}}

            <a
                href="{{ url('/dashboard') }}"
                class="flex items-center gap-3"
            >

                <div
                    class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-bold"
                >
                    S
                </div>

                <div>

                    <div class="font-bold text-slate-800">
                        StayTrack
                    </div>

                    <div class="text-xs text-slate-500">
                        Apartment Management
                    </div>

                </div>

            </a>


            {{-- NAVIGATION --}}

            <div class="hidden md:flex items-center gap-1">

    <a
        href="{{ route('dashboard') }}"
        class="px-3 py-2 rounded-lg text-sm font-medium
        {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
    >
        Dashboard
    </a>


    @if(auth()->user()->role === 'admin')

        <a
            href="{{ route('apartments.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('apartments.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            Apartments
        </a>

        <a
            href="{{ route('tenants.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('tenants.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            Tenants
        </a>

        <a
            href="{{ route('leases.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('leases.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            Leases
        </a>

        <a
            href="{{ route('bills.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('bills.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            Bills
        </a>

        <a
            href="{{ route('payments.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('payments.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            Payments
        </a>

    @else

        <a
            href="{{ route('bills.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('bills.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            My Bills
        </a>

        <a
            href="{{ route('payments.index') }}"
            class="px-3 py-2 rounded-lg text-sm font-medium
            {{ request()->routeIs('payments.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            My Payments
        </a>

    @endif

</div>

</nav>