#!/usr/bin/env bash
# Phase 9 idM endpoint validation (no secrets)
set -euo pipefail

BASE="${IDM_BASE_URL:-https://idm.oerse-kippies.nl}"
API_KEY="${IDM_API_KEY:-replace-with-production-api-key}"
COMML="${COMML_HEALTH_URL:-https://comml.oerse-kippies.nl/api/health.php}"
PROBE_USER="${IDM_PROBE_USER:-bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb}"

pass=0
fail=0

check() {
  local name="$1"
  local code="$2"
  local expected="$3"
  if [[ "${code}" == "${expected}" ]]; then
    echo "${name}: PASS (${code})"
    pass=$((pass + 1))
  else
    echo "${name}: FAIL (${code}, expected ${expected})"
    fail=$((fail + 1))
  fi
}

health_code=$(curl -k -sS -o /tmp/idm_health.json -w "%{http_code}" --max-time 15 "${BASE}/health" || echo "000")
check "GET /health" "${health_code}" "200"

users_code=$(curl -k -sS -o /tmp/idm_users.json -w "%{http_code}" --max-time 15 \
  -H "x-api-key: ${API_KEY}" \
  "${BASE}/v1/identity/users" || echo "000")
check "GET /v1/identity/users (users.list)" "${users_code}" "200"

actor_code=$(curl -k -sS -o /tmp/idm_actor.json -w "%{http_code}" --max-time 15 \
  -X POST \
  -H "Content-Type: application/json" \
  -H "x-source-module: communicationLayer" \
  -d "{\"credentialType\":\"USER\",\"subjectHint\":\"${PROBE_USER}\"}" \
  "${BASE}/v1/identity/actor-context" || echo "000")
check "POST /v1/identity/actor-context (actorContext)" "${actor_code}" "200"

comml_code=$(curl -k -sS -o /tmp/idm_comml.json -w "%{http_code}" --max-time 15 "${COMML}" || echo "000")
check "commL /api/health.php" "${comml_code}" "200"

echo "SUMMARY pass=${pass} fail=${fail}"
exit $([[ "${fail}" -eq 0 ]] && echo 0 || echo 1)
