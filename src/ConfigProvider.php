<?php
declare(strict_types=1);

namespace Componenta\Policy\App;

/**
 * @deprecated Register Componenta\Policy\ConfigProvider directly.
 * Policy resolution uses attributes and an in-memory cache; no build integration is needed.
 */
final class ConfigProvider extends \Componenta\Config\ConfigProvider
{
}
