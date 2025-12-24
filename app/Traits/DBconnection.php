<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DBconnection
{

    public function connectToDatabase($database = null)
    {
        config(['database.connections.mysql.database' => $database]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }
}
