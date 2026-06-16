<?php

declare(strict_types=1);

use Componenta\Policy\App\Compile\PolicyMapContributor;
use Componenta\Policy\ConfigKey;
use Componenta\Policy\Tests\Fixture\AttributeTargets\WithClassAttribute;

describe('PolicyMapContributor', function () {
    it('returns the compiled policy map config delta', function () {
        $delta = (new PolicyMapContributor())->compile([WithClassAttribute::class]);

        expect($delta[ConfigKey::POLICY][ConfigKey::COMPILED_POLICIES])->toHaveKey(WithClassAttribute::class);
    });
});
