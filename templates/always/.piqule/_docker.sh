#!/usr/bin/env bash
set -euo pipefail

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is not installed or not in PATH" >&2
  exit 1
fi

PROJECT_ROOT="$(pwd)"
DEFAULT_IMAGE="<< config(docker.image) >>"
LEGACY_IMAGE="ghcr.io/haspadar/piqule-infra@sha256:f1a41bcaab12ca89e65ecbf1cb42eddd400b0dac89f7b4d7a190ade6be089799"
IMAGE="${SHERIFF_INFRA_IMAGE:-${PIQULE_INFRA_IMAGE:-$DEFAULT_IMAGE}}"

if [ -z "${SHERIFF_INFRA_IMAGE:-}" ] \
  && [ -z "${PIQULE_INFRA_IMAGE:-}" ] \
  && ! docker image inspect "$IMAGE" >/dev/null 2>&1 \
  && ! docker pull "$IMAGE" >/dev/null 2>&1; then
  IMAGE="$LEGACY_IMAGE"
fi

docker run --rm \
  --user "$(id -u):$(id -g)" \
  -v "$PROJECT_ROOT:/project" \
  -w /project \
  "$IMAGE" \
  "$@"
