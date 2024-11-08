<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEncryptionKeyVersionToPostsUsersTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('encryption_key_version')->nullable()->after('tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('encryption_key_version')->nullable()->after('tenant_id');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('encryption_key_version')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('encryption_key_version');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('encryption_key_version');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('encryption_key_version');
        });
    }
}
