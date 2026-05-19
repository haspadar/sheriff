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

`templates/always/.github/workflows/sheriff.yml`
`templates/always/.sheriff/phpstan.neon`

### Git Templates

Location:

`templates/git/`

Everything under `templates/git/` is copied into:

`.git/`

Files are written with `DiffingStorage` (overwrite on change), except `pre-push` — it is appended via `AppendingStorage` using the marker `# BEGIN sheriff` / `# END sheriff`. This makes the operation idempotent: if the block is already present, `sync` skips it.

The appended block wires `pre-push-sheriff`:

```sh
# BEGIN sheriff
[ -f "$(dirname "$0")/pre-push-sheriff" ] && "$(dirname "$0")/pre-push-sheriff" "$@"
# END sheriff
```

`pre-push-renovate` is copied to `.git/hooks/` but not wired automatically. To enable it, add the following line inside the `# BEGIN sheriff / # END sheriff` block in `.git/hooks/pre-push`:

```sh
[ -f "$(dirname "$0")/pre-push-renovate" ] && "$(dirname "$0")/pre-push-renovate" "$@"
```

### Once Templates

Location:

`templates/once/`

Everything under `templates/once/` is copied relative to project root only if the target file does not exist yet. User edits survive subsequent `sync` runs.

`.sheriff.yaml` is generated from `templates/once/` on the first `sync`.

---

## Synchronization

Run:

`bin/sheriff sync`

Flow:

1. Load `.sheriff.yaml` if it exists (optional)
2. Scan `templates/always/`
3. Scan `templates/git/`
4. Scan `templates/once/`
5. Resolve placeholders
6. Write `templates/always/` → project root (overwrite on change)
7. Write `templates/git/` (non-pre-push files) → `.git/` (overwrite on change)
8. Write `templates/git/` (pre-push file) → `.git/hooks/pre-push` (append if marker absent)
9. Write `templates/once/` → project root (only if file doesn't exist)
10. Pin template checksums → `.sheriff/templates.md5`

Note:

`.sheriff.yaml` is generated from `templates/once/` on the first `sync`. Edit it freely — subsequent syncs will not overwrite it.

---

## Template Pinning

`bin/sheriff-pin` computes a combined MD5 checksum of all files in `templates/always/` and `templates/git/` and writes it to `.sheriff/templates.md5`. Called automatically by `bin/sheriff sync`.

`bin/sheriff-verify` compares the current checksum against the pinned value. If they differ, it prints a warning and suggests running `bin/sheriff sync`. Called automatically by `bin/sheriff check` and `bin/sheriff fix`. Silent if `.sheriff/templates.md5` does not exist.

---

## Placeholders

Syntax:

`{% Op(settings.key) %}`

Templates resolve placeholders via the Chain DSL: a settings key feeds a typed source op, which can be transformed through map ops and collapsed by a reduce op.

Example:

`{% IntText(phpstan.level) %}`

### Pipeline stages

1. **Source** — instantiated from a settings key. The resolved `Value` is fed into the constructor; type mismatches surface as PHP `TypeError` so misconfigured templates fail loudly.
2. **Map (optional)** — wraps the previous op into a decorator. Listed sources stay listed for further iteration.
3. **Reduce (optional)** — collapses a Listed pipeline into a single string.

### Source ops

| Op | Settings type | Renders |
|----|----|----|
| `IntText(key)` | `IntValue` | decimal integer (`9`) |
| `FloatText(key)` | `FloatValue` | decimal float (`0.5`); rejects `INF`/`NAN` |
| `BoolText(key)` | `BoolValue` | `true` / `false` |
| `StringText(key)` | `StringValue` | raw string, no quoting |
| `ListText(key)` | `ListValue` | Listed pipeline of per-item sources |
| `EnvsText(key, indent)` | `TreeValue` (or empty `ListValue`) | GitHub Actions step exporting env vars; empty tree → empty string |
| `NeonTree(key, depth)` | `TreeValue` | nested neon block mapping; `depth` is optional (default `0`) and controls indentation |
| `EnabledTools(key)` | `ListValue` of tool names | Listed pipeline of names whose `<name>.cli` flag is `true`; fails fast on missing or non-boolean flags, and when the resolved list is empty |

### Map ops

| Op | Effect |
|----|----|
| `Formatted("tpl")` | applies a sprintf template to a single rendered value |
| `EachFormatted("tpl")` | sprintf template applied to each part of a Listed pipeline |
| `Replaced("needle", "replacement")` | `str_replace` over a single rendered value |
| `EachReplaced("needle", "replacement")` | `str_replace` over each part of a Listed pipeline |
| `WhenNotEmpty("tpl")` | applies the sprintf template only when the source rendered output is non-empty; otherwise renders empty string (suppresses surrounding markup) |

### Reduce ops

| Op | Effect |
|----|----|
| `Joined("sep")` | concatenates all parts of a Listed pipeline with the given separator |
| `First()` | renders only the first part; throws when the pipeline is empty |

### Examples

List formatting:

`{% ListText(phpunit.testsuites.unit)|EachFormatted("            <directory>%s</directory>")|Joined("\n") %}`

Per-item rewriting before formatting:

`{% ListText(php.versions)|EachReplaced(".", "x")|EachFormatted("        '@PHP%sMigration' => true,")|Joined("\n") %}`

Single-value formatting:

`{% IntText(coverage.project.target)|Formatted("%s%%") %}`

Optional block (rendered only when the source list is non-empty):

`{% ListText(psalm.project.files)|EachFormatted('        <file name="%s" />')|Joined("\n")|WhenNotEmpty("<handler>\n%s\n</handler>") %}`

Picking the first list element:

`{% ListText(php.versions)|First() %}`

Tree rendering (neon block):

`{% NeonTree(phpstan.parameters, 1) %}`

Environment-variable export step for GitHub Actions:

`{% EnvsText(envs, "      ") %}`

---

## Project Configuration

Optional file:

`.sheriff.yaml`

Example:

```yaml
override:
    phpstan.parameters:
        level: 7
        haspadar:
            afferentCoupling:
                ignoreInterfaces: false

append:
    infra.exclude:
        - dist
    phpstan.parameters:
        haspadar:
            afferentCoupling:
                excludedClasses:
                    - '\App\MyException'
        ignoreErrors:
            - '#Pattern to ignore#'

remove:
    phpstan.parameters:
        haspadar:
            afferentCoupling:
                excludedClasses:
                    - '\App\OldException'
```

Keys use dot-separated names at the top level. All valid keys are declared in `templates/always/.sheriff/config.yaml`. Tree-typed keys (e.g. `phpstan.parameters`) carry nested mappings under the same top-level key.

The three operations apply at every depth of a tree-typed key:

| Leaf type | `override` | `append` | `remove` |
|---|---|---|---|
| scalar | replaces the value | error (use `override`) | error (use `override`) |
| list | replaces the whole list | concatenates new entries after the existing ones | drops the named string entries |
| tree | walks deeper into the matching subtree | adds missing keys, recurses into matching subtrees | recurses into matching subtrees; a list-of-strings spec at a tree position drops those keys |

Missing keys under `append:` / `remove:` are silently ignored so `.sheriff.yaml` stays idempotent across upgrades. Type collisions (e.g. appending a tree to a list leaf) raise `SheriffException` with the dotted path of the offending entry.

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

- `.sheriff.yaml` → `docker.image`
- `SHERIFF_INFRA_IMAGE` environment variable (highest priority)

Execution is delegated to `.sheriff/_docker.sh`.

---

## Infra Image Build

Build:

```bash
docker buildx build -t ghcr.io/haspadar/sheriff-infra:local --load .
```

Run shell:

```bash
docker run --rm -it \
  --entrypoint bash \
  -v "$PWD:/project" \
  -w /project \
  ghcr.io/haspadar/sheriff-infra:local
```

---

## Tool Versions

Pinned inside the infra image.

Updated via Renovate.

---

## Architecture Overview

Source code is organized into layers. Each layer depends only on its own interfaces:

```
Settings → Chain → File → Files → Storage
```

- **Settings** (`src/Settings/`) — typed key-value store. `DefaultSettings` reads `templates/always/.sheriff/config.yaml`; `PatchedSettings` applies one `Patch` (override / append / remove) on top; `ComposerSettings` derives extra keys (e.g. `phpcs.root_namespace`) from the project's `composer.json`. Values implement the `Value` interface — `IntValue`, `FloatValue`, `BoolValue`, `StringValue`, `ListValue`, `TreeValue`. `BoolSetting` is a small helper for strict boolean lookups with a default.
- **Chain** (`src/Chain/`) — DSL that resolves `{% Op(settings.key)|Op(...)|Op(...) %}` template placeholders. Source ops live in `Chain/Plain/` (format-neutral text) and `Chain/Render/<Format>/` (format-specific renderers like `NeonTree`); `Chain/Map/` and `Chain/Reduce/` hold transformations. `Chain/Parse/` translates a placeholder string into a pipeline of ops.
- **File** (`src/File/`) — represents a single file with `name()`, `contents()`, `mode()`; decorators add behaviour (`TemplateFile` resolves `{% %}` placeholders, `PrefixedFile` rewrites the path).
- **Files** (`src/Files/`) — iterable collection of `File` objects; composable via decorators.
- **Storage** (`src/Storage/`) — filesystem abstraction; decorators add write policy (diffing, once-only, or appending) and reactions.
- **Output** (`src/Output/`) — console output interface; `Console` writes to stdout, `Message` is a value object for a single line.
- **Check** (`src/Check/`) — discovers and runs tool checks; `ConfigChecks` enumerates available `<tool>.cli` keys via `Settings::keys()`, `EnabledChecks`/`FastChecks` filter on settings, `ParallelRun`/`SequentialRun` execute them.

---

## Adding a New Tool

1. Create `templates/always/.sheriff/<tool>/` and add a `command.sh` inside it
2. Add any settings keys the tool needs to the `defaults:` section of `templates/always/.sheriff/config.yaml` (typed YAML scalars, lists, or maps)
3. Add `<tool>.cli: true` to the same defaults section so `EnabledChecks` keeps the tool on by default
4. Reference the keys from the template via the Chain DSL (`{% StringText(<tool>.foo) %}`, `{% ListText(<tool>.bar)|EachFormatted("- %s")|Joined("\n") %}`, etc.)
5. Run `bin/sheriff sync` to verify template rendering
6. Write unit and integration tests

---

## Adding a Settings Key

1. Add the key to the `defaults:` section of `templates/always/.sheriff/config.yaml` with a typed default (scalar, list, or map). The YAML type drives the `Value` subclass (`IntValue`, `StringValue`, `ListValue`, `TreeValue`, …).
2. Reference the key from a template through the matching source op (e.g. `{% IntText(my.new.key) %}` for an int).
3. Optionally let the user override or extend it via `override:`, `append:`, or `remove:` in `.sheriff.yaml`. Each verb resolves to a `Patch` (`OverrideScalar/List/Tree`, `AppendList/Tree`, `RemoveList/Tree`).

Keys are flat dot-separated names. Accessing an undeclared key throws `SheriffException`. Type mismatches between the source op and the stored `Value` surface as PHP `TypeError` at render time.

Note: an empty YAML mapping `{}` parses as an empty PHP array, which `RawValue` wraps as an empty `ListValue` — not `TreeValue`. `OverrideTree` accepts that shape as an empty tree to keep the override flow predictable. Defaults that need a tree-typed key with a non-empty schema should ship at least one entry.

---

## Secrets and Environment Variables

External services require credentials. They are split into two categories:

- **Secrets** (`src/Secret/Secret.php`) — GitHub Secrets for CI. Verified via `gh secret list`. Output prefixed with `[SECRET]`.
- **Environment variables** (`src/EnvVar/EnvVar.php`) — local env vars on the developer machine. Verified via `getenv()`. Output prefixed with `[ENV]`.

`bin/sheriff-tokens-check` verifies both. Runs automatically after `sheriff sync`, `sheriff check` and `sheriff fix`.

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
2. Register it in `bin/sheriff-tokens-check` inside the `Secrets` array
3. Write unit tests

### Adding an Environment Variable

1. Create a class in `src/EnvVar/` implementing `EnvVar`
2. Register it in `bin/sheriff-tokens-check` inside the `EnvVars` array
3. Write unit tests

---

## Configuration Reference

All keys below are declared in `templates/always/.sheriff/config.yaml` with their defaults. Override any key in `.sheriff.yaml` under `override` or extend lists under `append`.

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
| `ci.sheriff_bin` | `"vendor/bin/sheriff"` | Path to Sheriff binary in CI |
| `ci.pr.max_lines_changed` | `250` | Maximum lines changed per PR |
| `ci.infra_checks` | `["actionlint", "hadolint", "markdownlint", "yamllint", "typos", "shellcheck", "jsonlint"]` | Tools placed into the infra CI matrix; filtered by each tool's `<name>.cli` flag. Override or `append` in `.sheriff.yaml` to extend the matrix |
| `ci.php_checks` | `["phpcs", "phpstan", "psalm", "phpmd", "phpmetrics"]` | Tools placed into the PHP-static CI matrix; filtered by each tool's `<name>.cli` flag. Override or `append` in `.sheriff.yaml` to extend the matrix |

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
| `docker.image` | `"ghcr.io/haspadar/sheriff-infra@sha256:..."` | Infra image for CI |

### actionlint

| Key | Default | Description |
|-----|---------|-------------|
| `actionlint.cli` | `true` | Enable GitHub Actions linting |

### hadolint

| Key | Default | Description |
|-----|---------|-------------|
| `hadolint.cli` | `true` | Enable Dockerfile linting |
| `hadolint.failure_threshold` | `"error"` | Minimum severity to fail |
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
| `phpmd.cli` | `false` | Enable PHP Mess Detector (opt-in: checks duplicate haspadar/phpstan-rules) |
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
| `phpmetrics.cli` | `false` | Enable PHP Metrics (opt-in: checks largely duplicate haspadar/phpstan-rules; enable for HTML/Halstead reports) |
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
| `phpstan.memory` | `"1G"` | Memory limit |
| `phpstan.neon_includes` | `["../../vendor/phpstan/phpstan-strict-rules/rules.neon", "../../vendor/phpstan/phpstan-phpunit/extension.neon", "../../vendor/phpstan/phpstan-phpunit/rules.neon", "../../vendor/haspadar/phpstan-rules/rules.neon"]` | Neon includes |
| `phpstan.parameters.level` | `9` | Analysis level (0-9) |
| `phpstan.parameters.errorFormat` | `table` | Error formatter |
| `phpstan.parameters.reportUnmatchedIgnoredErrors` | `true` | Fail when an ignore pattern matches nothing |
| `phpstan.parameters.checkUninitializedProperties` | `true` | Report properties that may be read before being assigned |
| `phpstan.parameters.checkClassCaseSensitivity` | `true` | Enforce case-sensitive class references |
| `phpstan.parameters.checkDynamicProperties` | `true` | Report writes to undeclared dynamic properties |
| `phpstan.parameters.exceptions.checkedExceptionClasses` | `['\Throwable']` | Checked exception classes for the strict-rules `throws` analysis |
| `phpstan.parameters.haspadar.afferentCoupling.ignoreInterfaces` | `true` | Skip interfaces when counting afferent coupling (haspadar rule) |
| `phpstan.parameters.haspadar.afferentCoupling.excludedClasses` | `[]` | FQCNs excluded from the haspadar afferent coupling rule |
| `phpstan.parameters.haspadar.prohibitStaticMethods.allowNamedConstructors` | `true` | Allow named constructors (e.g. `public static function fromX(...): self { return new self(...); }` or `public static function fromX(...): static { return new static(...); }`) as the only sanctioned static methods |

`phpstan.parameters` is a nested tree merged into the rendered `phpstan.neon` under `parameters:`. Use `override:` / `append:` / `remove:` on any leaf to customise the analysis without rewriting the file.

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
| `shellcheck.severity` | `"warning"` | Minimum severity |
| `shellcheck.shell` | `"bash"` | Shell dialect |
| `shellcheck.source_path` | `"."` | Source path for includes |

### sonar

| Key | Default | Description |
|-----|---------|-------------|
| `sonar.cloud` | `true` | Use SonarCloud automatic analysis (skip local scanner and SONAR_TOKEN). Must be a YAML boolean (`true`/`false`); aliases like `yes`/`on`/`1` are parsed as strings/ints and rejected. |
| `sonar.cli` | `false` | Enable local sonar-scanner |
| `sonar.organization` | `""` | SonarCloud organization |
| `sonar.projectKey` | `""` | SonarCloud project key |
| `sonar.tests` | `["tests"]` | Test directories |
| `sonar.exclusions` | `[]` | Excluded paths |
| `sonar.php.coverage.reportPaths` | `[".sheriff/codecov/coverage.xml"]` | Coverage report path |

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
