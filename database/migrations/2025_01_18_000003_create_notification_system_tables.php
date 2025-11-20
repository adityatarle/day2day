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
        // Notification Types
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'order_created', 'stock_low', 'payment_received'
            $table->string('display_name');
            $table->text('description');
            $table->string('icon')->nullable(); // FontAwesome icon class
            $table->string('color')->default('#3B82F6'); // Hex color for UI
            $table->json('channels'); // ['database', 'mail', 'sms', 'whatsapp', 'push']
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1); // 1=low, 2=medium, 3=high, 4=critical
            $table->timestamps();
        });

        // Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_type_id');
            $table->string('channel'); // database, mail, sms, whatsapp, push
            $table->string('subject')->nullable(); // For email
            $table->text('body'); // Template body with placeholders
            $table->json('variables'); // Available template variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('notification_type_id')->references('id')->on('notification_types');
            $table->unique(['notification_type_id', 'channel']);
        });

        // User Notification Preferences
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_type_id');
            $table->boolean('database_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('push_enabled')->default(true);
            $table->enum('email_frequency', ['realtime', 'digest_daily', 'digest_weekly', 'disabled'])->default('realtime');
            $table->json('digest_settings')->nullable(); // Time preferences for digest
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('notification_type_id')->references('id')->on('notification_types');
            $table->unique(['user_id', 'notification_type_id'], 'user_notif_prefs_unique');
        });

        // Notification Queue
        Schema::create('notification_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_type_id');
            $table->string('channel'); // database, mail, sms, whatsapp, push
            $table->json('data'); // Notification data
            $table->string('status')->default('pending'); // pending, processing, sent, failed
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('notification_type_id')->references('id')->on('notification_types');
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
        });

        // Notification History
        Schema::create('notification_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_type_id');
            $table->string('channel');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('status'); // sent, delivered, read, failed
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('notification_type_id')->references('id')->on('notification_types');
            $table->index(['user_id', 'created_at']);
            $table->index(['notification_type_id', 'created_at']);
        });

        // SMS Logs
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone_number');
            $table->text('message');
            $table->string('provider'); // twilio, textlocal, etc.
            $table->string('provider_message_id')->nullable();
            $table->string('status'); // sent, delivered, failed
            $table->decimal('cost', 8, 4)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['phone_number', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // WhatsApp Logs
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone_number');
            $table->text('message');
            $table->string('template_name')->nullable();
            $table->json('template_params')->nullable();
            $table->string('provider'); // whatsapp_business_api, etc.
            $table->string('provider_message_id')->nullable();
            $table->string('status'); // sent, delivered, read, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['phone_number', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        // Push Notification Tokens
        Schema::create('push_notification_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token');
            $table->enum('platform', ['ios', 'android', 'web']);
            $table->string('device_id')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['user_id', 'token']);
            $table->index(['platform', 'is_active']);
        });

        // Notification Digest
        Schema::create('notification_digests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('frequency', ['daily', 'weekly']);
            $table->date('digest_date');
            $table->json('notification_ids'); // Array of notification IDs included
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['user_id', 'frequency', 'digest_date'], 'user_digest_unique');
            $table->index(['status', 'digest_date']);
        });

        // Notification Actions
        Schema::create('notification_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id'); // References notification_history
            $table->string('action_type'); // approve, view, dismiss, etc.
            $table->string('action_label');
            $table->string('action_url')->nullable();
            $table->json('action_data')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->foreign('notification_id')->references('id')->on('notification_history');
            $table->index(['notification_id', 'action_type']);
        });

        // Notification Read Status
        Schema::create('notification_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_id'); // References notification_history
            $table->timestamp('read_at');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('notification_id')->references('id')->on('notification_history');
            $table->unique(['user_id', 'notification_id'], 'user_notif_read_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_read_status');
        Schema::dropIfExists('notification_actions');
        Schema::dropIfExists('notification_digests');
        Schema::dropIfExists('push_notification_tokens');
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('notification_history');
        Schema::dropIfExists('notification_queue');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_types');
    }
};
