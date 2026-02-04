<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['basic', 'header', 'jwt']);
            $table->text('auth_key')->nullable();
            $table->text('auth_value');
            $table->json('allowed_domains')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->text('destination_url');
            $table->string('upstream_method')->default('POST');
            $table->json('config')->nullable();
            $table->string('type')->default('form');
            $table->boolean('is_published')->default(false);
            $table->foreignId('credential_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        Schema::create('category_page', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'page_id']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->morphs('object');
            $table->boolean('can_view')->default(true);
            $table->boolean('can_edit')->default(false);
            $table->timestamps();
            $table->unique(['subject_id', 'subject_type', 'object_id', 'object_type'], 'subject_object_unique');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('category_page');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('credentials');
    }
};
