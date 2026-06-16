<?php

declare(strict_types=1);

use Componenta\App\ConfigKey as AppConfigKey;
use Componenta\Config\ConfigKey as DependencyConfigKey;
use Componenta\Policy\App\Compile\PolicyMapCompiler;
use Componenta\Policy\App\Compile\PolicyMapContributor;
use Componenta\Policy\App\ConfigProvider;

describe('policy app ConfigProvider', function () {
    it('registers the policy compile cache contributor', function () {
        $config = (new ConfigProvider())();

        expect($config[DependencyConfigKey::DEPENDENCIES][DependencyConfigKey::INVOKABLES])->toBe([
            PolicyMapCompiler::class,
            PolicyMapContributor::class,
        ])->and($config[AppConfigKey::COMPILE_CACHE_CONTRIBUTORS])->toBe([
            PolicyMapContributor::class,
        ]);
    });
});
