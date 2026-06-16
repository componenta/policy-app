<?php

declare(strict_types=1);

namespace Componenta\Policy\App;

use Componenta\App\ConfigKey as AppConfigKey;
use Componenta\Config\ConfigProvider as BaseConfigProvider;
use Componenta\Policy\App\Compile\PolicyMapContributor;
use Componenta\Policy\App\Compile\PolicyMapCompiler;

final class ConfigProvider extends BaseConfigProvider
{
    protected function getInvokables(): array
    {
        return [
            PolicyMapCompiler::class,
            PolicyMapContributor::class,
        ];
    }

    protected function getConfig(): array
    {
        return [
            AppConfigKey::COMPILE_CACHE_CONTRIBUTORS => [
                PolicyMapContributor::class,
            ],
        ];
    }
}
