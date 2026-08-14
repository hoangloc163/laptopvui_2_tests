<?php
declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase as BaseTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base TestCase với HTTP client wrapper + DOM helpers.
 * Mọi Feature test kế thừa từ đây để có sẵn:
 * - $this->http: Guzzle client với cookie jar (giữ session)
 * - $this->get() / $this->post(): quick request
 * - $this->crawler(): DOM crawler cho response HTML
 */
abstract class TestCase extends BaseTestCase
{
    protected Client $http;
    protected CookieJar $cookies;
    protected string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');
        $this->cookies = new CookieJar();

        $this->http = new Client([
            'base_uri' => $this->baseUrl . '/',
            'cookies' => $this->cookies,
            'timeout' => 15,
            'http_errors' => false,  // Don't throw on 4xx/5xx - we assert on status
            'allow_redirects' => false, // Test redirect explicitly
        ]);
    }

    /**
     * Ping server before test — skip if app isn't running.
     * Call in setUp of tests requiring live server.
     */
    protected function requireApp(): void
    {
        try {
            $r = $this->http->get('', ['timeout' => 3]);
            if ($r->getStatusCode() >= 500) {
                $this->markTestSkipped("App server returned {$r->getStatusCode()}");
            }
        } catch (RequestException $e) {
            $this->markTestSkipped("App server not reachable at {$this->baseUrl}: {$e->getMessage()}");
        }
    }

    /**
     * Shorthand GET request.
     */
    protected function get(string $path, array $query = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->http->get(ltrim($path, '/'), ['query' => $query]);
    }

    /**
     * Shorthand POST request (form-encoded).
     */
    protected function post(string $path, array $data = []): \Psr\Http\Message\ResponseInterface
    {
        return $this->http->post(ltrim($path, '/'), ['form_params' => $data]);
    }

    /**
     * Get DOM crawler for response body.
     */
    protected function crawler(\Psr\Http\Message\ResponseInterface $response): Crawler
    {
        return new Crawler((string)$response->getBody(), $this->baseUrl);
    }

    /**
     * Assert response is a redirect (3xx) with optional Location match.
     */
    protected function assertRedirect(\Psr\Http\Message\ResponseInterface $response, ?string $expectedLocationContains = null): void
    {
        $status = $response->getStatusCode();
        $this->assertTrue($status >= 300 && $status < 400, "Expected redirect, got HTTP {$status}");
        if ($expectedLocationContains !== null) {
            $location = $response->getHeaderLine('Location');
            $this->assertStringContainsString($expectedLocationContains, $location, "Redirect location mismatch");
        }
    }

    /**
     * Follow redirect chain manually (up to N hops).
     */
    protected function follow(\Psr\Http\Message\ResponseInterface $response, int $maxHops = 5): \Psr\Http\Message\ResponseInterface
    {
        $current = $response;
        $hops = 0;
        while ($current->getStatusCode() >= 300 && $current->getStatusCode() < 400 && $hops < $maxHops) {
            $location = $current->getHeaderLine('Location');
            if (!$location) break;
            $current = $this->http->get($location);
            $hops++;
        }
        return $current;
    }

    /**
     * Assert HTML body contains substring.
     */
    protected function assertBodyContains(\Psr\Http\Message\ResponseInterface $response, string $needle): void
    {
        $body = (string)$response->getBody();
        $this->assertStringContainsString($needle, $body, "Body does not contain '{$needle}'");
    }

    /**
     * Login as user via /login_ endpoint. Returns cookie jar (session).
     */
    protected function loginAs(string $email, string $password): void
    {
        $this->post('login_', ['email' => $email, 'matkhau' => $password]);
    }

    /**
     * Login as demo admin (admin@demo.local / admin123).
     */
    protected function loginAsAdmin(): void
    {
        $this->post('admin/login_', ['email' => 'admin@demo.local', 'matkhau' => 'admin123']);
    }
}
