#!/usr/bin/env bash
set -euo pipefail

CONFIG=".sheriff/hadolint/.hadolint.yml"

if [ ! -f "$CONFIG" ]; then
  echo "No hadolint config found, skipping hadolint"
  exit 0
fi

# ============================================================
# File selection (portable, correct precedence, NUL-safe)
# ============================================================

FILES=()
while IFS= read -r -d '' file; do
  FILES+=("$file")
done < <(
  find . \
    -type f \
    \( \
<< config(hadolint.patterns)
   |format_each('      -name "%s" -o \')
   |join("\n")
>>
      -false \
    \) \
<< config(hadolint.ignore)
   |format_each('    ! -path "./%s/*" \')
   |join("\n")
>>
    -print0
)

if [ ${#FILES[@]} -eq 0 ]; then
  echo "No Dockerfiles found"
  exit 0
fi

.sheriff/_docker.sh hadolint \
  --config "$CONFIG" \
  "${FILES[@]}"
