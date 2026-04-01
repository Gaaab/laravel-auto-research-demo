#!/bin/bash
# benchmark.sh — DO NOT MODIFY
# Runs the test suite first, then benchmarks avg response time

# 1. Correctness check — must pass or experiment is invalid
php artisan test --filter=ProductApiTest
if [ $? -ne 0 ]; then
  echo "FAIL: Tests did not pass. Aborting benchmark."
  exit 1
fi

# 2. Clear caches to start clean
php artisan cache:clear && php artisan route:clear

# 3. Benchmark: 100 requests, extract avg response time
TOTAL=0
for i in $(seq 1 100); do
  TIME=$(curl -s -o /dev/null -w "%{time_total}" \
    http://localhost:8000/api/products?category=electronics\&sort=price)
  TOTAL=$(echo "$TOTAL + $TIME" | bc)
done

AVG=$(echo "scale=4; $TOTAL / 100 * 1000" | bc)
echo "avg_ms: $AVG"
