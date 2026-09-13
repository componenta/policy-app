<?php
declare(strict_types=1);

it('leaves application configuration unchanged during migration', function (): void {
    $composition = (new \Componenta\Config\ConfigFactory())->create(
        new \Componenta\Config\Environment([]),
        static fn (): array => ['kept' => true],
        new \Componenta\Policy\App\ConfigProvider(),
    );
    expect($composition->config->toArray())->toBe(['kept' => true])
        ->and($composition->dependencies->sections)->toBe([]);
});
