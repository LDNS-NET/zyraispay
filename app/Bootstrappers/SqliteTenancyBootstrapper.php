<?php
/*namespace App\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;

class SqliteTenancyBootstrapper implements TenancyBootstrapper
{
    public function bootstrap($tenant)
    {
        // Force the correct DB file path
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => database_path('database.sqlite'),
        ]);
    }

    public function revert()
    {
        // Restore to central DB if needed
        config([
            'database.default' => env('DB_CONNECTION', 'sqlite'),
            'database.connections.sqlite.database' => env('DB_DATABASE', database_path('database.sqlite')),
        ]);
    }
}*/



namespace App\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;

class SqliteTenancyBootstrapper implements TenancyBootstrapper
{
    /**
     * Bootstrap tenant-specific database connection.
     *
     * @param mixed $tenant
     */
    public function bootstrap($tenant): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => database_path('database.sqlite'),
        ]);
    }

    /**
     * Revert back to the central database connection.
     */
    public function revert(): void
    {
        config([
            'database.default' => env('DB_CONNECTION', 'sqlite'),
            'database.connections.sqlite.database' => env(
                'DB_DATABASE',
                database_path('database.sqlite')
            ),
        ]);
    }
}

