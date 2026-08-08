<?php

declare(strict_types=1);

namespace Componenta\Policy\App\Tests\Fixture;

use Componenta\DI\FactoryInterface;

final class FactoryStub implements FactoryInterface
{
    public function make(string $entry, array $params = []): object
    {
        return new $entry(...$params);
    }
}
