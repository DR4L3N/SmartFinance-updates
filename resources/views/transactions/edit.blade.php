@php
    use App\Enums\TransactionCategory;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="date" :value="__('Date')" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', $transaction->date->format('Y-m-d'))" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('date')" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select Type') }}</option>
                                <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                                <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div id="categoryContainer" style="display: none;">
                            <x-input-label for="category" :value="__('Category')" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach($categories as $category)
                                    @if($category->value !== 'Income')
                                        <option value="{{ $category->value }}" {{ old('category', $transaction->category?->value) == $category->value ? 'selected' : '' }} title="{{ __(TransactionCategory::getDescriptions()[$category->value] ?? '') }}">
                                            {{ __($category->value) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="categoryDescription"></p>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>
                        <input type="hidden" id="hiddenCategory" name="category" value="{{ TransactionCategory::INCOME->value }}" disabled>

                        <div>
                            <x-input-label for="amount" :value="__('Amount')" />
                            <x-text-input id="amount" name="amount" type="number" class="mt-1 block w-full" :value="old('amount', abs($transaction->amount))" required placeholder="Enter amount" />
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $transaction->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Update Transaction') }}</x-primary-button>
                            <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const categoryContainer = document.getElementById('categoryContainer');
            const categorySelect = document.getElementById('category');
            const hiddenCategory = document.getElementById('hiddenCategory');
            const descriptionElement = document.getElementById('categoryDescription');

            function handleTypeChange() {
                const isIncome = typeSelect.value === 'income';
                categoryContainer.style.display = isIncome ? 'none' : 'block';
                categorySelect.disabled = isIncome;
                hiddenCategory.disabled = !isIncome;

                if (isIncome) {
                    categorySelect.removeAttribute('required');
                    hiddenCategory.setAttribute('required', 'required');
                } else {
                    categorySelect.setAttribute('required', 'required');
                    hiddenCategory.removeAttribute('required');
                }
            }

            typeSelect.addEventListener('change', handleTypeChange);

            categorySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                descriptionElement.textContent = selectedOption.title || '';
            });

            // Initial setup
            handleTypeChange();
            if (categorySelect.value) {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                descriptionElement.textContent = selectedOption.title || '';
            }
        });
    </script>
</x-app-layout>
