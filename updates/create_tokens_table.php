<?php namespace Codecycler\SURFconext\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateTokensTable Migration
 */
class CreateTokensTable extends Migration
{
    public function up()
    {
        Schema::create('codecycler_surfconext_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->longText('access_token')->nullable();
            $table->string('organisation')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('codecycler_surfconext_tokens');
    }
}
