<x-app-layout>

    <div class="min-h-screen bg-slate-50">

        {{-- TOP HEADER --}}
        <header class="bg-white border-b border-slate-200">

            <div class="flex items-center justify-between px-6 py-4">

                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Dashboard
                    </h1>

                    <p class="text-sm text-slate-500 mt-1">
                        Welcome back, {{ auth()->user()->name }}.
                        Here's what's happening today.
                    </p>
                </div>

                <div class="flex items-center gap-4">

                    <a
                        href="{{ route('dashboard') }}"
                        aria-label="View notifications"
                        class="relative p-2 rounded-lg hover:bg-slate-100"
                    >
                        🔔

                        <span
                            class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                        ></span>
                    </a>

                    <div class="hidden sm:block text-right">

                        <p class="text-sm font-semibold text-slate-800">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ ucfirst(auth()->user()->role ?? 'User') }}
                        </p>

                    </div>

                    <div
                        class="w-10 h-10 bg-slate-800 text-white rounded-full flex items-center justify-center font-bold"
                    >
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                </div>

            </div>

        </header>


        {{-- MAIN CONTENT --}}

        <main class="p-6">

            <div class="max-w-7xl mx-auto">


                {{-- QUICK ACTIONS --}}

                <div class="flex flex-wrap gap-3 mb-8">

                    <a
                        href="{{ url('/apartments') }}"
                        class="px-4 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition"
                    >
                        + Apartment
                    </a>

                    <a
                        href="{{ url('/tenants') }}"
                        class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition"
                    >
                        + Tenant
                    </a>

                    <a
                        href="{{ url('/leases/create') }}"
                        class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition"
                    >
                        + Lease
                    </a>

                    <a
                        href="{{ url('/bills/create') }}"
                        class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition"
                    >
                        + Bill
                    </a>

                </div>


                {{-- STAT CARDS --}}

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">


                    {{-- TOTAL APARTMENTS --}}

                    <div class="bg-white rounded-2xl border border-slate-200 p-6">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500">
                                    Total Apartments
                                </p>

                                <h2 class="text-3xl font-bold text-slate-800 mt-2">
                                    {{ \App\Models\Apartment::count() }}
                                </h2>

                            </div>

                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl">
                                🏢
                            </div>

                        </div>

                        <a
                            href="{{ url('/apartments') }}"
                            class="inline-block mt-4 text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            View apartments →
                        </a>

                    </div>


                    {{-- OCCUPIED --}}

                    <div class="bg-white rounded-2xl border border-slate-200 p-6">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500">
                                    Occupied Units
                                </p>

                                <h2 class="text-3xl font-bold text-slate-800 mt-2">

                                    {{ \App\Models\Apartment::where('status', 'occupied')->count() }}

                                </h2>

                            </div>

                            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-xl">
                                🟢
                            </div>

                        </div>

                        <a
                            href="{{ url('/apartments') }}"
                            class="inline-block mt-4 text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            View units →
                        </a>

                    </div>


                    {{-- TENANTS --}}

                    <div class="bg-white rounded-2xl border border-slate-200 p-6">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500">
                                    Active Tenants
                                </p>

                                <h2 class="text-3xl font-bold text-slate-800 mt-2">

                                    {{ \App\Models\Tenant::where('status', 'active')->count() }}

                                </h2>

                            </div>

                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-xl">
                                👥
                            </div>

                        </div>

                        <a
                            href="{{ url('/tenants') }}"
                            class="inline-block mt-4 text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            View tenants →
                        </a>

                    </div>


                    {{-- BILLS --}}

                    <div class="bg-white rounded-2xl border border-slate-200 p-6">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="text-sm text-slate-500">
                                    Unpaid Bills
                                </p>

                                <h2 class="text-3xl font-bold text-slate-800 mt-2">

                                    {{ \App\Models\Bill::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->count() }}

                                </h2>

                            </div>

                            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-xl">
                                🧾
                            </div>

                        </div>

                        <a
                            href="{{ url('/bills') }}"
                            class="inline-block mt-4 text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            View bills →
                        </a>

                    </div>

                </div>


                {{-- SECOND ROW --}}

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


                    {{-- RECENT PAYMENTS --}}

                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200">

                        <div class="flex items-center justify-between px-6 py-5 border-b">

                            <div>

                                <h3 class="font-semibold text-slate-800">
                                    Recent Payments
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    Latest recorded tenant payments
                                </p>

                            </div>

                            <a
                                href="{{ url('/payments') }}"
                                class="text-sm font-medium text-slate-600 hover:text-slate-900"
                            >
                                View all →
                            </a>

                        </div>


                        <div class="divide-y">

                            @forelse(
                                \App\Models\Payment::with([
                                    'bill.lease.tenant',
                                    'bill.lease.apartment'
                                ])->latest()->take(5)->get()
                                as $payment
                            )

                                <div class="px-6 py-4 flex items-center justify-between">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center"
                                        >
                                            💳
                                        </div>

                                        <div>

                                            <p class="font-medium text-slate-800">

                                                {{ $payment->bill->lease->tenant->first_name }}
                                                {{ $payment->bill->lease->tenant->last_name }}

                                            </p>

                                            <p class="text-xs text-slate-500">

                                                Unit
                                                {{ $payment->bill->lease->apartment->unit_number }}

                                                ·

                                                {{ $payment->payment_date->format('M d, Y') }}

                                            </p>

                                        </div>

                                    </div>


                                    <div class="text-right">

                                        <p class="font-semibold text-slate-800">

                                            ${{ number_format($payment->amount_paid, 2) }}

                                        </p>

                                        <p class="text-xs text-green-600">
                                            Paid
                                        </p>

                                    </div>

                                </div>

                            @empty

                                <div class="px-6 py-10 text-center text-slate-500">

                                    No payments recorded yet.

                                </div>

                            @endforelse

                        </div>

                    </div>


                    {{-- QUICK MENU --}}

                    <div class="bg-white rounded-2xl border border-slate-200">

                        <div class="px-6 py-5 border-b">

                            <h3 class="font-semibold text-slate-800">
                                Quick Management
                            </h3>

                            <p class="text-xs text-slate-500 mt-1">
                                Manage StayTrack
                            </p>

                        </div>


                        <div class="p-4 space-y-2">

                            <a
                                href="{{ url('/apartments') }}"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50"
                            >

                                <span class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    🏢
                                </span>

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Apartments
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Manage units
                                    </p>

                                </div>

                            </a>


                            <a
                                href="{{ url('/tenants') }}"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50"
                            >

                                <span class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    👥
                                </span>

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Tenants
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Manage residents
                                    </p>

                                </div>

                            </a>


                            <a
                                href="{{ url('/leases') }}"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50"
                            >

                                <span class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    📄
                                </span>

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Leases
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Manage contracts
                                    </p>

                                </div>

                            </a>


                            <a
                                href="{{ url('/bills') }}"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50"
                            >

                                <span class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    🧾
                                </span>

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Bills
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Manage billing
                                    </p>

                                </div>

                            </a>


                            <a
                                href="{{ url('/payments') }}"
                                class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50"
                            >

                                <span class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center">
                                    💳
                                </span>

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Payments
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        Payment history
                                    </p>

                                </div>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</x-app-layout>