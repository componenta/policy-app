<?php

declare(strict_types=1);

use Componenta\Policy\App\Compile\PolicyMapCompiler;
use Componenta\Policy\Context\Context;
use Componenta\Policy\Provider\AttributePolicyProvider;
use Componenta\Policy\Provider\CompiledPolicyProvider;
use Componenta\Policy\Tests\Fixture\AttributeTargets\InjectedPolicy;
use Componenta\Policy\Tests\Fixture\AttributeTargets\Plain;
use Componenta\Policy\Tests\Fixture\AttributeTargets\WithClassAttribute;
use Componenta\Policy\Tests\Fixture\AttributeTargets\WithComposite;
use Componenta\Policy\Tests\Fixture\AttributeTargets\WithPolicyAttribute;
use Componenta\Policy\Tests\Fixture\AttributeTargets\WithTwoPolicies;
use Componenta\Policy\Tests\Fixture\FakeActor;
use Componenta\Policy\Tests\Fixture\FakeFactory;
use Componenta\Policy\Tests\Fixture\FakeRole;

describe('PolicyMapCompiler', function () {
    it('compiles policy metadata that the runtime compiled provider can enforce', function () {
        $map = (new PolicyMapCompiler())->compile([
            Plain::class,
            WithClassAttribute::class,
            WithTwoPolicies::class,
            WithComposite::class,
            WithPolicyAttribute::class,
        ]);
        $compiled = new CompiledPolicyProvider(new FakeFactory(), $map);
        $attribute = new AttributePolicyProvider(new FakeFactory());
        $admin = new FakeActor(1, new FakeRole('admin'));
        $editorWithPermission = new FakeActor(2, new FakeRole('editor', ['posts.update']));
        $guest = new FakeActor(3, new FakeRole('guest'));

        expect($compiled->provideFor(Plain::class))->toBeNull()
            ->and($compiled->provideFor(WithClassAttribute::class)?->enforce($admin, new Context()))->toBeTrue()
            ->and($compiled->provideFor(WithClassAttribute::class)?->enforce($guest, new Context()))->not->toBeTrue()
            ->and($compiled->provideFor(WithTwoPolicies::class . '::method')?->enforce($editorWithPermission, new Context()))
            ->toBe($attribute->provideFor(WithTwoPolicies::class . '::method')?->enforce($editorWithPermission, new Context()))
            ->and($compiled->provideFor(WithComposite::class . '::method')?->enforce($admin, new Context()))->toBeTrue()
            ->and($compiled->provideFor(WithComposite::class . '::method')?->enforce($guest, new Context()))->not->toBeTrue()
            ->and($compiled->provideFor(WithPolicyAttribute::class . '::method'))->toBeInstanceOf(InjectedPolicy::class);
    });
});
