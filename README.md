# Policy application integration

This package is deprecated. Register `Componenta\Policy\ConfigProvider` from
`componenta/policy` directly and remove `componenta/policy-app` from your dependencies.

Policy resolution uses native attributes in every environment. The provider keeps
resolved policies in memory for its lifetime. Policy constructors and dependency
resolution retain their normal semantics.

Disk compilation has been removed. Remove `compiled_policies`,
`compiled_policies_file` and `compiled_policies_strict` from your policy settings;
old artifacts are unused. No policy builder is registered by `app:build`.

The empty legacy `Componenta\Policy\App\ConfigProvider` remains available so an
existing generated provider list can be regenerated during migration.
