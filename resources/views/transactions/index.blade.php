<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transactions') }}
            </h2>
            <div class="space-x-4">
                <a href="{{ route('transactions.export') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Add Transaction') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                        <a href="{{ route('transactions.index', ['sort' => 'date', 'direction' => $sort === 'date' && $direction === 'asc' ? 'desc' : 'asc']) }}" class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100">
                                            {{ __('Date') }}
                                            @if ($sort === 'date')
                                                <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                        <a href="{{ route('transactions.index', ['sort' => 'type', 'direction' => $sort === 'type' && $direction === 'asc' ? 'desc' : 'asc']) }}" class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100">
                                            {{ __('Type') }}
                                            @if ($sort === 'type')
                                                <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                        <a href="{{ route('transactions.index', ['sort' => 'category', 'direction' => $sort === 'category' && $direction === 'asc' ? 'desc' : 'asc']) }}" class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100">
                                            {{ __('Category') }}
                                            @if ($sort === 'category')
                                                <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right">
                                        <a href="{{ route('transactions.index', ['sort' => 'amount', 'direction' => $sort === 'amount' && $direction === 'asc' ? 'desc' : 'asc']) }}" class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hover:text-gray-700 dark:hover:text-gray-100">
                                            {{ __('Amount') }}
                                            @if ($sort === 'amount')
                                                <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ $transaction->date->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                            {{ __(ucfirst($transaction->type)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            {{ __($transaction->category->value) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $transaction->amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($transaction->amount, 2) }} {{ $currency }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">{{ __('Edit') }}</a>
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline ml-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200" onclick="return confirm('{{ __('Are you sure you want to delete this transaction?') }}')">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No transactions found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
