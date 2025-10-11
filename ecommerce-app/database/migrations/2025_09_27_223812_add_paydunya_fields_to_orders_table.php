<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Ajouter les champs PayDunya seulement s'ils n'existent pas déjà
            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('orders', 'payment_url')) {
                $table->text('payment_url')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_token')) {
                $table->string('payment_token')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_date')) {
                $table->timestamp('payment_date')->nullable();
            }
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')->nullable();
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'payment_url', 
                'payment_token',
                'payment_date',
                'customer_name',
                'customer_phone',
                'customer_email'
            ]);
        });
    }
};
