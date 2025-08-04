<?php

namespace Tests\Feature\PWA;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceWorkerRemovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function service_worker_file_does_not_exist()
    {
        // Check that service worker files are not present
        $this->assertFileDoesNotExist(public_path('sw.js'));
        $this->assertFileDoesNotExist(public_path('service-worker.js'));
        $this->assertFileDoesNotExist(public_path('serviceworker.js'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function manifest_json_file_does_not_exist()
    {
        // Check that manifest.json is not present
        $this->assertFileDoesNotExist(public_path('manifest.json'));
        $this->assertFileDoesNotExist(public_path('site.webmanifest'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_service_worker_registration_in_html()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain service worker registration code
        $this->assertStringNotContainsString('navigator.serviceWorker', $content);
        $this->assertStringNotContainsString('serviceWorker.register', $content);
        $this->assertStringNotContainsString('sw.js', $content);
        $this->assertStringNotContainsString('service-worker.js', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_manifest_link_in_html()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain manifest link
        $this->assertStringNotContainsString('rel="manifest"', $content);
        $this->assertStringNotContainsString('manifest.json', $content);
        $this->assertStringNotContainsString('site.webmanifest', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_pwa_installation_prompts()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain PWA installation related code
        $this->assertStringNotContainsString('beforeinstallprompt', $content);
        $this->assertStringNotContainsString('Add to Home Screen', $content);
        $this->assertStringNotContainsString('Install App', $content);
        $this->assertStringNotContainsString('pwa-install', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_pwa_meta_tags()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain PWA-specific meta tags
        $this->assertStringNotContainsString('name="apple-mobile-web-app', $content);
        $this->assertStringNotContainsString('name="mobile-web-app', $content);
        $this->assertStringNotContainsString('name="theme-color"', $content);
        $this->assertStringNotContainsString('content="standalone"', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_pwa_icon_assets()
    {
        // Check that PWA icon files are not present
        $iconSizes = ['192x192', '512x512', '180x180', '152x152', '144x144', '120x120', '114x114', '76x76', '72x72', '60x60', '57x57'];
        
        foreach ($iconSizes as $size) {
            $this->assertFileDoesNotExist(public_path("icons/icon-{$size}.png"));
            $this->assertFileDoesNotExist(public_path("images/icons/icon-{$size}.png"));
            $this->assertFileDoesNotExist(public_path("assets/icons/icon-{$size}.png"));
        }
        
        // Check for common PWA icon names
        $this->assertFileDoesNotExist(public_path('apple-touch-icon.png'));
        $this->assertFileDoesNotExist(public_path('favicon-32x32.png'));
        $this->assertFileDoesNotExist(public_path('favicon-16x16.png'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_offline_functionality_references()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Should not contain PWA offline/caching related code
        $this->assertStringNotContainsString('offline-first', $content);
        $this->assertStringNotContainsString('cache-first', $content);
        $this->assertStringNotContainsString('workbox', $content);
        $this->assertStringNotContainsString('sw-precache', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_page_works_without_pwa_issues()
    {
        // Test that login page works properly without PWA-related CSRF issues
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
        
        // Should not contain any PWA-related code that could interfere with authentication
        $content = $response->getContent();
        $this->assertStringNotContainsString('serviceWorker', $content);
        $this->assertStringNotContainsString('manifest.json', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function csrf_tokens_work_properly_without_pwa()
    {
        // Test that CSRF tokens work correctly without PWA interference
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        
        // Should contain CSRF token (either in form input or meta tag)
        $content = $response->getContent();
        $hasCsrfToken = (strpos($content, '_token') !== false) || 
                       (strpos($content, 'csrf-token') !== false) ||
                       (strpos($content, '@csrf') !== false);
        $this->assertTrue($hasCsrfToken, 'Login page should contain CSRF protection');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function application_functions_normally_without_pwa()
    {
        $this->actingAs($this->user);

        // Test that main application features work without PWA
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        
        $response = $this->get(route('food-management'));
        $response->assertStatus(200);
        
        $response = $this->get(route('medicines-management'));
        $response->assertStatus(200);
        
        // All routes should work normally without PWA functionality
    }
}