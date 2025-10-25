<?php

namespace App\Http\Controllers;

use App\Enums\TransactionCategory;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $query = $user->transactions();

        // Handle sorting
        $sort = $request->get('sort', 'date');
        $direction = $request->get('direction', 'desc');

        $allowedSorts = ['date', 'type', 'category', 'amount'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }

        $transactions = $query->paginate(15);
        $currency = $user->currency;

        return view('transactions.index', compact('transactions', 'sort', 'direction', 'currency'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = TransactionCategory::cases();
        return view('transactions.create', compact('categories'));
    }

    /**
     * Export transactions to CSV
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $transactions = $user->transactions()->orderBy('date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Date',
                'Type',
                'Category',
                'Amount',
            ]);

            // Add data rows
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->type,
                    $transaction->category->value,
                    number_format($transaction->amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $transaction = $user->transactions()->create($request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);
        $categories = TransactionCategory::cases();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
