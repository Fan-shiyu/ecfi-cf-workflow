# ECFI CF Workflow

A WordPress plugin for managing a collaborative data update workflow for the [ECFI Atlas](https://www.ecfi.eu/atlas/) community foundation directory.

---

## Overview

Community foundation data in the ECFI Atlas requires periodic review and correction by multiple stakeholders. This plugin provides a structured workflow so that collaborators can propose, review, and approve data updates directly within WordPress — without needing direct database or admin access.

## Features

Planned features:

- Collaborator submission form for proposing data updates to existing foundation records
- Admin review queue with diff view (current vs. proposed values)
- Role-based access: submitter → reviewer → approver
- Email notifications at each workflow stage
- Audit log of all accepted and rejected changes
- Integration with Advanced Custom Fields (ACF) for structured field definitions
- Export of approved changes for upstream sync

## Requirements

- WordPress 6.0+
- PHP 7.4+
- [Advanced Custom Fields (ACF)](https://www.advancedcustomfields.com/) — free or Pro
- ACF (free version sufficient; taxonomy field support is built-in from ACF 6.0+)

## Installation

> Installation instructions will be added once the plugin reaches a stable release.

1. Download the latest release `.zip` from GitHub Releases.
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the `.zip`, activate, and configure under **CF Workflow** in the admin menu.

## Development Setup

> Development setup instructions will be added as the project matures.

**Prerequisites:** Local WordPress environment (e.g. [LocalWP](https://localwp.com/)) with ACF installed.

```bash
# Clone into your wp-content/plugins directory
git clone https://github.com/Fan-shiyu/ecfi-cf-workflow.git
```

## Roadmap

- **Phase 1 — Foundation**
  - Plugin scaffold and custom database tables
  - Collaborator management (tokens, organisation assignments)
  - Tokenised landing page with name/position capture
  - Edit form for assigned foundation(s)
  - Pending changes table and basic admin review dashboard
  - Basic audit log

- **Phase 2 — Review UX polish**
  - Side-by-side "old → new" diff display
  - Per-field accept/reject in review dashboard
  - Highlighted changed fields
  - Add new foundation flow

- **Phase 3 — Operational features**
  - Session-based email notifications to admin (requires SMTP)
  - Multi-organisation per collaborator
  - Collaborator-facing submission history
  - Admin screen for managing collaborators and tokens

- **Phase 4 — Later**
  - Table-view editing for bulk updates
  - Full version history browsing
  - Public submission form integration (if needed)

## License

MIT © 2026 Fan Shiyu. See [LICENSE](LICENSE) for details.
