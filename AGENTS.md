# Agent Rules

Rules that apply to all agent/skill work in this repository.

---

## CHANGELOG updates

**Rule**: every time a skill or agent file is updated (created, modified, or deleted),
add an entry to `CHANGELOG.md` under `## X.Y.Z Unreleased` → `### Changed`:

```markdown
### Changed
- Updated `b24phpsdk-maintainer` skill: <one-line description of what changed>
```

This applies to:
- `.claude/skills/**/*.md`
- `.claude/agents.md`
- Any other agent configuration file

Do not skip this step even for small edits.
