<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateMaxCapacitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::select('CREATE VIEW `max_capacities` AS 
        SELECT hotel_id, date, MAX(capacity) as capacity FROM capacities GROUP BY hotel_id, date;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::select('DROP VIEW `max_capacities`');
    }
}
