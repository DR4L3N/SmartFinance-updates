<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class InsightService
{
    /**
     * Generate insights for the user
     */
    public function generateInsights(int $userId): array
    {
        $allInsights = [
            $this->safeExecute(fn() => $this->getCategorySpendingChange($userId)),
            $this->safeExecute(fn() => $this->getHighestSpendingCategory($userId)),
            $this->safeExecute(fn() => $this->getAverageTransactionAmount($userId)),
            $this->safeExecute(fn() => $this->getConsecutiveExpenseDays($userId)),
            $this->safeExecute(fn() => $this->getBudgetProgress($userId)),
            $this->safeExecute(fn() => $this->getWeekendSpending($userId)),
            $this->safeExecute(fn() => $this->getSavingsRate($userId)),
        ];

        // Filter out null insights and randomly select 2-3
        $validInsights = array_filter($allInsights, fn($insight) => $insight !== null && $insight !== '');

        if (empty($validInsights)) {
            return [];
        }

        return $this->randomInsights($validInsights, rand(2, min(3, count($validInsights))));
    }

    /**
     * Safely execute an insight function and catch any errors
     */
    private function safeExecute(callable $callback): ?string
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::warning('Insight generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get random insights from the collection
     */
    private function randomInsights(array $insights, int $count): array
    {
        shuffle($insights);
        return array_slice($insights, 0, min($count, count($insights)));
    }

    /**
     * Compare category spending month-over-month
     */
    /**
 * Compare category spending month-over-month
 */
    private function getCategorySpendingChange(int $userId): ?string
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = $lastMonth->copy()->endOfMonth();

        // Use enum cases instead of strings
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

        // Skip if no spending data
        if ($lastMonthSpending == 0 && $thisMonthSpending == 0) {
            return null;
        }

        // Handle case where there was no spending last month
        if ($lastMonthSpending == 0 && $thisMonthSpending > 0) {
            return "You started spending on " . $categoryValue . " this month ($" . number_format($thisMonthSpending, 2) . ").";
        }

        if ($lastMonthSpending == 0) {
            return null;
        }

        $change = round((($thisMonthSpending - $lastMonthSpending) / $lastMonthSpending) * 100);

        if (abs($change) < 5) {
            return null; // Ignore small changes
        }

        if ($change > 0) {
            return "You spent {$change}% more on " . $categoryValue . " this month compared to last month.";
        } elseif ($change < 0) {
            return "Great job! You spent " . abs($change) . "% less on " . $categoryValue . " this month.";
        }

        return null;
    }


    /**
     * Get highest spending category
     */
    private function getHighestSpendingCategory(int $userId): ?string
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

        // Get the enum value or cast to string
        $categoryName = $topCategory->category instanceof \App\Enums\TransactionCategory
            ? $topCategory->category->value
            : $topCategory->category;

        return "Your highest spending category this month is " . $categoryName . " with $" . number_format($topCategory->total, 2) . ".";
    }

    /**
     * Get average transaction amount
     */
    private function getAverageTransactionAmount(int $userId): ?string
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

        return "Your average expense this month is $" . number_format(abs($average), 2) . " per transaction.";
    }

    /**
     * Get consecutive expense days
     */
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

    /**
     * Budget progress insight
     */
    private function getBudgetProgress(int $userId): ?string
    {
        $user = \App\Models\User::find($userId);

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
            return "You've exceeded your budget by " . ($percentage - 100) . "%. Time to cut back!";
        } elseif ($percentage > 80) {
            return "You've used {$percentage}% of your monthly budget. Watch your spending!";
        } elseif ($percentage > 50) {
            return "You're at {$percentage}% of your monthly budget. Great job staying on track!";
        }

        return null;
    }

    /**
     * Weekend spending pattern (SQLite compatible)
     */
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

    /**
     * Savings rate
     */
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
