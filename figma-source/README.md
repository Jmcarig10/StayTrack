# StayTrack Figma source

This folder is a **Figma plugin source** that generates a complete, **fully editable** Figma recreation of the StayTrack website — from the landing page and login screens, through the admin workspace, to the tenant portal. Every screen uses real text from the live app and real data from the database.

## What it generates

Running the plugin creates 5 pages in the current Figma file:

| Page | Contents |
| --- | --- |
| `01 · Landing & Auth` | Landing, Login (+ error state), Register, Forgot password (+ link-sent state), Reset password, Verify email (+ resent state), Confirm password |
| `02 · Admin` | Dashboard (+ notifications panel, delete modal states), Apartments (list / create / detail / edit), Tenants (list / quick-create / detail / create), Leases (list / create / detail), Bills (list / create / detail), Payments (list / create / detail), Maintenance (list / create / detail), Profile |
| `03 · Tenant` | Tenant dashboard (+ no-active-lease state), Maintenance (list / create), Profile |
| `04 · Components` | Single reference frame with the full design system: color palette, Figtree typography, buttons, badges, tags, chips, form fields, avatars, cards, empty states, modal, toast, notification panel |
| `05 · Prototype Flows` | Route-flow diagrams connecting the screens (Landing/auth, Admin, Tenant) |

All nodes are plain Figma objects (frames, rectangles, ellipses, text) with real layer names — edit any text, recolor the slate palette, restructure cards, and restyle freely. No Figma API token or cloud file is needed.

## Generate the design in Figma

1. Open the Figma desktop app (or figma.com) and create/open a design file.
2. Go to **Plugins > Development > Import plugin from manifest**.
3. Select `figma-source/manifest.json`.
4. Run **StayTrack UI Source** from **Plugins > Development**.

The plugin loads the Figtree family (400/500/600/700/800, with automatic fallback) and places every frame on its own page.

## Tips

- See **[CHECKLIST.md](CHECKLIST.md)** for the full route-by-route mapping (65 routes) with verification status.
- Screens are laid out on a 3-column grid per page — drag/re-flow them however you like.
- To restyle the whole system, select a text/shape and use its fill/flip properties; there are no auto-layout constraints, so everything stays editable.
- Data matches the live site at `http://127.0.0.1:8000` (apartments, tenants, leases, bills, payments). Maintenance has no live records — screens show the empty state plus a sample request for the detail view.