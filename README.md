# notes.philipnewborough.co.uk

A private, encrypted personal notes application built with CodeIgniter 4.

## Overview

Notes are stored as AES-encrypted blobs in a MySQL database. The encryption key (`noteskey`) is supplied by the client on each request — the server never holds it at rest, so stored note content remains opaque without the key. Note titles and bodies are both encrypted.

## Features

- **Create, edit, and delete notes** with a Markdown editor and live preview
- **Revision history** — a new revision is automatically saved whenever the body of a note changes
- **Pin notes** for quick access
- **Search** notes by title and body
- **Paginated note listing** (20 notes per page)
- **Admin dashboard** showing note and revision counts, recent activity, and encryption key management

## Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4 (PHP 8) |
| Database | MySQL |
| Frontend | Bootstrap 5, Vanilla JS (Airbnb style), Marked.js |
| Code style | PSR-12 (PHP), Airbnb (JS), BEM (CSS) |

## Authentication

Authentication is delegated to an external auth microservice. On each request the `AuthFilter` validates the user's `user_uuid` and `token` cookies against that service. Admin-only routes (`/admin/*`) additionally require `is_admin = true`. API routes are protected by a master API key or a per-user API key.

## Project Structure

```
app/
  Config/       # CodeIgniter configuration, including route and filter definitions
  Controllers/  # Web, Admin, API, CLI, and Debug controllers
  Database/     # Migrations and seeds
  Filters/      # Auth, Admin, API, and optional-auth filters
  Libraries/    # Markdown, Notification, and Sendmail service wrappers
  Models/       # NoteModel, NoteRevisionModel
  Views/        # Blade-style PHP views with Bootstrap 5 layouts
public/         # Web root — assets (CSS, JS, images)
tests/          # PHPUnit unit, database, and session tests
```

## Key Routes

| Method | Route | Description |
|---|---|---|
| GET | `/` | Notes list |
| GET | `/note/new` | New note editor |
| GET | `/note/:id/edit` | Edit a note |
| POST | `/note` | Create a note |
| PATCH | `/note/:id` | Update a note |
| DELETE | `/note/:id` | Delete a note |
| GET | `/note/:id/revisions` | List revisions |
| GET | `/note/:id/revision/:rid` | View a revision |
| POST | `/note/preview` | Render Markdown to HTML |
| GET | `/admin` | Admin dashboard |
| GET | `/logout` | End session |

## Running Tests

```bash
composer test
```

Coverage reports are written to `build/logs/`.

