<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('userdata')) {
            Schema::create('userdata', function (Blueprint $table): void {
                $table->id();
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product')) {
            Schema::create('product', function (Blueprint $table): void {
                $table->id();
                $table->string('name_product');
                $table->string('image');
                $table->unsignedInteger('quantity')->default(0);
                $table->decimal('price', 10, 2)->default(0);
                $table->string('short_desc', 500);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('comment')) {
            Schema::create('comment', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->text('message');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table): void {
                $table->id('order_id');
                $table->string('username');
                $table->string('address');
                $table->string('state');
                $table->string('postcode', 20);
                $table->string('city');
                $table->dateTime('time_order');
                $table->string('payment_method');
                $table->string('receipt')->nullable();
                $table->decimal('total', 10, 2)->default(0);
                $table->string('status')->default('Pending');
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('product');
                $table->unsignedInteger('quantity')->default(1);
                $table->index('order_id');
            });
        }

        if (! Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table): void {
                $table->id();
                $table->string('email')->index();
                $table->string('token')->unique();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('comment');
        Schema::dropIfExists('product');
        Schema::dropIfExists('userdata');
    }
};
