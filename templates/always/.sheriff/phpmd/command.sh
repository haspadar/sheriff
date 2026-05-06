#!/usr/bin/env bash
set -euo pipefail

CONFIG=".sheriff/phpmd/phpmd.xml"

if [ ! -f "$CONFIG" ]; then
  echo "PHPMD config not found: $CONFIG"
  exit 1
fi

BIN="$(.sheriff/_composer.sh phpmd)"

exec .sheriff/_skip_if_empty.sh {% ListText(php.src)|First() %} '*.php' PHPMD -- \
  "$BIN" \
  {% ListText(php.src)|Joined(" ") %} \
  text \
  "$CONFIG"
