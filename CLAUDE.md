# CLAUDE.md — Penova Core

Instructions for Claude Code and any AI assistant working in this repository.

---

# Primary objective

Your job is not to produce code.
Your job is to preserve the integrity of the Penova Platform.
Code is only one way to achieve that objective.

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

Before making any change to code, documentation, tests, configuration,
architecture, APIs, naming, or public behavior, you must:

1. Read `../../strategy/00-constitution.md`.

2. Read `../../strategy/README.md` and identify which strategy document governs
   the decision.

3. Read every relevant strategy document before making changes.

4. Explicitly state which document governs your decision before implementing
   anything.

Example:

> Governing document:
> 03-product-principles.md
> 13-architecture-principles.md
> 06-glossary.md

5. Verify that the implementation complies with every governing document.

6. If implementation and strategy disagree:

   - never ignore the strategy;
   - never silently invent a new rule;
   - either change the implementation to match the strategy,
     or propose a strategy amendment through
     `../../strategy/14-governance.md`
     together with a new Decision Log entry.

---

# Strategy always wins

Implementation follows strategy.

Never use the current codebase as proof that something is correct.

Existing code may contain historical decisions that predate the strategy.

When strategy and implementation disagree,
assume the implementation is wrong unless the strategy is formally amended.

---

# Optimize for consistency, not speed

Do not optimize for speed.

Optimize for long-term consistency with the strategy.

---

# Canonical terminology

Only use terms defined in:

`../../strategy/06-glossary.md`

Never invent synonyms.

Never introduce a new first-class term unless it is added to the Glossary
first.

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

# Documentation policy

Documentation is part of the product.

Whenever behavior changes, determine whether documentation must change.

If documentation should change,
update it in the same change whenever possible.

---

# Strategy documents are living documents

If you discover:

- ambiguity,
- inconsistency,
- missing terminology,
- architectural drift,
- repeated undocumented decisions,

do not work around them.

Instead, propose improvements to the strategy itself before implementing new
patterns.

Improving the strategy is part of improving the platform.

---

# When unsure

Stop.

State:

- which strategy documents are relevant,
- what appears ambiguous,
- which rule conflicts,

and ask before proceeding.

Guessing is not acceptable.
