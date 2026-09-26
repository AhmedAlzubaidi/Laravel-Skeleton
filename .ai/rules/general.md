---
paths:
  - '{composer.json,composer.lock,vet.json}'
---

# General

## Dependency changes go through Laravel Vet
`laravel/vet` runs as a Composer plugin on every install/update. `vet.json` is the trust file and is committed — never gitignore or hand-edit it.

- `minimum-release-age` is 7, so any release younger than 7 days is held back and fails the audit even if trusted. Vet exempts itself; add entries to `minimum-release-age-exclude` if a package needs to skip the wait.
- After changing dependencies, run `./vendor/bin/vet` (non-zero exit = untrusted package) and commit the updated `vet.json` alongside `composer.lock`.
- Re-recording trust non-interactively: `./vendor/bin/vet --init --minimum-release-age=7 --no-interaction`. `--fresh` clears entries but keeps settings.
- The plugin needs `allow-plugins.laravel/vet: true` in composer.json.
