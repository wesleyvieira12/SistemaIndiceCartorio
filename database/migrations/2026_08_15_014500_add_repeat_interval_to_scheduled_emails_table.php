<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRepeatIntervalToScheduledEmailsTable extends Migration
{
    public function up()
    {
        Schema::table('scheduled_emails', function (Blueprint $table) {
            $table->string('repeat_interval', 20)->default('none')->after('scheduled_at');
            $table->timestamp('last_sent_at')->nullable()->after('sent_at');
        });
    }

    public function down()
    {
        Schema::table('scheduled_emails', function (Blueprint $table) {
            $table->dropColumn(['repeat_interval', 'last_sent_at']);
        });
    }
}
