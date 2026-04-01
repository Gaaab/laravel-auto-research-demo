# Laravel Autoresearch — Product API Optimization

## Goal
Minimize average response time (ms) for GET /api/products.
Metric: avg_ms from benchmark.sh. Lower is better.

## Setup
1. git checkout -b autoresearch/products
2. Read ProductController.php — the ONLY file you modify
3. Run ./benchmark.sh to get baseline avg_ms
4. Initialize results.tsv with header row

## Experiment Loop
1. Form a hypothesis (e.g. "eager loading will reduce N+1 queries")
2. Edit ProductController.php
3. Run ./benchmark.sh > run.log 2>&1
4. grep "avg_ms:" run.log
5. If avg_ms improved → git commit, else → git reset --hard
6. Log result to results.tsv, GOTO 1

## Research Directions (try these)
- Eager loading relationships
- Query column selection (avoid SELECT *)
- Redis cache with smart invalidation
- Cursor pagination vs offset pagination
- Database indexing via migration
- Response transformation with API Resources
- Query builder vs raw SQL for complex filters
