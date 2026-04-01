#!/bin/bash
# =============================================================================
# benchmark.sh — IMMUTABLE EVALUATOR (DO NOT MODIFY)
# =============================================================================
# This is the equivalent of Karpathy's prepare.py in autoresearch.
# It runs correctness tests first, then benchmarks the API endpoint.
# The agent MUST NOT modify this file.
# =============================================================================

set -e

# --- Configuration ---
BASE_URL="http://localhost:8000"
ENDPOINT="/api/products?category=electronics&sort=price&per_page=50"
NUM_REQUESTS=50

echo "[autoresearch] Running correctness tests..."

# Step 1: Correctness check — tests MUST pass or experiment is invalid
php artisan test --filter=ProductApiTest --no-ansi 2>&1
if [ $? -ne 0 ]; then
    echo "FAIL: Tests did not pass. Experiment is invalid."
    exit 1
fi
echo "[autoresearch] Tests passed ✓"

# Step 2: Warm up (1 request, discarded)
curl -s -o /dev/null "${BASE_URL}${ENDPOINT}"

# Step 3: Benchmark — measure average response time over N requests
echo "[autoresearch] Benchmarking ${NUM_REQUESTS} requests to ${ENDPOINT}..."

TOTAL=0
for i in $(seq 1 $NUM_REQUESTS); do
    # time_total is in seconds with microsecond precision
    TIME=$(curl -s -o /dev/null -w "%{time_total}" "${BASE_URL}${ENDPOINT}")
    TOTAL=$(echo "$TOTAL + $TIME" | bc)
done

# Convert to milliseconds
AVG=$(echo "scale=4; $TOTAL / $NUM_REQUESTS * 1000" | bc)

echo "avg_ms: ${AVG}"
