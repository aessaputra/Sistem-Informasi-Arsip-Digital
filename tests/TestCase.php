<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use \Tests\CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Only run database-related setup for tests that actually use database traits
        if ($this->usesDatabase()) {
            // Ensure basic roles exist for tests that use database
            $this->ensureRolesExist();

            // Set up testing environment
            if ($this->shouldSeedDatabase()) {
                $this->seed();
            }
        }
    }

    /**
     * Determine if the test uses database (has RefreshDatabase or DatabaseTransactions trait)
     */
    protected function usesDatabase(): bool
    {
        $traits = class_uses_recursive($this);
        return in_array(RefreshDatabase::class, $traits) || 
               in_array(DatabaseTransactions::class, $traits);
    }

    /**
     * Determine if the test should seed the database
     */
    protected function shouldSeedDatabase(): bool
    {
        return in_array(RefreshDatabase::class, class_uses_recursive($this));
    }

    /**
     * Create application instance for testing
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    /**
     * Set up database for testing
     */
    protected function setUpDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            // For SQLite in-memory database
            Artisan::call('migrate:fresh');
        } else {
            // For other databases
            Artisan::call('migrate:fresh', ['--seed' => true]);
        }
    }

    /**
     * Clean up after test
     */
    protected function tearDown(): void
    {
        // Clean up any test artifacts
        if (app()->environment('testing')) {
            // Clear any cached data
            if (method_exists($this, 'clearTestCache')) {
                $this->clearTestCache();
            }
        }

        parent::tearDown();
    }

    /**
     * Assert that a database table has a specific count
     */
    protected function assertDatabaseCount($table, int $count, $connection = null): void
    {
        $actual = $this->app['db']->connection($connection)->table($table)->count();
        
        $this->assertEquals(
            $count, 
            $actual,
            "Expected table '{$table}' to have {$count} records, but found {$actual}."
        );
    }

    /**
     * Assert that a model exists in database with specific attributes
     */
    protected function assertModelExistsWithAttributes(string $model, array $attributes): void
    {
        $this->assertDatabaseHas((new $model)->getTable(), $attributes);
    }

    /**
     * Assert that a model does not exist in database with specific attributes
     */
    protected function assertModelMissingWithAttributes(string $model, array $attributes): void
    {
        $this->assertDatabaseMissing((new $model)->getTable(), $attributes);
    }

    /**
     * Create a user with specific role for testing
     */
    protected function createUserWithRole(string $role): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        
        // Create role if it doesn't exist (use firstOrCreate to avoid duplicates)
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        
        $user->assignRole($role);
        
        return $user;
    }

    /**
     * Ensure basic roles exist for testing
     */
    protected function ensureRolesExist(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator']);
    }

    /**
     * Create test file for upload testing
     */
    protected function createTestFile(string $name = 'test.pdf', int $sizeKB = 100, string $mimeType = 'application/pdf'): \Illuminate\Http\UploadedFile
    {
        return \Illuminate\Http\UploadedFile::fake()->create($name, $sizeKB, $mimeType);
    }

    /**
     * Create test image for upload testing
     */
    protected function createTestImage(string $name = 'test.jpg', int $width = 100, int $height = 100): \Illuminate\Http\UploadedFile
    {
        return \Illuminate\Http\UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Assert JSON response has specific structure
     */
    protected function assertJsonStructureExact(array $structure, array $responseData = null): void
    {
        if (is_null($responseData)) {
            $responseData = $this->decodeResponseJson();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key !== '*') {
                $this->assertArrayHasKey($key, $responseData);
                $this->assertJsonStructureExact($value, $responseData[$key]);
            } elseif ($key === '*') {
                $this->assertIsArray($responseData);
                if (!empty($responseData)) {
                    $this->assertJsonStructureExact($value, reset($responseData));
                }
            } else {
                $this->assertArrayHasKey($value, $responseData);
            }
        }
    }

    /**
     * Get decoded JSON response
     */
    protected function decodeResponseJson(): array
    {
        return json_decode($this->response->getContent(), true);
    }

    /**
     * Assert that response contains validation errors for specific fields
     */
    protected function assertValidationErrors(array $fields): void
    {
        $this->assertSessionHasErrors($fields);
        
        foreach ($fields as $field) {
            $this->assertSessionHasErrorsIn('default', $field);
        }
    }

    /**
     * Mock external service for testing
     */
    protected function mockExternalService(string $service, array $methods = []): \Mockery\MockInterface
    {
        $mock = \Mockery::mock($service);
        
        foreach ($methods as $method => $return) {
            $mock->shouldReceive($method)->andReturn($return);
        }
        
        $this->app->instance($service, $mock);
        
        return $mock;
    }

    /**
     * Skip test if condition is not met
     */
    protected function skipIf(bool $condition, string $message = 'Test skipped due to condition'): void
    {
        if ($condition) {
            $this->markTestSkipped($message);
        }
    }

    /**
     * Skip test if not in Docker environment
     */
    protected function skipIfNotDocker(): void
    {
        $this->skipIf(
            !$this->isDockerEnvironment(),
            'Test requires Docker environment'
        );
    }

    /**
     * Check if running in Docker environment
     */
    protected function isDockerEnvironment(): bool
    {
        return file_exists('/.dockerenv') || 
               getenv('DOCKER_CONTAINER') === 'true' ||
               gethostname() === 'arsip-digital-test';
    }

    /**
     * Get test data path
     */
    protected function getTestDataPath(string $file = ''): string
    {
        $path = __DIR__ . '/data';
        
        return $file ? $path . '/' . $file : $path;
    }

    /**
     * Create test data directory if it doesn't exist
     */
    protected function ensureTestDataDirectory(): void
    {
        $path = $this->getTestDataPath();
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}