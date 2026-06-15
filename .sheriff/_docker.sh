#!/usr/bin/env bash
set -euo pipefail

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is not installed or not in PATH" >&2
  exit 1
fi

PROJECT_ROOT="$(pwd)"
DEFAULT_IMAGE="ghcr.io/haspadar/sheriff-infra@sha256:7b0af8c57265504fd4b14d0ad565b1432c9361a4678090092c89bb6f8d4bb160"
IMAGE="${SHERIFF_INFRA_IMAGE:-$DEFAULT_IMAGE}"

docker run --rm \
  --user "$(id -u):$(id -g)" \
  -v "$PROJECT_ROOT:/project" \
  -w /project \
  "$IMAGE" \
  "$@"
