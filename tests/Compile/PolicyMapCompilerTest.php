<?php

declare(strict_types=1);

use Componenta\Policy\App\Compile\PolicyMapCompiler;
use Componenta\Policy\App\Tests\Fixture\FactoryStub;
use Componenta\Policy\App\Tests\Fixture\Plain;
use Componenta\Policy\App\Tests\Fixture\WithClassAttribute;
use Componenta\Policy\Context\Context;
use Componenta\Policy\Provider\CompiledPolicyProvider;

it('compiles policy metadata that the runtime compiled provider can enforce', function (): void {
    $map = (new PolicyMapCompiler())->compile([
        Plain::class,
        WithClassAttribute::class,
    ]);
    $compiled = new CompiledPolicyProvider(new FactoryStub(), $map);

    expect($compiled->provideFor(Plain::class))->toBeNull()
        ->and($compiled->provideFor(WithClassAttribute::class)?->enforce(new stdClass(), new Context()))->toBeTrue();
});
