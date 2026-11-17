---
paths:
  - '{docker/**,compose.*.yaml,.dockerignore,Makefile}'
---

# Docker

## Container security and credentials
Keep production application source and entrypoints owned by root; only storage and bootstrap/cache are writable by PHP. Supply environment files and Passport keys at runtime, and exclude database backups, credentials and cached configuration from build contexts. PostgreSQL runs as postgres, so its image deliberately removes the unused gosu binary; do not switch that image back to root.
