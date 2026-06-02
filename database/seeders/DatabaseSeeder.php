<?php

declare(strict_types=1);

namespace Database\Seeders;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->syncPermissions();
        $this->appendBuckets();

        if (! app()->isProduction()) {
            $adminData = $this->spawnAdminUser();
            $this->call(DevelopmentSeeder::class, false, ['adminData' => $adminData]);
        }

        $this->command->newLine();
        $this->command->info('Database seeding completed successfully. Have fun!');
    }

    public function spawnAdminUser(): array
    {
        $this->command->warn('Creating admin user...');

        $admin = User::factory()
            ->admin()
            ->create([
                'password' => $this->resolveAdminPassword(),
            ]);

        $admin->assignRole(Roles::SuperAdmin);

        $team = Team::factory()
            ->hasDepartments(2)
            ->create([
                'name' => '3Pontos',
                'slug' => '3pontos',
                'owner_id' => $admin->getKey(),
            ]);

        $team->members()->attach($admin);

        $departments = $team->departments;

        $this->command->info('Admin user created successfully.');

        return [
            'admin' => $admin,
            'team' => $team,
            'departments' => $departments,
        ];
    }

    private function resolveAdminPassword(): string
    {
        $password = config('app.dev_admin_password');

        if (filled($password)) {
            return (string) $password;
        }

        throw_unless(app()->isLocal(), RuntimeException::class, 'DEV_ADMIN_PASSWORD não está definido. Configure-o no ambiente para semear o admin fora de local.');

        return 'password';
    }

    private function syncPermissions(): void
    {
        $this->command->warn('Syncing permissions...');
        Artisan::call('sync:permissions');
        $this->command->info('Permissions synced successfully.');
    }

    private function appendBuckets(): void
    {
        if (config('filesystems.default') !== 'r2') {
            return;
        }

        try {
            $client = new S3Client([
                'version' => 'latest',
                'region' => 'us-east-1',
                'endpoint' => config('filesystems.disks.r2.endpoint'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => config('filesystems.disks.r2.key'),
                    'secret' => config('filesystems.disks.r2.secret'),
                ],
            ]);

            $bucket = config('filesystems.disks.r2.bucket');
            if (! $client->doesBucketExist($bucket)) {
                $client->createBucket(['Bucket' => $bucket]);
                note(sprintf("Created S3 bucket '%s'", $bucket));
            } else {
                note(sprintf("S3 bucket '%s' already exists", $bucket));
            }

            $testBucket = $bucket.'-test';
            if (! $client->doesBucketExist($testBucket)) {
                $client->createBucket(['Bucket' => $testBucket]);
                note(sprintf("Created S3 bucket '%s'", $testBucket));
            } else {
                note(sprintf("S3 bucket '%s' already exists", $testBucket));
            }
        } catch (S3Exception $e) {
            warning('Could not create S3 buckets: '.$e->getMessage());
        } catch (Exception $e) {
            warning('Could not connect to S3: '.$e->getMessage());
        }
    }
}
