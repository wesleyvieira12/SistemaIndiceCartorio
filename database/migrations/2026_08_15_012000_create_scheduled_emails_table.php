<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateScheduledEmailsTable extends Migration
{
    public function up()
    {
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject', 255);
            $table->text('body');
            $table->text('recipients');
            $table->dateTime('scheduled_at');
            $table->string('status', 20)->default('pending'); // pending, sent, failed, cancelled
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'scheduled_at']);
        });

        // Permissão para admins (mesmas roles que têm view_user)
        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'manage_scheduled_emails',
            'label' => 'Agendar e gerenciar e-mails',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $roleIds = DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permissions.name', 'view_user')
            ->pluck('permission_role.role_id')
            ->unique()
            ->values()
            ->all();

        if (empty($roleIds)) {
            $roleIds = DB::table('roles')->pluck('id')->all();
        }

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down()
    {
        $permission = DB::table('permissions')->where('name', 'manage_scheduled_emails')->first();
        if ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }

        Schema::dropIfExists('scheduled_emails');
    }
}
