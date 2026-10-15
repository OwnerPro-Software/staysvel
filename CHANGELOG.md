# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.10.1] - 2026-10-15

### Changed
- Package renamed from `jonathanpmartins/staysvel` to `ownerpro/staysvel`, and the repository moved to `https://github.com/OwnerPro-Software/staysvel`. Consumers must replace `jonathanpmartins/staysvel` with `ownerpro/staysvel` in their `require`. The `Staysvel\` namespace and the public API are unchanged. The old Packagist package is abandoned in favor of the new one, which starts at this release.
- `composer.json` no longer carries a `version` field: the git tag is the source of truth for releases.
