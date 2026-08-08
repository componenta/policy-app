<?php

declare(strict_types=1);

namespace Componenta\Policy\App\Compile;

use Componenta\App\Discovery\Compile\CompileCacheContributorInterface;
use Componenta\Policy\ConfigKey;

final readonly class PolicyMapContributor implements CompileCacheContributorInterface
{
    /**
     * @param list<class-string> $classes
     *
     * @return array<string, mixed>
     */
    public function compile(array $classes): array
    {
        $policies = (new PolicyMapCompiler())->compile($classes);

        return $policies === []
            ? []
            : [
                ConfigKey::POLICY => [ConfigKey::COMPILED_POLICIES => $policies],
            ];
    }
}
