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
- ACF Extended (for taxonomy field support)

## Installation

> Installation instructions will be added once the plugin reaches a stable release.

1. Download the latest release `.zip` from GitHub Releases.
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the `.zip`, activate, and configure under **CF Workflow** in the admin menu.

## Development Setup

> Development setup instructions will be added as the project matures.

**Prerequisites:** Local WordPress environment (e.g. [LocalWP](https://localwp.com/)), Composer, Node.js.

```bash
# Clone into your wp-content/plugins directory
git clone https://github.com/felicityfan/ecfi-cf-workflow.git

# Install PHP dependencies (when composer.json is added)
composer install

# Install JS dependencies (when package.json is added)
npm install
```

## Roadmap

- **Phase 1 — Foundation**
  - Plugin scaffold and constants
  - Custom DB table for change proposals
  - Basic submission form (public-facing)

- **Phase 2 — Review Workflow**
  - Admin review queue
  - Diff view for proposed vs. current field values
  - Email notifications (submission, approval, rejection)

- **Phase 3 — Polish & Integration**
  - Audit log UI
  - Role & capability management
  - Export to CSV / upstream sync hook

## License

MIT © 2026 Fan Shiyu. See [LICENSE](LICENSE) for details.
