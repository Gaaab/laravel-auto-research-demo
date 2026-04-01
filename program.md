# Laravel Autoresearch

## Setup
1. git checkout -b autoresearch/<tag>
2. Read ProductController.php
   — the ONLY file you modify
3. Run ./benchmark.sh for baseline
4. Initialize results.tsv

## Experiment Loop
1. Edit ProductController.php
2. git commit
3. ./benchmark.sh > run.log 2>&1
4. grep "avg_ms:" run.log
5. Improved → keep, worse → git reset
6. Log to results.tsv, GOTO 1

## Research Directions
- Eager loading with ->with()
- Column selection with ->select()
- Cache::remember() with smart keys
- Cursor pagination
- ->withCount() / ->withAvg()
- API Resources for serialization

## NEVER STOP
The human might be asleep.
You are autonomous. Run until interrupted.
