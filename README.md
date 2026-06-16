# Componenta Policy App

Application-level compile integration for `componenta/policy`. This package turns policy attributes into serializable descriptors that can be loaded from production cache by the runtime policy provider.

Use it in a Componenta application cache build. Runtime code that only evaluates policies should depend on `componenta/policy`.

## Installation

```bash
composer require componenta/policy-app
```

The package declares `Componenta\Policy\App\ConfigProvider` in `extra.componenta.config-providers`.
When `componenta/composer-plugin` is installed, the provider is added to the generated provider list automatically.

## Related Packages

| Package | Why it matters here |
|---|---|
| `componenta/policy` | Enforces policies and consumes compiled policy descriptors. |
| `componenta/class-finder` | Finds classes and public methods with policy attributes. |
| `componenta/app` | Runs the compiler only when policy support is installed and bound. |

## What It Adds

The package provides `Componenta\Policy\App\Compile\PolicyMapCompiler`.

The compiler scans discovered classes for policy attributes on:

- classes
- public methods

It returns a descriptor map keyed by action id. `CompiledPolicyProvider` from `componenta/policy` consumes those descriptors at runtime.

## Development Mode

In development, policy metadata may be read through reflection or rebuilt during cache warmup. This is useful while classes and attributes are changing.

## Production Mode

In production, the application should load compiled descriptors instead of scanning source files. This keeps request startup deterministic and avoids repeated reflection over command/query/controller classes.

The compiler should be enabled only when `componenta/policy` is installed and a policy provider binding exists. `componenta/app` performs that feature gating through compile support.

## Failure Behavior

Runtime policy enforcement stays in `componenta/policy`. If a compiled descriptor is stale or malformed, the runtime provider handles that as a runtime policy concern. The app package only produces the descriptor map.

## Boundaries

`componenta/policy-app` must not contain authorization decisions. Policy contracts, attributes, providers, policy composition, and enforcement belong to `componenta/policy`; this package owns only application compile integration.
