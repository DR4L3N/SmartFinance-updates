<?php

namespace App\Enums;

enum TransactionCategory: string
{
    case INCOME = 'Income';
    case CLOTHING = 'Clothing & Apparel';
    case FOOD = 'Food & Groceries';
    case HOUSING = 'Housing';
    case TRANSPORTATION = 'Transportation';
    case HEALTHCARE = 'Healthcare & Medical';
    case ENTERTAINMENT = 'Entertainment & Leisure';
    case EDUCATION = 'Education & Tuition';
    case PERSONAL_CARE = 'Personal Care';
    case INSURANCE = 'Insurance';
    case TRAVEL = 'Travel & Accommodation';
    case UTILITIES = 'Utilities';
    case ELECTRONICS = 'Electronics & Gadgets';
    case GIFTS = 'Gifts & Donations';
    case BANKING = 'Banking Fees & Charges';
    case SUBSCRIPTIONS = 'Subscriptions & Memberships';

    public static function getDescriptions(): array
    {
        return [
            self::INCOME->value => 'General income and earnings',
            self::CLOTHING->value => 'Clothing, shoes, accessories, and other apparel',
            self::FOOD->value => 'Supermarkets, grocery stores, restaurants, and food delivery',
            self::HOUSING->value => 'Rent, mortgage payments, utilities, and home maintenance',
            self::TRANSPORTATION->value => 'Fuel, public transit, car maintenance, and transportation services',
            self::HEALTHCARE->value => 'Medical appointments, medications, and health-related expenses',
            self::ENTERTAINMENT->value => 'Movies, events, streaming services, and leisure activities',
            self::EDUCATION->value => 'School fees, tuition, courses, and educational materials',
            self::PERSONAL_CARE->value => 'Hair salons, cosmetics, personal hygiene products',
            self::INSURANCE->value => 'Health insurance, auto insurance, property insurance',
            self::TRAVEL->value => 'Hotels, flights, vacation expenses, and travel-related costs',
            self::UTILITIES->value => 'Electricity, water, gas, internet, and utility services',
            self::ELECTRONICS->value => 'Computers, phones, gadgets, and electronic accessories',
            self::GIFTS->value => 'Gifts, charitable donations, and contributions',
            self::BANKING->value => 'Bank fees, transaction charges, and financial service costs',
            self::SUBSCRIPTIONS->value => 'Monthly subscriptions, memberships, and recurring services'
        ];
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
