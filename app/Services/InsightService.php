<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class InsightService
{
    public function generateInsights(int $userId): array
    {
        $user = User::find($userId);
        $currency = $user?->currency ?? 'USD';

        $allInsights = [
            $this->safeExecute(fn() => $this->getCategorySpendingChange($userId, $currency)),
            $this->safeExecute(fn() => $this->getHighestSpendingCategory($userId, $currency)),
            $this->safeExecute(fn() => $this->getAverageTransactionAmount($userId, $currency)),
            $this->safeExecute(fn() => $this->getConsecutiveExpenseDays($userId)),
            $this->safeExecute(fn() => $this->getBudgetProgress($userId, $currency)),
            $this->safeExecute(fn() => $this->getWeekendSpending($userId)),
            $this->safeExecute(fn() => $this->getSavingsRate($userId)),
        ];

        $validInsights = array_filter($allInsights, fn($insight) => $insight !== null && $insight !== '');
        return empty($validInsights)
            ? []
            : $this->randomInsights($validInsights, rand(2, min(3, count($validInsights))));
    }

    private function safeExecute(callable $callback): ?string
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::warning('Insight generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function randomInsights(array $insights, int $count): array
    {
        shuffle($insights);
        return array_slice($insights, 0, min($count, count($insights)));
    }

    private function getCategorySpendingChange(int $userId, string $currency): ?string
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = $lastMonth->copy()->endOfMonth();

        $categories = [
            \App\Enums\TransactionCategory::FOOD,
            \App\Enums\TransactionCategory::TRANSPORTATION,
            \App\Enums\TransactionCategory::ENTERTAINMENT,
            \App\Enums\TransactionCategory::CLOTHING,
        ];
        $category = $categories[array_rand($categories)];
        $categoryValue = $category->value;

        $thisMonthSpending = abs(Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('category', $category)
            ->whereBetween('date', [$thisMonth, Carbon::now()])
            ->sum('amount'));

        $lastMonthSpending = abs(Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('category', $category)
            ->whereBetween('date', [$lastMonth, $lastMonthEnd])
            ->sum('amount'));

        if ($lastMonthSpending == 0 && $thisMonthSpending == 0) {
            return null;
        }

        if ($lastMonthSpending == 0 && $thisMonthSpending > 0) {
            return "You started spending on {$categoryValue} this month (" . number_format($thisMonthSpending, 2) . " {$currency}).";
        }

        $change = $lastMonthSpending > 0 ? round((($thisMonthSpending - $lastMonthSpending) / $lastMonthSpending) * 100) : 0;

        if (abs($change) < 5) return null;

        return $change > 0
            ? "You spent {$change}% more on {$categoryValue} this month compared to last month."
            : "Great job! You spent " . abs($change) . "% less on {$categoryValue} this month.";
    }

    private function getHighestSpendingCategory(int $userId, string $currency): ?string
    {
        $topCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->selectRaw('category, SUM(ABS(amount)) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        if (!$topCategory || $topCategory->total <= 0) {
            return null;
        }

        $categoryName = $topCategory->category instanceof \App\Enums\TransactionCategory
            ? $topCategory->category->value
            : $topCategory->category;

        return "Your highest spending category this month is {$categoryName} with " .
               number_format($topCategory->total, 2) . " {$currency}.";
    }

    private function getAverageTransactionAmount(int $userId, string $currency): ?string
    {
        $count = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->count();

        if ($count === 0) {
            return null;
        }

        $average = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->avg('amount');

        if (!$average || $average == 0) {
            return null;
        }

        return "Your average expense this month is " . number_format(abs($average), 2) . " {$currency} per transaction.";
    }

    private function getConsecutiveExpenseDays(int $userId): ?string
    {
        $recentExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->subDays(7), Carbon::now()])
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->count();

        if ($recentExpenses === 0) {
            return null;
        }

        if ($recentExpenses >= 5) {
            return "You've had expenses on {$recentExpenses} of the last 7 days. Consider having a no-spend day.";
        }

        return null;
    }

    private function getBudgetProgress(int $userId, string $currency): ?string
    {
        $user = User::find($userId);
        if (!$user || !$user->budget_goal || $user->budget_goal <= 0) {
            return null;
        }

        $monthlyExpenses = abs(Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->sum('amount'));

        if ($monthlyExpenses == 0) {
            return "You haven't recorded any expenses this month yet.";
        }

        $percentage = round(($monthlyExpenses / $user->budget_goal) * 100);

        if ($percentage > 100) {
            $over = number_format($monthlyExpenses - $user->budget_goal, 2);
            return "You've exceeded your budget by {$over} {$currency}! Time to cut back.";
        } elseif ($percentage > 80) {
            return "You've used {$percentage}% of your monthly budget. Watch your spending!";
        } elseif ($percentage > 50) {
            return "You're at {$percentage}% of your monthly budget. Great job staying on track!";
        }

        return null;
    }

    private function getWeekendSpending(int $userId): ?string
    {
        $transactions = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->get();

        if ($transactions->isEmpty()) {
            return null;
        }

        $weekendSpending = $transactions->filter(function ($transaction) {
            return in_array($transaction->date->dayOfWeek, [0, 6]); // Sunday = 0, Saturday = 6
        })->sum('amount');

        $totalSpending = abs($transactions->sum('amount'));

        if ($totalSpending == 0) {
            return null;
        }

        $percentage = round((abs($weekendSpending) / $totalSpending) * 100);

        if ($percentage > 40) {
            return "You spend {$percentage}% of your money on weekends. Consider free activities!";
        }

        return null;
    }

    private function getSavingsRate(int $userId): ?string
    {
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->sum('amount');

        $expenses = abs(Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()])
            ->sum('amount'));

        if ($income <= 0) {
            return null;
        }

        $savingsRate = round((($income - $expenses) / $income) * 100);

        if ($savingsRate > 20) {
            return "Excellent! You're saving {$savingsRate}% of your income this month.";
        } elseif ($savingsRate > 0) {
            return "You're saving {$savingsRate}% of your income. Try to increase this to 20% or more.";
        } else {
            return "You're spending more than you earn this month. Review your expenses!";
        }
    }
}
