<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

it('uses the patched stable Nginx release in both environments', function (string $environment): void {
    $dockerfile = file_get_contents(base_path("docker/{$environment}/nginx/Dockerfile"));

    expect(preg_match('/^FROM nginx:(1\.30\.\d+)-alpine$/m', $dockerfile, $matches))->toBe(1);
    expect(version_compare($matches[1], '1.30.7', '>='))->toBeTrue();
})->with(['development', 'production']);

it('keeps the production database private and requires an explicit password', function (): void {
    $services = Yaml::parseFile(base_path('compose.prod.yaml'))['services'];

    expect($services['postgres'])->not->toHaveKey('ports');
    expect($services['postgres']['environment'])
        ->toContain('POSTGRES_PASSWORD=${POSTGRES_PASSWORD:?Set POSTGRES_PASSWORD to a strong password}');
    expect($services['php-fpm']['environment']['DB_PASSWORD'])
        ->toBe('${POSTGRES_PASSWORD:?Set POSTGRES_PASSWORD to a strong password}');
});

it('disables debug mode and uses the production environment in the production stack', function (): void {
    $environment = Yaml::parseFile(base_path('compose.prod.yaml'))['services']['php-fpm']['environment'];

    expect($environment['APP_ENV'])->toBe('production');
    expect($environment['APP_DEBUG'])->toBe('false');
    expect($environment['DB_HOST'])->toBe('postgres');
});

it('only publishes development services on the loopback interface', function (): void {
    $services = Yaml::parseFile(base_path('compose.dev.yaml'))['services'];

    foreach ($services as $service) {
        foreach ($service['ports'] ?? [] as $port) {
            expect($port)->toStartWith('127.0.0.1:');
        }
    }
});
