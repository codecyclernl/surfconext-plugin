<?php namespace Codecycler\SURFconext\Updates;

use System\Classes\PluginManager;
use October\Rain\Support\Facades\Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddSurfconextOrganisationToTeamsTable extends Migration
{
    public function up()
    {
        if (!PluginManager::instance()->exists('Codecycler.Teams')) {
            return;
        }

        Schema::table('codecycler_teams_teams', function (Blueprint $table) {
            $table->string('surfconext_organisation')->nullable();
        });
    }

    public function down()
    {
        if (!PluginManager::instance()->exists('Codecycler.Teams')) {
            return;
        }

        Schema::table('codecycler_teams_teams', function (Blueprint $table) {
            $table->dropColumn('surfconext_organisation');
        });
    }
}