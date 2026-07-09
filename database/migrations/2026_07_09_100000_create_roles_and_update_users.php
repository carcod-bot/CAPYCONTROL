<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable(); // list of granted permission keys
            $table->boolean('is_system')->default(false); // system roles can't be deleted
            $table->timestamps();
        });

        // Add role_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete()->after('role');
        });

        // Seed system roles
        $allPermissions = [
            'capycontrol.access',
            'capypos.access',
            'dashboard.view',
            'inventory.view',
            'inventory.edit',
            'finances.view',
            'finances.edit',
            'pos_control.view',
            'pos_control.manage',
            'pos_control.sessions',
            'configuraciones.view',
            'configuraciones.edit',
        ];

        DB::table('roles')->insert([
            [
                'name'        => 'Administrador',
                'description' => 'Acceso total al sistema',
                'permissions' => json_encode($allPermissions),
                'is_system'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Cajero',
                'description' => 'Acceso a CapyPOS y apertura de turnos',
                'permissions' => json_encode(['capypos.access', 'pos_control.sessions']),
                'is_system'   => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Supervisor',
                'description' => 'Monitoreo de cajas y autorización de turnos',
                'permissions' => json_encode([
                    'capycontrol.access',
                    'capypos.access',
                    'dashboard.view',
                    'pos_control.view',
                    'pos_control.sessions',
                ]),
                'is_system'   => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('roles');
    }
};
