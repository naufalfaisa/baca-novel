<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Premium
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <x-alert type="success" class="mb-6">{{ session('status') }}</x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
            @endif

            <div class="bg-white rounded-md p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between items-center w-full">

                    @if ($active)
                        <div>
                            <p class="text-lg font-semibold">
                                Active Premium
                            </p>
                            <p class="text-sm text-gray-500">
                                Expires on {{ $active->expires_at->format('d M Y') }}
                            </p>
                        </div>

                        <div class="text-left sm:text-right">
                            <p class="text-2xl font-bold">
                                {{ now()->diffInDays($active->expires_at, false) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                days left
                            </p>
                        </div>

                    @elseif ($pendingInvoice)
                        <p class="text-gray-700">
                            You have a pending payment.
                        </p>

                        <a href="{{ $pendingInvoice->xendit_payload['invoice_url'] ?? '#' }}"
                           class="px-6 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                            Complete Payment
                        </a>

                    @else
                        <div>
                            <p class="text-lg font-semibold">
                                Upgrade to Premium
                            </p>
                            <p class="text-sm text-gray-500">
                                Ad-free access for 30 days
                            </p>
                        </div>

                        <form action="{{ route('subscription.subscribe') }}" method="post">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-md">
                                Rp 19.000 / 30 Days
                            </button>
                        </form>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
