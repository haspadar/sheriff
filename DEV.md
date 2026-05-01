# DEV

## Templates

Templates are stored in three locations:

- `templates/always/`
- `templates/git/`
- `templates/once/`

### Always Templates

Location:

`templates/always/`

Structure mirrors target project root.

Everything under `templates/always/` is copied relative to project root on every `sync`, overwriting on change.

Example:

`templates/always/.github/workflows/piqule.yml`
`templates/always/.piqule/phpstan.neon`

### Git Templates

Location:

`templates/git/`

Everything under `templates/git/` is copied into:

`.git/`

Files are written with `DiffingStorage` (overwrite on change), except `pre-push` — it is appended via `AppendingStorage` using the marker `# BEGIN piqule` / `# END piqule`. This makes the operation idempotent: if the block is already present, `sync` skips it.

The appended block wires `pre-push-piqule`:

```sh
# BEGIN piqule
[ -f "$(dirname "$0")/pre-push-piqule" ] && "$(dirname "$0")/pre-push-piqule" "$@"
# END piqule
```

`pre-push-renovate` is copied to `.git/hooks/` but not wired automatically. To enable it, add the following line inside the `# BEGIN piqule / # END piqule` block in `.git/hooks/pre-push`:

```sh
[ -f "$(dirname "$0")/pre-push-renovate" ] && "$(dirname "$0")/pre-push-renovate" "$@"
```

### Once Templates

Location:

`templates/once/`

Everything under `templates/once/` is copied relative to project root only if the target file does not exist yet. User edits survive subsequent `sync` runs.

`.piqule.yaml` is generated from `templates/once/` on the first `sync`.

---

## Synchronization

Run:

`bin/piqule sync`

Flow:

1. Load `.piqule.yaml` if it exists (optional)
2. Scan `templates/always/`
3. Scan `templates/git/`
4. Scan `templates/once/`
5. Resolve placeholders
6. Write `templates/always/` → project root (overwrite on change)
7. Write `templates/git/` (non-pre-push files) → `.git/` (overwrite on change)
8. Write `templates/git/` (pre-push file) → `.git/hooks/pre-push` (append if marker absent)
9. Write `templates/once/` → project root (only if file doesn't exist)
10. Pin template checksums → `.piqule/templates.md5`

Note:

`.piqule.yaml` is generated from `templates/once/` on the first `sync`. Edit it freely — subsequent syncs will not overwrite it.

---

## Template Pinning

`bin/piqule-pin` computes a combined MD5 checksum of all files in `templates/always/` and `templates/git/` and writes it to `.piqule/templates.md5`. Called automatically by `bin/piqule sync`.

`bin/piqule-verify` compares the current checksum against the pinned value. If they differ, it prints a warning and suggests running `bin/piqule sync`. Called automatically by `bin/piqule check` and `bin/piqule fix`. Silent if `.piqule/templates.md5` does not exist.

---

## Placeholders

Syntax:

`<< config(path.to.value) >>`

Example:

`<< config(phpstan.level) >>`

### Supported actions

- `config(key)` — loads a list of values from configuration
- `envs(indent)` — renders a GitHub Actions step that exports environment variables via `$GITHUB_ENV`; returns empty string when no envs configured
- `first()` — extracts the first element from the list; empty input becomes `['']`
- `format_each(template)` — formats each list item via `sprintf`
- `join(delimiter)` — reduces the list to a single scalar value; supports escape sequences (`\n`, `\t`, `\r`, `\\`)
- `replace(search, replace)` — replaces every occurrence of `search` with `replace` in each list item; supports escape sequences (`\n`, `\t`, `\r`, `\\`); arguments are split on the first `,`, so `search` cannot contain a literal comma (any commas after the first separator are preserved as part of `replace`)
- `shell_quote()` — wraps each list item in POSIX single-quoted form for safe interpolation into shell commands; combines with `join(' ')` to produce a list of safe argv tokens
- `json_escape()` — escapes each list item for safe interpolation inside a JSON string literal (quotes, backslashes, control chars become escape sequences); does not add surrounding quotes
- `if_not_empty()` — guard: empty input (`[]` or `['']`) becomes `[]`, non-empty passes through unchanged
- `if_empty()` — inverse guard: non-empty input becomes `[]`, empty passes through unchanged
- `format(template)` — formats a single value via `sprintf`; empty input (`[]`) passes through as `[]`; supports escape sequences (`\n`, `\t`, `\r`, `\\`)

### Semantics

The DSL operates in stages:

1. `config(...)` produces a list of values
2. List-level actions:
   - `first` — picks the first element
   - `format_each`
   - `shell_quote` — wraps each item in POSIX single quotes
   - `json_escape` — escapes each item for a JSON string literal
3. `join` reduces the list to a single value
4. Conditional guards (optional, placed after `join`):
   - `if_not_empty` — drops empty values, enabling conditional block rendering
   - `if_empty` — drops non-empty values
5. Scalar-level actions:
   - `format`

When a guard returns `[]`, downstream `format` passes it through as `[]`, which resolves to an empty string in the final output. This enables conditional template blocks without `if`/`endif` syntax.

### Examples

List formatting:

`<< config(phpunit.testsuites.unit)|format_each("            <directory>%s</directory>")|join("\n") >>`

Final value formatting:

`<< config(phpstan.level)|join(",")|format('level: %s') >>`

Conditional block (rendered only when config key is non-empty):

`<< config(psalm.project.files)|format_each('        <file name="%s" />')|join("\n")|if_not_empty()|format('<handler>\n%s\n</handler>') >>`

Shell argv composition (safe interpolation of arbitrary values into a shell command):

`<< config(phpunit.php_options)|shell_quote()|join(' ') >>`

JSON string interpolation (safe insertion of arbitrary values into a JSON string literal):

`"description": "<< config(project.description)|json_escape()|join("") >>"`

---

## Project Configuration

Optional file:

`.piqule.yaml`

Example:

```yaml
override:
    phpstan.level: 8
    psalm.suppress.possibly_unused: ["../../src"]

append:
    exclude:
        - legacy
```

Keys are flat and use dot-separated names. All valid keys are declared in `templates/always/.piqule/config.yaml`.

`override` replaces the default value entirely. `append` adds to the default list.

### Environment variables

The `envs` section declares environment variables exported in CI workflows before dependency installation. Each value is a shell command whose stdout becomes the variable value at runtime:

```yaml
envs:
    COMPOSER_ROOT_VERSION: "git describe --tags --abbrev=0 | sed 's/^v//'"
```

This generates a workflow step:

```yaml
      - name: Set environment variables
        run: |
          git fetch --tags --unshallow 2>/dev/null || git fetch --tags
          echo "COMPOSER_ROOT_VERSION=$(git describe --tags --abbrev=0 | sed 's/^v//')" >> "$GITHUB_ENV"
```

Variable names must match `^[A-Za-z_][A-Za-z0-9_]*$`. If `envs` is absent or empty, no step is generated.

If the file does not exist, defaults are used.

---

## Infra Image

Runtime image is selected via:

- `.piqule.yaml` → `docker.image`
- `PIQULE_INFRA_IMAGE` environment variable (highest priority)

Execution is delegated to `.piqule/_docker.sh`.

---

## Infra Image Build

Build:

```bash
docker buildx build -t ghcr.io/haspadar/piqule-infra:local --load .
```

Run shell:

```bash
docker run --rm -it \
  --entrypoint bash \
  -v "$PWD:/project" \
  -w /project \
  ghcr.io/haspadar/piqule-infra:local
```

---

## Tool Versions

Pinned inside the infra image.

Updated via Renovate.

---

## Architecture Overview

Source code is organized into layers. Each layer depends only on its own interfaces:

```
Config → Formula → File → Files → Storage
```

- **Config** (`src/Config/`) — flat key-value store; `DefaultConfig` declares all valid keys, `OverrideConfig` applies user overrides
- **Formula** (`src/Formula/`) — evaluates `<< ... >>` placeholder expressions via a pipeline of `Action` objects
- **File** (`src/File/`) — represents a single file with `name()`, `contents()`, `mode()`; decorators add behaviour (placeholder resolution, path prefix, string replacement)
- **Files** (`src/Files/`) — iterable collection of `File` objects; composable via decorators
- **Storage** (`src/Storage/`) — filesystem abstraction; decorators add write policy (diffing, once-only, or appending) and reactions
- **Output** (`src/Output/`) — console output interface; `Console` writes to stdout, `Message` is a value object for a single line

For a full description of every class and the decorator pattern, see [docs/architecture.md](docs/architecture.md).

---

## Adding a New Tool

1. Create `templates/always/.piqule/<tool>/` and add a `command.sh` inside it
2. Add any config keys the tool needs to `src/Config/DefaultConfig.php` (`DEFAULTS` array)
3. Register the new key type in `src/Config/OverrideConfig.php` (`OverrideMap` PHPDoc)
4. Add the tool name to `$checks` in `bin/piqule-check`
5. Add `'<tool>.cli' => true` to `DefaultConfig` and `'<tool>.cli'?: bool` to `OverrideMap` in `OverrideConfig`
6. Run `vendor/bin/piqule sync` to verify template rendering
7. Write unit and integration tests

---

## Adding a Config Key

1. Add the key to `DEFAULTS` in `src/Config/DefaultConfig.php`:
   - Scalar value → stored as-is, returned as single-element list
   - List value → `list<scalar>`
2. Add the corresponding entry to the `OverrideMap` PHPDoc type in `src/Config/OverrideConfig.php`
3. Use the key in a template placeholder: `<< config(my.new.key) >>`

Keys are flat dot-separated names. Accessing an undeclared key throws `PiquleException`.

---

## Secrets and Environment Variables

External services require credentials. They are split into two categories:

- **Secrets** (`src/Secret/Secret.php`) — GitHub Secrets for CI. Verified via `gh secret list`. Output prefixed with `[SECRET]`.
- **Environment variables** (`src/EnvVar/EnvVar.php`) — local env vars on the developer machine. Verified via `getenv()`. Output prefixed with `[ENV]`.

`bin/piqule-tokens-check` verifies both. Runs automatically after `piqule sync`, `piqule check` and `piqule fix`.

If `gh` is not installed or not authenticated, only secret verification is skipped; env var checks still run.

### Secret Interface

Each secret implements `Secret` (`src/Secret/Secret.php`):

- `name()` — GitHub Secret name (e.g. `CODECOV_TOKEN`)
- `url(string $org)` — URL where the user can obtain the secret
- `enabled(Config $config)` — whether the secret is needed based on config

### EnvVar Interface

Each env var implements `EnvVar` (`src/EnvVar/EnvVar.php`):

- `name()` — environment variable name (e.g. `SONAR_TOKEN`)
- `url()` — URL where the user can obtain the value
- `enabled(Config $config)` — whether the env var is needed based on config

### Existing Credentials

| Class | Name | Type | Enabled when |
|-------|------|------|-------------|
| `CodecovSecret` | `CODECOV_TOKEN` | Secret | `phpunit.cli` is true |
| `InfectionSecret` | `STRYKER_DASHBOARD_API_KEY` | Secret | `infection.cli` is true |
| `SonarEnvVar` | `SONAR_TOKEN` | EnvVar | `sonar.cloud` is false and `sonar.cli` is true |

### Adding a Secret

1. Create a class in `src/Secret/` implementing `Secret`
2. Register it in `bin/piqule-tokens-check` inside the `Secrets` array
3. Write unit tests

### Adding an Environment Variable

1. Create a class in `src/EnvVar/` implementing `EnvVar`
2. Register it in `bin/piqule-tokens-check` inside the `EnvVars` array
3. Write unit tests

---

## Configuration Reference

All keys below are declared in `templates/always/.piqule/config.yaml` with their defaults. Override any key in `.piqule.yaml` under `override` or extend lists under `append`.

### Global

| Key | Default | Description |
|-----|---------|-------------|
| `php.src` | `["src"]` | Source directories — cascades to PHPStan, Psalm, PHPUnit, Infection, PHPMD, PHPCS, PHP Metrics, SonarQube |
| `exclude` | `["vendor", "tests", ".git"]` | Excluded directories — cascades to all tools |
| `php.versions` | `["8.3"]` | PHP versions for CI matrix |

### Check Groups

| Key | Default | Description |
|-----|---------|-------------|
| `check.full` | `false` | Include slow checks by default (`-f`/`--full` to force, `-F`/`--no-full` to disable) |
| `check.parallel` | `true` | Run checks concurrently by default (`-p`/`--parallel` to force, `-P`/`--no-parallel` to disable) |
| `check.slow` | `["infection", "sonar"]` | Checks excluded unless `--full` is passed or `check.full` is `true` |

### CI

| Key | Default | Description |
|-----|---------|-------------|
| `ci.piqule_bin` | `"vendor/bin/snob"` | Path to Snob binary in CI |
| `ci.pr.max_lines_changed` | `250` | Maximum lines changed per PR |

### Coverage

| Key | Default | Description |
|-----|---------|-------------|
| `coverage.patch.target` | `80` | Minimum patch coverage % |
| `coverage.patch.threshold` | `5` | Allowed drop below target |
| `coverage.project.target` | `80` | Minimum project coverage % |
| `coverage.project.threshold` | `2` | Allowed drop below target |

### codecov

| Key | Default | Description |
|-----|---------|-------------|
| `codecov.cloud` | `true` | Upload coverage to Codecov in CI |
| `codecov.cli` | `false` | Enable codecov-cli locally |

### coderabbit

| Key | Default | Description |
|-----|---------|-------------|
| `coderabbit.cloud` | `true` | Enable CodeRabbit GitHub App reviews |
| `coderabbit.cli` | `false` | Enable coderabbit-cli locally |

### Docker

| Key | Default | Description |
|-----|---------|-------------|
| `docker.image` | `"ghcr.io/haspadar/piqule-infra@sha256:..."` | Infra image for CI |

### actionlint

| Key | Default | Description |
|-----|---------|-------------|
| `actionlint.cli` | `true` | Enable GitHub Actions linting |

### hadolint

| Key | Default | Description |
|-----|---------|-------------|
| `hadolint.cli` | `true` | Enable Dockerfile linting |
| `hadolint.failure_threshold` | `"error"` | Minimum severity to fail |
| `hadolint.ignore` | `["vendor", "tests", ".git"]` | Ignored directories |
| `hadolint.ignored_yaml` | `"[]"` | Ignored rules (YAML literal) |
| `hadolint.override.error_yaml` | `"[]"` | Rules promoted to error (YAML literal) |
| `hadolint.override.warning_yaml` | `"[]"` | Rules promoted to warning (YAML literal) |
| `hadolint.patterns` | `["Dockerfile*"]` | File patterns to lint |

### jsonlint

| Key | Default | Description |
|-----|---------|-------------|
| `jsonlint.cli` | `true` | Enable JSON linting |
| `jsonlint.compact` | `true` | Compact output |
| `jsonlint.continue` | `true` | Continue on error |
| `jsonlint.duplicate_keys` | `false` | Allow duplicate keys |
| `jsonlint.mode` | `["json5"]` | Parsing mode |
| `jsonlint.patterns` | `["**/*.json", ...]` | File patterns to lint |

### markdownlint

| Key | Default | Description |
|-----|---------|-------------|
| `markdownlint.cli` | `true` | Enable Markdown linting |
| `markdownlint.ignores` | `["vendor/**", "tests/**", ".git/**"]` | Ignored patterns |

### php-cs-fixer

| Key | Default | Description |
|-----|---------|-------------|
| `php-cs-fixer.cli` | `true` | Enable PHP CS Fixer |
| `php_cs_fixer.allow_unsupported` | `["true"]` | Allow unsupported PHP versions |
| `php_cs_fixer.exclude` | `["vendor", "tests", ".git"]` | Excluded directories |
| `php_cs_fixer.extend` | `""` | Raw PHP fragment inserted at the end of the `setRules()` array (overrides preset and built-in entries) |
| `php_cs_fixer.paths` | `["../.."]` | Paths to fix |

### phpcs

| Key | Default | Description |
|-----|---------|-------------|
| `phpcs.cli` | `true` | Enable PHP_CodeSniffer |
| `phpcs.excludes` | `["vendor/*", "tests/*", ".git/*"]` | Excluded patterns |
| `phpcs.extend` | `""` | Raw XML fragment inserted at the end of the generated `<ruleset>` (lets you silence or tune inherited sniffs) |
| `phpcs.files` | `["../../src"]` | Files/directories to check |
| `phpcs.root_namespace` | `""` | Root namespace for PSR-4 check |

### phpmd

| Key | Default | Description |
|-----|---------|-------------|
| `phpmd.cli` | `true` | Enable PHP Mess Detector |
| `phpmd.paths` | `["src"]` | Source paths |
| `phpmd.class_complexity` | `50` | Max class complexity |
| `phpmd.class_length` | `200` | Max class length |
| `phpmd.cyclomatic` | `10` | Max cyclomatic complexity |
| `phpmd.max_fields` | `10` | Max fields per class |
| `phpmd.max_methods` | `10` | Max methods per class |
| `phpmd.max_parameters` | `5` | Max parameters per method |
| `phpmd.method_length` | `50` | Max method length |
| `phpmd.npath` | `200` | Max NPath complexity |

### phpmetrics

| Key | Default | Description |
|-----|---------|-------------|
| `phpmetrics.cli` | `true` | Enable PHP Metrics |
| `phpmetrics.includes` | `["../../src"]` | Included directories |
| `phpmetrics.excludes` | `["vendor", "tests", ".git"]` | Excluded directories |
| `phpmetrics.extensions` | `["php"]` | File extensions |
| `phpmetrics.complexity.max_cyclomatic_per_method` | `10` | Max cyclomatic per method |
| `phpmetrics.complexity.max_weighted_methods_per_class` | `20` | Max WMC per class |
| `phpmetrics.coupling.max_afferent` | `14` | Max afferent coupling |
| `phpmetrics.coupling.max_efferent` | `10` | Max efferent coupling |
| `phpmetrics.halstead.max_bugs_per_method` | `0.5` | Max Halstead bugs per method |
| `phpmetrics.halstead.max_difficulty_per_method` | `15` | Max Halstead difficulty |
| `phpmetrics.halstead.max_effort_per_method` | `15000` | Max Halstead effort |
| `phpmetrics.halstead.max_volume_per_method` | `1000` | Max Halstead volume |
| `phpmetrics.inheritance.max_depth` | `3` | Max inheritance depth |
| `phpmetrics.report.html` | `["html"]` | HTML report directory |
| `phpmetrics.report.json` | `["phpmetrics.json"]` | JSON report file |
| `phpmetrics.size.max_loc_per_class` | `1000` | Max LOC per class |
| `phpmetrics.size.max_logical_loc_per_class` | `600` | Max logical LOC per class |
| `phpmetrics.size.max_logical_loc_per_method` | `20` | Max logical LOC per method |
| `phpmetrics.structure.max_methods_per_class` | `10` | Max methods per class |

### phpstan

| Key | Default | Description |
|-----|---------|-------------|
| `phpstan.cli` | `true` | Enable PHPStan |
| `phpstan.level` | `9` | Analysis level (0-9) |
| `phpstan.memory` | `"1G"` | Memory limit |
| `phpstan.paths` | `["../../src"]` | Paths to analyze |
| `phpstan.checked_exceptions` | `['\Throwable']` | Checked exception classes |
| `phpstan.neon_includes` | `["../../vendor/phpstan/phpstan-strict-rules/rules.neon", "../../vendor/haspadar/phpstan-rules/rules.neon"]` | Neon includes |
| `phpstan.afferent_coupling.ignore_interfaces` | `true` | Skip interfaces when counting afferent coupling (haspadar rule) |
| `phpstan.afferent_coupling.excluded_classes` | `[]` | FQCNs excluded from the haspadar afferent coupling rule |

### phpunit

| Key | Default | Description |
|-----|---------|-------------|
| `phpunit.cli` | `true` | Enable PHPUnit |
| `phpunit.php_options` | `"-d memory_limit=1G"` | PHP CLI options |
| `phpunit.source.include` | `["../../src"]` | Source directories for coverage |
| `phpunit.testsuites.unit` | `["../../tests/Unit"]` | Unit test directories |
| `phpunit.testsuites.integration` | `["../../tests/Integration"]` | Integration test directories |

### psalm

| Key | Default | Description |
|-----|---------|-------------|
| `psalm.cli` | `true` | Enable Psalm |
| `psalm.error_level` | `1` | Error level (1-8) |
| `psalm.project.directories` | `["../../src"]` | Directories to analyze |
| `psalm.project.files` | `[]` | Individual files to analyze |
| `psalm.project.ignore` | `["../../vendor", "../../tests", "../../.git"]` | Ignored directories |
| `psalm.suppress.possibly_unused` | `[]` | Directories to suppress PossiblyUnusedMethod (e.g. `["../../src"]` for DI-constructed classes) |

### infection

| Key | Default | Description |
|-----|---------|-------------|
| `infection.cli` | `true` | Enable Infection mutation testing |
| `infection.min_msi` | `50` | Minimum Mutation Score Indicator (0–100) |
| `infection.min_covered_msi` | `80` | Minimum Covered Code MSI (0–100) |
| `infection.php_options` | `"-d memory_limit=1G"` | PHP CLI options |
| `infection.source.directories` | `["../../src"]` | Source directories |
| `infection.timeout` | `30` | Timeout per mutant (seconds) |

### shellcheck

| Key | Default | Description |
|-----|---------|-------------|
| `shellcheck.cli` | `true` | Enable ShellCheck |
| `shellcheck.exclude` | `[]` | Excluded rule codes |
| `shellcheck.external_sources` | `true` | Follow sourced files |
| `shellcheck.ignore_dirs` | `["vendor", "tests", ".git"]` | Ignored directories |
| `shellcheck.severity` | `"warning"` | Minimum severity |
| `shellcheck.shell` | `"bash"` | Shell dialect |
| `shellcheck.source_path` | `"."` | Source path for includes |

### sonar

| Key | Default | Description |
|-----|---------|-------------|
| `sonar.cloud` | `true` | Use SonarCloud automatic analysis (skip local scanner and SONAR_TOKEN) |
| `sonar.cli` | `false` | Enable local sonar-scanner |
| `sonar.organization` | `[]` | SonarCloud organization |
| `sonar.projectKey` | `[]` | SonarCloud project key |
| `sonar.sources` | `["src"]` | Source directories |
| `sonar.tests` | `["tests"]` | Test directories |
| `sonar.exclusions` | `[]` | Excluded paths |
| `sonar.php.coverage.reportPaths` | `[".piqule/codecov/coverage.xml"]` | Coverage report path |

### typos

| Key | Default | Description |
|-----|---------|-------------|
| `typos.cli` | `true` | Enable typo checking |
| `typos.exclude` | `["vendor/", "tests/", ".git/"]` | Excluded directories |

### yamllint

| Key | Default | Description |
|-----|---------|-------------|
| `yamllint.cli` | `true` | Enable YAML linting |
| `yamllint.ignore` | `["vendor/**", "tests/**", ...]` | Ignored patterns |
| `yamllint.line_length.max` | `120` | Maximum line length |
