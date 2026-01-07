<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Migrations\Migrator;

trait RunMigrations
{
    use DBconnection;

    public function runPendingMigrations(string $connection = 'mysql', string $path = 'database/migrations/tenant', $database = null): bool
    {
        if ($database) {
            $this->connectToDatabase($database);
        }

        /** @var Migrator $migrator */
        $migrator = App::make('migrator');
        $migrator->setConnection($connection);

        // Ensure migration table exists
        if (! $migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }

        // Already ran migrations
        $ran = $migrator->getRepository()->getRan();

        // Migration files available
        $files = $migrator->getMigrationFiles(database_path('migrations/tenant'));

        // Pending migrations
        $pending = array_diff(array_keys($files), $ran);

        if (! empty($pending)) {
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path'     => $path,
                '--force'    => true,
            ]);

            return true;
        }

        return false;
    }
}
