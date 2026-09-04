---
paths:
  - composer.json
---

# General

## Never pass --silent to boost:update in composer scripts
The post-update-cmd hook must stay `@php artisan boost:update --ansi`, never `--silent`.

Boost decides whether to include the "Enforce Tests" guidelines by shelling out to `php artisan test --list-tests` and counting results against a six-test minimum. Symfony Console exports verbosity to child processes as SHELL_VERBOSITY, and Process inherits the parent env, so `--silent` reaches the subprocess as SHELL_VERBOSITY=-2. It emits nothing, Boost detects zero tests, and the Test Enforcement block is stripped from CLAUDE.md and AGENTS.md on every composer update.

`|| true` plus `--silent` means this fails invisibly. Check `grep -c 'tests rules' CLAUDE.md` after changing the hook. `--no-discover` does NOT prevent this; discovery is unrelated.
