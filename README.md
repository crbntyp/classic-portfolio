# Carbontype Portfolio

> A modern, interactive portfolio website built with PHP, MySQL, and vanilla JavaScript, featuring dynamic project management, user authentication, and a unique particle-based UI.

## Screenshots

<div align="center">

<table>
  <tr>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.39.50.png" alt="Home page with particle background" />
      <p align="center"><em>Home page with particle background</em></p>
    </td>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.39.55.png" alt="Blob navigation menu" />
      <p align="center"><em>Dynamic blob navigation</em></p>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.40.09.png" alt="Portfolio carousel" />
      <p align="center"><em>Interactive portfolio carousel</em></p>
    </td>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.40.22.png" alt="Project showcase" />
      <p align="center"><em>Project showcase view</em></p>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.40.29.png" alt="Project details modal" />
      <p align="center"><em>Project details modal</em></p>
    </td>
    <td width="50%">
      <img src="screenshots/Screenshot 2025-11-09 at 09.40.35.png" alt="Login modal" />
      <p align="center"><em>User authentication</em></p>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <img src="screenshots/Screenshot 2025-11-09 at 09.41.00.png" alt="Admin dashboard" />
      <p align="center"><em>Admin dashboard for project management</em></p>
    </td>
  </tr>
</table>

</div>

**📋 [View Changelog](./CHANGELOG.md)** - See recent refactoring improvements and security fixes

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Development](#development)
- [Architecture](#architecture)
- [Database](#database)
- [Deployment](#deployment)
- [Security](#security)

---

## Features

### Public Features
- **Dynamic Portfolio Carousel** - Interactive fullscreen carousel showcasing projects
- **Blob Navigation** - Dynamic menu entries with custom project links
- **Particle Canvas Background** - Animated particle effects on all pages
- **Responsive Design** - Mobile-first approach with adaptive layouts
- **User Authentication** - Login system with password reset functionality

### Admin Features
- **Admin Dashboard** - Central hub for portfolio management
- **Project Management** - Add, edit, and organize portfolio projects
- **Statistics Overview** - Real-time counts of projects and entries
- **Blob Entry Management** - Control dynamic navigation items
- **User Management** - Account and permission controls

### Technical Features
- **Modular PHP Architecture** - DRY principles with shared includes
- **SCSS Build System** - Modular, maintainable stylesheets
- **JavaScript Bundling** - Automatic concatenation and optimization
- **Docker Development** - Containerized environment for consistency
- **Email Service** - Password reset with SMTP integration

---

## Quick Start

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installed
- Node.js 16+ and npm (for build tools)

### Installation

```bash
# Clone the repository
git clone <repository-url>
cd portfolio

# Install Node dependencies
npm install

# Start Docker containers
docker-compose up -d

# Build assets
npm run build
```

### Access the Application

| Service | URL | Credentials |
|---------|-----|-------------|
| **Portfolio Website** | http://localhost:8080 | - |
| **Admin Dashboard** | http://localhost:8080/admin/ | Set up via database |
| **phpMyAdmin** | http://localhost:8081 | root / root |
| **BrowserSync** (dev) | http://localhost:3000 | - |

---

## Tech Stack

### Backend
- **PHP 8.1** - Server-side logic
- **MySQL 8.0** - Relational database
- **Apache 2.4** - Web server

### Frontend
- **Vanilla JavaScript** - No frameworks, pure JS
- **SCSS** - Modular CSS preprocessing
- **HTML5** - Semantic markup

### Development Tools
- **Docker & Docker Compose** - Containerization
- **Node.js & npm** - Build tooling
- **BrowserSync** - Live reload during development
- **Sass** - SCSS compilation

### UI Libraries
- **Line Awesome** - Icon library (CDN)
- **Adobe Typekit** - Custom fonts (CDN)
- **Particles.js** - Canvas-based particle animations

---

## Project Structure

```
portfolio/
├── src/                          # Source files (EDIT HERE)
│   ├── admin/                    # Admin dashboard
│   │   ├── index.php             # Admin home page
│   │   ├── add-link.php          # API: Add project endpoint
│   │   └── auth-check.php        # Authentication guard
│   ├── includes/                 # Shared PHP components
│   │   ├── html-head.php         # ✨ Opening HTML, head, CSS
│   │   ├── html-close.php        # ✨ Closing tags, JS, DB cleanup
│   │   ├── init.php              # ✨ Session & DB initialization
│   │   ├── connection.php        # Database connection
│   │   ├── user-nav.php          # User navigation bar
│   │   ├── main-nav.php          # Main navigation menu
│   │   ├── login-modal.php       # Login modal component
│   │   ├── forgot-password-modal.php
│   │   ├── add-link-modal.php
│   │   ├── email-service.php     # SMTP email service
│   │   ├── email-config.php      # Email configuration
│   │   └── analytics.php         # Analytics tracking
│   ├── portfolio/                # Portfolio pages
│   │   ├── index.php             # Portfolio carousel
│   │   ├── project.php           # Individual project page
│   │   └── uploads/              # Project images
│   ├── js/                       # JavaScript modules
│   │   ├── particles.js          # Particle canvas
│   │   ├── carousel.js           # Portfolio carousel
│   │   ├── modal.js              # Modal functionality
│   │   ├── main-nav.js           # Navigation interactions
│   │   ├── tooltips.js           # Tooltip system
│   │   └── color-extractor.js    # Image color extraction
│   ├── scss/                     # SCSS source files
│   │   ├── abstracts/
│   │   │   └── _variables.scss   # Design tokens
│   │   ├── base/
│   │   │   ├── _reset.scss       # CSS reset
│   │   │   ├── _typography.scss  # Font styles
│   │   │   └── _utilities.scss   # Utility classes
│   │   ├── components/
│   │   │   ├── _admin.scss       # Admin styles
│   │   │   ├── _buttons.scss     # Button components
│   │   │   ├── _carousel.scss    # Carousel styles
│   │   │   ├── _home.scss        # Home page
│   │   │   ├── _main-nav.scss    # Navigation
│   │   │   ├── _modal.scss       # Modal styles
│   │   │   ├── _navigation.scss  # Navigation base
│   │   │   ├── _portfolio-header.scss
│   │   │   ├── _project.scss     # Project pages
│   │   │   └── _tooltip.scss     # Tooltips
│   │   └── main.scss             # Main entry point
│   ├── img/                      # Images and assets
│   ├── index.php                 # Home page
│   ├── login.php                 # Login API endpoint
│   ├── logout.php                # Logout handler
│   ├── add-link.php              # Add link API
│   ├── forgot-password.php       # Password reset API
│   ├── reset-password.php        # Reset password form
│   ├── reset-password-handler.php
│   ├── logo.png                  # Site logo
│   └── init.sql                  # Database schema
├── dist/                         # Built files (DO NOT EDIT)
│   ├── css/main.css              # Compiled CSS
│   ├── js/bundle.js              # Bundled JavaScript
│   └── (mirrors src/ structure)  # All other files copied
├── dbtools/                      # Database utilities
├── build.js                      # Build script
├── bs-config.js                  # BrowserSync configuration
├── docker-compose.yml            # Docker services
├── Dockerfile                    # PHP container config
├── package.json                  # npm scripts
└── README.md                     # This file
```

**✨ New consolidation:**  Three shared includes (`html-head.php`, `html-close.php`, `init.php`) eliminate ~120 lines of duplicated HTML across all pages.

---

## Development

### npm Scripts

```bash
# Development with live reload
npm run dev              # Full dev mode with BrowserSync on :3000
npm run dev:simple       # Dev mode without BrowserSync

# Production build
npm run build            # Build optimized assets for production

# Individual tasks
npm run sass:build       # Compile SCSS to CSS
npm run sass:watch       # Watch SCSS files for changes
```

### Development Workflow

1. **Start Docker containers:**
   ```bash
   docker-compose up -d
   ```

2. **Start development mode:**
   ```bash
   npm run dev
   ```
   This starts:
   - SCSS compilation with watch mode
   - File sync from `src/` to `dist/`
   - BrowserSync proxy on http://localhost:3000
   - Auto-reload on file changes

3. **Edit files in `src/`**
   - All PHP, JS, SCSS files
   - Changes automatically sync to `dist/`
   - Browser auto-refreshes

4. **Never edit `dist/` directly** - It's auto-generated

### Build System

The build system (`build.js`) automatically handles:

**SCSS Compilation:**
- Compiles `src/scss/main.scss` → `dist/css/main.css`
- Production builds are minified
- Source maps in development mode

**JavaScript Bundling:**
- All `.js` files in `src/js/` → `dist/js/bundle.js`
- Priority files load first (particles, carousel, etc.)
- Single HTTP request for all scripts

**Asset Copying:**
- PHP files, images, uploads automatically copied
- Directory structure preserved
- Hidden files (`.DS_Store`) skipped

### Docker Commands

```bash
# Container management
docker-compose up -d        # Start all services
docker-compose down         # Stop all services
docker-compose restart      # Restart services
docker-compose logs -f      # View logs

# Database access
docker-compose exec db mysql -u portfolio_user -pportfolio_pass portfolio
```

### Adding New Features

**New SCSS Component:**
1. Create `src/scss/components/_feature.scss`
2. Add `@use '../abstracts/variables' as *;` at top
3. Write styles using SCSS features
4. Import in `src/scss/main.scss`: `@use 'components/feature';`
5. Auto-compiles when watching

**New JavaScript Module:**
1. Create `src/js/feature.js`
2. Write module code
3. Auto-bundles into `bundle.js`
4. No configuration needed

**New PHP Page:**
1. Create file in `src/`
2. Include shared components:
   ```php
   <?php
   require_once 'includes/init.php';
   $pageTitle = 'Page Title';
   $bodyClass = 'page-body';
   ?>
   <?php include 'includes/html-head.php'; ?>

   <!-- Your content here -->

   <?php include 'includes/html-close.php'; ?>
   ```
3. Auto-copied to `dist/` when building

---

## Architecture

### PHP Architecture

**Modular Includes System:**
- `html-head.php` - Opening HTML structure, configurable via variables
- `html-close.php` - Closing tags, scripts, database cleanup
- `init.php` - Session management and database connection
- Component includes (modals, navigation) for reusability

**Page Variables:**
```php
$pageTitle = 'Page Title';           // <title> tag
$bodyClass = 'custom-body';          // <body> class
$pathPrefix = '../';                 // For subdirectories
$cssVersion = '11';                  // Cache busting
$skipTypekit = true;                 // Omit Typekit fonts
```

**Database Layer:**
- MySQLi with connection pooling
- Prepared statements for security
- Connection auto-closes in `html-close.php`

### Frontend Architecture

**JavaScript Modules:**
- `particles.js` - Canvas particle system
- `carousel.js` - Portfolio navigation
- `modal.js` - Login/forgot password modals
- `main-nav.js` - Dynamic navigation with database queries
- `tooltips.js` - Tooltip interactions
- `color-extractor.js` - Extract dominant colors from images

**SCSS Organization:**
- **abstracts/** - Variables, mixins, functions
- **base/** - Resets, typography, utilities
- **components/** - Individual UI components
- Follows 7-1 pattern principles

### Authentication Flow

1. User submits login form → `login.php` API
2. Server validates credentials, creates session
3. Redirects to admin or returns to referring page
4. `auth-check.php` guards admin routes
5. Password reset via email token system

---

## Database

### Schema Overview

**Tables:**
- `users` - User accounts with password hashing
- `projects` - Portfolio projects with metadata
- `themes` - Project theme/category system

**Key Fields:**
```sql
projects:
  - projectID (PK)
  - projectHeading
  - projectDescription
  - projectTeaser (image filename)
  - blobEntry (0=portfolio, 1=blob menu)
  - url, url_two (external links)

users:
  - userID (PK)
  - username, email
  - password (hashed)
  - reset_token, reset_token_expiry
```

### Database Configuration

```php
// src/includes/connection.php
$host = 'db';  // Docker service name
$dbname = 'portfolio';
$username = 'portfolio_user';
$password = 'portfolio_pass';
```

### Initialization

Database automatically initializes from `src/init.sql` on first run:
- Creates tables
- Sets up indexes
- Inserts sample data
- Creates default user

### Backup & Restore

```bash
# Backup
docker-compose exec db mysqldump -u portfolio_user -pportfolio_pass portfolio > backup.sql

# Restore
docker-compose exec -T db mysql -u portfolio_user -pportfolio_pass portfolio < backup.sql
```

---

## Deployment

### Production Checklist

- [ ] Run `npm run build` to create optimized assets
- [ ] **Create `.env` file** - Copy from `.env.example` and set production credentials
- [ ] Ensure `.env` is in project root (NOT in dist/)
- [ ] Set production database credentials in `.env`
- [ ] Remove or secure phpMyAdmin
- [ ] Enable HTTPS/SSL certificates
- [ ] Set secure session cookie settings
- [ ] Configure SMTP for password resets
- [ ] Update `email-config.php` with production settings
- [ ] Test all authentication flows
- [ ] Verify file upload permissions (755 for directories)
- [ ] Set proper `.htaccess` rules
- [ ] **Run password migration** - Execute `migrate-passwords.php` if needed

### Environment Variables

For production, consider externalizing:
- Database credentials
- SMTP settings
- API keys
- Debug mode toggles

### Build for Production

```bash
# Create production build
npm run build

# Deploy dist/ folder contents to server
rsync -avz dist/ user@server:/var/www/html/
```

---

## Security

### Current Implementation

**✅ Implemented (Recently Improved):**
- ✅ **Environment variables** - Database credentials in `.env` (Phase 1)
- ✅ **Password hashing** - Bcrypt with `password_hash()` (Phase 1)
- ✅ **Secure sessions** - Centralized session management (Phase 2)
- ✅ **File upload validation** - MIME type, size, extension checks (Phase 5)
- ✅ **Centralized API responses** - Consistent error handling (Phase 2)
- ✅ **Prepared statements** - For database queries (in most places)
- ✅ **CSRF protection** - Via session validation
- ✅ **Email token expiry** - For password resets
- ✅ **HTTPS ready** - Configure in production

**⚠️ Development Only:**
- phpMyAdmin exposed
- Error display enabled
- No rate limiting

### Production Security Recommendations

**✅ Already Implemented:**
1. ✅ ~~Update all credentials~~ - **DONE:** Using `.env` environment variables
2. ✅ ~~File upload validation~~ - **DONE:** Type, size, and extension restrictions

**Still TODO:**
3. **Implement prepared statements everywhere** - Prevent SQL injection (mostly done)
4. **Add CSRF tokens** - To all forms
5. **Input validation** - Sanitize all user inputs
6. **Rate limiting** - Prevent brute force attacks
7. **Content Security Policy** - Add CSP headers
8. **Secure headers** - X-Frame-Options, X-Content-Type-Options
9. **Remove phpMyAdmin** - Or restrict to IP whitelist
10. **Enable HTTPS** - Redirect all HTTP to HTTPS

### SQL Injection Prevention

Example of safe queries:
```php
// ✅ Good - Prepared statement
$stmt = $mysqli->prepare("SELECT * FROM projects WHERE projectID = ?");
$stmt->bind_param("i", $projectID);
$stmt->execute();

// ❌ Bad - Direct interpolation (some legacy code)
$sql = "SELECT * FROM projects WHERE projectID = '$projectID'";
```

---

## License

© Carbontype. All rights reserved.

---

## Support

For issues or questions, please contact the development team
