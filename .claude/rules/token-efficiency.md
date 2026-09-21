# Token Efficiency

Optimize total token usage without reducing implementation quality.

## Core Principles

- Prioritize correctness, maintainability, and code quality over token savings.
- Gather enough context before modifying code to avoid trial-and-error fixes.
- Prefer targeted investigation over broad repository exploration.
- Do not sacrifice debugging, testing, verification, or architectural understanding to save tokens.

## File Exploration

- Search before reading large files.
- Use targeted Grep/Glob/search operations to locate relevant code before opening files.
- Read only relevant sections of large files when possible.
- Read complete files when architectural or execution-flow context is necessary.
- Do not re-read files unless:
  - the file changed;
  - relevant context was lost;
  - verification requires it.
- Avoid exploring unrelated directories or files.
- Reuse information already available in the current context.

## Avoid Unnecessary Context

Exclude generated, third-party, runtime, and dependency directories from broad exploration unless the task specifically requires them:

- vendor/
- node_modules/
- files/
- marketplace/
- cache/
- logs/

Do not inspect large generated files, lock files, compiled assets, or dependency source code unless relevant to the problem.

## Communication

- Keep progress updates concise.
- Do not repeat explanations already given.
- Do not summarize every file that was inspected.
- Report findings, decisions, modifications, and important verification results.
- Avoid long explanations when a short technical explanation is sufficient.

## Subagents

- Do not create subagents for simple or localized tasks.
- Use subagents when they meaningfully reduce context pollution, isolate investigations, or enable useful parallel work.
- Avoid multiple agents investigating the same code unnecessarily.

## Context Management

- Preserve important architectural decisions and discovered relationships.
- Compact context after completing a major phase, not during active investigation.
- Before compacting, preserve information that will be required in the next phase.

# GLPI Project Rules

## Primary Scope

Treat these directories as the primary application source code:

- glpi_data/plugins/
- glpi_data/src/

When working on an Ativa custom plugin:

1. Search the relevant directory under `glpi_data/plugins/` first.
2. Inspect `glpi_data/src/` only when GLPI core behavior, hooks, inheritance, APIs, permissions, routing, database models, or framework integration requires it.
3. Expand the investigation only when evidence indicates that another part of GLPI is involved.

## Directories to Avoid by Default

Do not inspect:

- glpi_data/vendor/
- glpi_data/files/
- glpi_data/marketplace/

Unless specifically required by the task.

Exceptions include:

- Composer/dependency problems → vendor/
- uploads/cache/sessions/logs/storage → files/
- marketplace plugin behavior → marketplace/

## Graphify

Use the Graphify dependency graph when relationships between files, classes, plugins, hooks, or GLPI core components are unclear.

Prefer:

Graphify → targeted search → relevant files → implementation

instead of:

repository-wide scan → many file reads → implementation

Do not use Graphify for simple changes when the relevant file or implementation location is already known.

Use Graphify especially when:

- tracing cross-file dependencies;
- identifying callers or implementations;
- investigating relationships between plugins and GLPI core;
- understanding unfamiliar code paths;
- determining which files are likely relevant before reading them.

After Graphify identifies relevant components, inspect only the files necessary to understand and implement the change.

## Git Awareness

- Use git status and git diff to understand existing changes before broadly investigating the repository.
- Do not inspect unrelated modified files.
- After implementation, use targeted git diff to review the changes made.
- Do not generate large repository-wide diffs unless necessary.