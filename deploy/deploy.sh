#!/usr/bin/env bash
# Usage: deploy.sh <full git SHA>   (runs on the server)

# Stop on any error, on an unset variable, and when any part of a pipe fails
set -euo pipefail

# $1 = the SHA to deploy; accept only 40 hex characters, so "latest" or a typo is refused
NEW_TAG="${1:-}"
[[ "$NEW_TAG" =~ ^[0-9a-f]{40}$ ]] || { echo "Need a full git SHA"; exit 2; }

# compose.yaml and .env live here (APP_DIR can be overridden for testing)
cd "${APP_DIR:-/opt/labapp}"

# Remember the current version: this is the rollback target
OLD_TAG="$(sed -n 's/^APP_TAG=//p' .env)"

# Write a version into .env, so a reboot or a manual "up" uses the same one
set_tag() { sed -i "s/^APP_TAG=.*/APP_TAG=$1/" .env; }

# Start version $1 and prove it works; returns non-zero if any check fails
start_and_check() {
  # --no-build: the server only runs images, it never builds them
  # --wait: block until healthchecks pass and migrate exits 0 (max 180 s)
  docker compose up -d --no-build --wait --wait-timeout 180 || return 1
  # Prove the running app container really uses this SHA
  local image
  image="$(docker inspect --format '{{.Config.Image}}' "$(docker compose ps -q app)")" || return 1
  [[ "$image" == *":$1" ]] || return 1
  # Full path: Nginx -> PHP-FPM -> Laravel. -f = fail on HTTP 4xx/5xx
  curl -fsS --max-time 10 --retry 5 --retry-delay 2 --retry-connrefused -o /dev/null http://127.0.0.1:8080/up
}

# 1. Download first. The shell's APP_TAG beats .env. If this fails, the site is untouched
echo "Pulling $NEW_TAG"
APP_TAG="$NEW_TAG" docker compose --progress quiet pull

# 2. Switch version, start it, check it
set_tag "$NEW_TAG"
if start_and_check "$NEW_TAG"; then
  echo "Deployed $NEW_TAG"
  # Small disk: delete labapp images except the new one and the rollback one
  # xargs -r = do nothing if the list is empty; "|| true" = cleanup never fails a deploy
  docker images --format '{{.Repository}}:{{.Tag}}' \
    | grep '^ghcr.io/chrollocollab/labapp-' \
    | grep -v -e ":${NEW_TAG}\$" -e ":${OLD_TAG}\$" \
    | xargs -r docker rmi || true
  exit 0
fi

# 3. Failed. Print container states only: app logs could leak data into the CI log
echo "FAILED: $NEW_TAG"
docker compose ps -a || true

# Put the old version back in .env, so a bad SHA never becomes the next rollback target
set_tag "$OLD_TAG"

# Roll back only to a real, different SHA (the first deploy has none)
if [[ "$OLD_TAG" =~ ^[0-9a-f]{40}$ && "$OLD_TAG" != "$NEW_TAG" ]]; then
  if start_and_check "$OLD_TAG"; then
    echo "Rolled back to $OLD_TAG"
  else
    echo "ROLLBACK FAILED: fix the server by hand"
  fi
else
  echo "No previous SHA to roll back to"
fi

# Always exit 1: the deploy failed, so the CI job must be red
exit 1
