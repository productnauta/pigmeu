context_paths:
  - key: aiomatic-ai
    path: analysis/aiomatic-ai
    description: AIomatic AI analysis and integration layer
    writable: true

  - key: rank-math
    path: analysis/rankmath
    description: Rank Math analysis, SEO logic, and integrations
    writable: true

  - key: content-copilot-code
    path: content-copilot/core
    description: Content Copilot core application code
    writable: true

  - key: content-copilot-docs
    path: content-copilot/docs
    description: Content Copilot documentation
    writable: false

instructions: |
  Available context paths:

  - aiomatic-ai → analysis/aiomatic-ai (writable)
  - rank-math → analysis/rankmath (writable)
  - content-copilot code → content-copilot/core (writable)
  - content-copilot docs → content-copilot/docs (read-only)

  Rules:
  - All file paths must be relative to the workspace root.
  - Only writable paths may be modified.
  - Documentation paths are read-only unless explicitly authorized.
  - If a request spans multiple context paths, explain the dependency before proceeding.
  - If the target path is unclear, ask for clarification.
