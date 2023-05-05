<?php namespace Codecycler\SURFconext\Updates;

use System\Classes\PluginManager;
use October\Rain\Support\Facades\Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddSurfConextToDepartmentsTable extends Migration
{
    public function up()
    {
        if (!PluginManager::instance()->exists('LearnKit.LMS')) {
            return;
        }

        Schema::table('learnkit_lms_departments', function (Blueprint $table) {
            $table->boolean('is_created_by_surfconext')->default(false);
        });
    }

    public function down()
    {
        if (!PluginManager::instance()->exists('LearnKit.LMS')) {
            return;
        }

        Schema::table('learnkit_lms_departments', function (Blueprint $table) {
            $table->dropColumn('is_created_by_surfconext');
        });
    }
}
