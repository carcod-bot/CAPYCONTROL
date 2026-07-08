<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\User;

class PosControlSeeder extends Seeder
{
    public function run(): void
    {
        // Create cash registers
        $registers = [
            ['number' => '003', 'name' => 'Caja 3', 'location' => 'Entrada'],
            ['number' => '004', 'name' => 'Caja 4', 'location' => 'Pasillo Central'],
            ['number' => '009', 'name' => 'Caja 9', 'location' => 'Pasillo 2'],
            ['number' => '010', 'name' => 'Caja 10', 'location' => 'Pasillo 3'],
            ['number' => '014', 'name' => 'Caja 14', 'location' => 'Salida'],
        ];

        foreach ($registers as $reg) {
            CashRegister::firstOrCreate(['number' => $reg['number']], $reg);
        }

        // Get user for sessions
        $user = User::first();
        if (!$user) return;

        // Create sample open sessions
        $reg003 = CashRegister::where('number', '003')->first();
        $reg004 = CashRegister::where('number', '004')->first();
        $reg009 = CashRegister::where('number', '009')->first();
        $reg010 = CashRegister::where('number', '010')->first();
        $reg014 = CashRegister::where('number', '014')->first();

        // Session for 003 - Open
        CashSession::create([
            'cash_register_id' => $reg003->id,
            'user_id' => $user->id,
            'status' => 'open',
            'turn_number' => 2639,
            'opening_amount' => 100.00,
            'expected_amount' => 850.00,
            'total_sales' => 31,
            'total_returns' => 2,
            'total_withdrawals' => 0,
            'pending_invoices' => 0,
            'opened_at' => today()->setTime(13, 11, 11),
        ]);

        // Session for 004 - Closed
        CashSession::create([
            'cash_register_id' => $reg004->id,
            'user_id' => $user->id,
            'status' => 'closed',
            'turn_number' => 3281,
            'opening_amount' => 100.00,
            'expected_amount' => 1250.00,
            'actual_amount' => 1245.50,
            'difference' => -4.50,
            'total_sales' => 60,
            'total_returns' => 3,
            'total_withdrawals' => 3,
            'pending_invoices' => 0,
            'opened_at' => today()->setTime(9, 54, 27),
            'closed_at' => today()->setTime(16, 59, 59),
        ]);

        // Session for 009 - Open
        CashSession::create([
            'cash_register_id' => $reg009->id,
            'user_id' => $user->id,
            'status' => 'open',
            'turn_number' => 2986,
            'opening_amount' => 100.00,
            'expected_amount' => 620.00,
            'total_sales' => 21,
            'total_returns' => 0,
            'total_withdrawals' => 0,
            'pending_invoices' => 0,
            'opened_at' => today()->setTime(9, 35, 31),
        ]);

        // Session for 010 - Open
        CashSession::create([
            'cash_register_id' => $reg010->id,
            'user_id' => $user->id,
            'status' => 'open',
            'turn_number' => 2822,
            'opening_amount' => 100.00,
            'expected_amount' => 980.00,
            'total_sales' => 35,
            'total_returns' => 0,
            'total_withdrawals' => 0,
            'pending_invoices' => 0,
            'opened_at' => today()->setTime(13, 11, 6),
        ]);

        // Session for 014 - Open
        CashSession::create([
            'cash_register_id' => $reg014->id,
            'user_id' => $user->id,
            'status' => 'open',
            'turn_number' => 3002,
            'opening_amount' => 100.00,
            'expected_amount' => 1100.00,
            'total_sales' => 40,
            'total_returns' => 0,
            'total_withdrawals' => 0,
            'pending_invoices' => 1,
            'opened_at' => today()->setTime(13, 24, 3),
        ]);
    }
}
