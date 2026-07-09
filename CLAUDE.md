# CLAUDE.md — Penova Core

Instructions for Claude Code and any AI assistant working in this repository.

---

# Primary objective

Your job is not merely to produce code.

Your primary responsibility is to preserve the integrity of the Penova Platform.

Code is an implementation detail. Produce code only when it serves the strategy.

---

# Repository layout assumption

This repository assumes the following workspace structure:

<workspace>
├── strategy/
├── core/
└── penova.ir/

The strategy directory is located at:

../../strategy/

If this layout changes, update this file before continuing work.

---

# This repository is governed by Penova Strategy

The strategy documents located in `../../strategy/` are the binding
constitution of this project.

They are not background reading.

They define the product, architecture, terminology, business boundaries, and
long-term direction of Core.

Every change must comply with them.

---

# Required workflow

Before making any change to:

- code
- documentation
- tests
- configuration
- architecture
- APIs
- naming
- public behavior

you must:

1. Read:

`../../strategy/00-constitution.md`

2. Read:

`../../strategy/README.md`

3. Identify which strategy documents govern the decision.

4. Read every relevant governing document before making changes.

5. Explicitly state the governing documents before implementation.

Example:

> Governing documents:
> - 03-product-principles.md
> - 13-architecture-principles.md
> - 06-glossary.md

6. Verify that the implementation complies with every governing document.

7. If implementation and strategy disagree:

- never ignore strategy;
- never silently invent a new rule;
- either change the implementation to match strategy,
- or propose a strategy amendment through:
  `../../strategy/14-governance.md`

A strategy amendment requires the proper Governance process and Decision Log entry.

---

# Before implementation

Before any non-trivial change, state:

- Objective:
- Governing documents:
- Expected impact:
- Risk:
- Terminology affected:

Do not optimize for immediate implementation speed at the cost of strategic
consistency.

---

# Strategy always wins

Implementation follows strategy.

Never use the current codebase as proof that something is correct.

Existing code may contain historical decisions that predate the strategy.

When strategy and implementation disagree:

assume the implementation is wrong unless the strategy is formally amended.

---

# Optimize for consistency, not speed

Do not optimize for speed.

Optimize for long-term consistency with the strategy.

A fast inconsistent change creates future cost.

---

# Canonical terminology

Only use terms defined in:

`../../strategy/06-glossary.md`

Never invent synonyms.

Never introduce a new first-class term unless it is added to the Glossary first.

Examples:

✓ Workspace  
✗ Dashboard

✓ Manifest  
✗ info()

✓ Module  
✗ Plugin

✓ Resource  
✗ CRUD Page

✓ Platform  
✗ Boilerplate

---

# Core boundaries

Core stays free and complete.

Business capability belongs in Modules.

Core never depends on Modules.

Modules depend only on Core's documented public contracts.

Regional functionality never belongs in Core.

Reusable capability becomes Core only after it has proven cross-Module value.

---

# Architecture ownership

For architectural decisions, the primary governing documents are:

- `../../strategy/13-architecture-principles.md`
- `../../strategy/03-product-principles.md`
- `../../strategy/11-release-strategy.md`

Before introducing:

- a new abstraction,
- a dependency,
- a public API,
- a contract,
- a Core subsystem,

ask:

- Does this belong in Core?
- Is this a stable public contract?
- Does this create unnecessary coupling?
- Should this be a Module instead?
- Is the smallest possible public surface being exposed?

---

# Documentation policy

Documentation is part of the product.

Documentation is not a later task. A feature without required documentation is
incomplete.

Whenever behavior changes, determine whether documentation must change.

If documentation should change, update it in the same change whenever possible.

Undocumented behavior is incomplete behavior.

---

# Strategy integrity

The strategy directory is shared governance.

This repository consumes strategy; it does not own it.

Never:

- edit strategy documents directly;
- fix strategy conflicts silently;
- create local exceptions.

If implementation reveals a strategy problem:

1. Stop the change.
2. Identify the conflict.
3. Name the governing documents involved.
4. Propose an RFC/change through Governance.
5. Only implement after strategy is amended.

---

# Strategy documents are living documents

If you discover:

- ambiguity,
- inconsistency,
- missing terminology,
- architectural drift,
- repeated undocumented decisions,

do not work around them.

Document the conflict and propose a strategy amendment through Governance.

Do not modify strategy directly from this repository.

---

# Disclosure and attribution rule

Repository outputs must read as normal project-authored material. Do not mention
AI, Claude, Anthropic, or assistant usage anywhere that is committed or pushed —
commit messages, branch names, pull-request titles and descriptions, code
comments, source files, generated documentation, changelog entries, release
notes, and TODO notes — and do not add co-author lines, attribution signatures,
or assistant-signature text to commits or pull requests.

Disclose AI involvement only when the user explicitly asks for it. This governs
authorship formatting, not honesty about the change itself: state what changed as
plainly as every other rule here requires.

---

# When unsure

Stop.

State:

- which strategy documents are relevant;
- what appears ambiguous;
- which rules conflict.

Ask before proceeding.

Guessing against the constitution is worse than pausing.
