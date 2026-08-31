<?php

namespace Database\Seeders;

use App\Models\DepositMethod;
use App\Models\Faq;
use App\Models\WithdrawalMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SetupSeeder extends Seeder
{
    public function run(): void
    {
        // Deposit methods (manual + paystack gateway)
        DepositMethod::updateOrCreate(['slug' => 'paystack'], [
            'name' => 'Paystack', 'logo' => '/uploads/deposit-method/paystack.png',
            'min_amount' => 1, 'active' => true, 'manual' => false, 'position' => 0,
        ]);
        DepositMethod::updateOrCreate(['slug' => 'flutterwave'], [
            'name' => 'Flutterwave', 'logo' => null, 'min_amount' => 1,
            'active' => true, 'manual' => false, 'position' => 1,
        ]);
        DepositMethod::updateOrCreate(['slug' => 'bank-transfer'], [
            'name' => 'Bank Transfer', 'logo' => null, 'min_amount' => 10,
            'instructions' => 'Transfer to: Bank Name — Account Number 0000000000. Then submit proof below.',
            'active' => true, 'manual' => true, 'position' => 1,
        ]);

        // Withdrawal methods
        $bank = WithdrawalMethod::updateOrCreate(['slug' => 'bank'], [
            'name' => 'Bank Transfer', 'logo' => null, 'min_amount' => 10,
            'active' => true, 'gift_card' => false, 'position' => 0, 'parent_id' => null,
        ]);
        WithdrawalMethod::updateOrCreate(['slug' => 'paypal'], [
            'name' => 'PayPal', 'logo' => null, 'min_amount' => 5,
            'active' => true, 'gift_card' => false, 'position' => 1, 'parent_id' => null,
        ]);
        WithdrawalMethod::updateOrCreate(['slug' => 'payoneer'], [
            'name' => 'Payoneer', 'logo' => null, 'min_amount' => 10, 'active' => true, 'gift_card' => false, 'position' => 2, 'parent_id' => null,
        ]);
        WithdrawalMethod::updateOrCreate(['slug' => 'usdt'], [
            'name' => 'USDT', 'logo' => null, 'min_amount' => 10, 'active' => true, 'gift_card' => false, 'position' => 3, 'parent_id' => null,
        ]);
        WithdrawalMethod::updateOrCreate(['slug' => 'gift-card'], [
            'name' => 'Gift Cards', 'logo' => null, 'min_amount' => 5,
            'active' => true, 'gift_card' => true, 'position' => 4, 'parent_id' => null,
        ]);
        // Bank options
        foreach (['Local Bank' => 10, 'International Wire' => 50] as $name => $min) {
            WithdrawalMethod::updateOrCreate(['slug' => Str::slug($name.'-bank')], [
                'name' => $name, 'min_amount' => $min, 'active' => true,
                'parent_id' => $bank->id,
            ]);
        }

        // Default FAQs
        $faqs = [
            ['What is Airtrendmedia?', 'Airtrendmedia is an all-in-one social network, microjob marketplace and advertising platform where employers post small tasks, workers complete them for a fee, advertisers run campaigns, and users connect socially.'],
            ['How do I start earning?', 'Create an account, verify your email, pay the one-time account activation fee, then browse and complete available tasks.'],
            ['What is the account activation fee?', 'A one-time fee that activates your account so you can perform tasks and receive payments. The amount is configurable by the admin.'],
            ['How do I get paid?', 'When an employer approves your task proof, the task price is credited to your wallet balance. You can then request a withdrawal.'],
            ['Is there an affiliate program?', 'Yes. Invite friends with your referral link. When they register and pay the activation fee, you earn an affiliate reward (default $1.50).'],
            ['How long do withdrawals take?', 'Withdrawal requests are reviewed by the admin and processed according to the chosen withdrawal method.'],
        ];
        $pos = 0;
        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question' => $faq[0]], [
                'answer' => $faq[1], 'position' => $pos++,
            ]);
        }
    }
}
