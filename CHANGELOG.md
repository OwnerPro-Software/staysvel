# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.11.0] - 2026-11-27

### Added
- `Stays::whenUnreachable(?Closure $callback)`: a process-wide hook that decides what the SDK throws when Stays cannot be reached (timeout, refused connection, DNS failure). The hook receives the `Illuminate\Http\Client\ConnectionException`, the HTTP verb (`GET`, `POST`, `PATCH` or `DELETE`) and the request URI (e.g. `/content/listings/123`) and returns the `Throwable` to be thrown in its place. It covers every SDK call, including the XLSX export. `Stays::whenUnreachable(null)` removes the hook. Without a hook the `ConnectionException` escapes as before, so the change is backward compatible.

## [1.10.1] - 2026-10-15

### Changed
- Package renamed from `jonathanpmartins/staysvel` to `ownerpro/staysvel`, and the repository moved to `https://github.com/OwnerPro-Software/staysvel`. Consumers must replace `jonathanpmartins/staysvel` with `ownerpro/staysvel` in their `require`. The `Staysvel\` namespace and the public API are unchanged. The old Packagist package is abandoned in favor of the new one, which lists every release from 1.0.0 onward.
- `composer.json` no longer carries a `version` field: the git tag is the source of truth for releases.
