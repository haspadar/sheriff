#!/usr/bin/env bash
set -euo pipefail

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is not installed or not in PATH" >&2
  exit 1
fi

PROJECT_ROOT="$(pwd)"

IMAGE="${SHERIFF_INFRA_IMAGE:-${PIQULE_INFRA_IMAGE:-ghcr.io/haspadar/sheriff-infra@sha256:f1a41bcaab12ca89e65ecbf1cb42eddd400b0dac89f7b4d7a190ade6be089799}}"

docker run --rm \
  --user "$(id -u):$(id -g)" \
  -v "$PROJECT_ROOT:/project" \
  -w /project \
  "$IMAGE" \
  "$@"
