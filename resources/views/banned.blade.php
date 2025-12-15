<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-red-600">Account Banned</h2>
    </div>

    <div class="mb-4 text-sm text-gray-600">
        <p class="mb-4">Your account has been banned from EXPoints.</p>

        @if(session('ban_reason'))
            <p class="mb-4"><strong>Reason:</strong> {{ session('ban_reason') }}</p>
        @endif

        <p>If you believe this is a mistake, please contact the administrators.</p>
    </div>

    <div class="flex items-center justify-center mt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-primary-button>
                {{ __('Logout') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
