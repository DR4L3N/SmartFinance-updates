<?php

namespace App\Http\Controllers;

use App\Services\InsightService;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Auth middleware is already applied in web.php
    }

    /**
     * Show the dashboard.
     */
    public function index(InsightService $insightService): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->latest('date')  // Order by date instead of created_at
            ->take(5)
            ->get();

        // Calculate monthly totals
        $currentMonth = now();

        $monthlyIncome = $user->transactions()
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->where('type', 'income')
            ->sum('amount');

        $monthlyExpenses = $user->transactions()
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->where('type', 'expense')
            ->sum('amount');

        // Get budget goal and calculate remaining
        $budgetGoal = $user->buget_goal;
        $remainingBudget = $budgetGoal ? $budgetGoal - abs($monthlyExpenses) : 0;
        $budgetPercentage = ($budgetGoal > 0) ? (abs($monthlyExpenses) / $budgetGoal) * 100 : 0;

        // Get transaction history for trends (last 6 months)
        $transactionTrends = $user->transactions()
            ->selectRaw("strftime('%Y-%m', date) as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as total")
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $startDate = now()->subMonths(2);

        // Get daily income trends
        $dailyIncomeTrends = $user->transactions()
            ->where('type', 'income')
            ->where('date', '>=', $startDate)
            ->selectRaw("DATE(date) as day, SUM(amount) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Get daily expense trends
        $dailyExpenseTrends = $user->transactions()
            ->where('type', 'expense')
            ->where('date', '>=', $startDate)
            // We use ABS() to make the chart values positive
            ->selectRaw("DATE(date) as day, SUM(ABS(amount)) as total")
            ->groupBy('day')
            ->orderBy('day')
            ->get();


        // Prepare data for income vs expenses pie chart
        $distributionData = [
            ['label' => 'Income', 'value' => $monthlyIncome],
            ['label' => 'Expenses', 'value' => abs($monthlyExpenses)]
        ];

        $userId = $user->id;

        // Generate random insights
        $insights = $insightService->generateInsights($userId);

        // Money-saving tips (also randomized)
        $allTips = [
            'Set a monthly budget and track your progress.',
            'Compare prices before you shop.',
            'Limit impulse purchases by making a shopping list.',
            'Bring lunch from home instead of buying out.',
            'Cancel unused subscriptions.',
            'Use the 24-hour rule before making non-essential purchases.',
            'Track every expense, no matter how small.',
            'Set up automatic savings transfers.',
        ];

        shuffle($allTips);
        $tips = array_slice($allTips, 0, 1);

        $currency = $user->currency;

        return view('dashboard', compact(
            'currency',
            'tips',
            'insights',
            'recentTransactions',
            'monthlyIncome',
            'monthlyExpenses',
            'transactionTrends', // old
            'distributionData',
            'budgetGoal',
            'remainingBudget',
            'budgetPercentage',
            'dailyIncomeTrends',
            'dailyExpenseTrends'
        ));
    }
}