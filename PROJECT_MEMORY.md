# Project Memory

- Browser-based retro game music quiz: players identify games from audio clues.
- Question data lives in numbered files under `data/`; sets are grouped by platform/theme.
- Question modes:
  - `expert`: uses `scores` tables; player types a freeform answer matched against regex rules, and one response can satisfy several more precise subquestions.
  - `easy`: uses multiple choice; player answers several choices in sequence.
- AJAX question payloads are converted to JSON via `get_json()` and stripped of answers or hints before being sent to the client.