# SEO & Searchability Implementation Guide

> **Status:** Ready to implement when portfolio is complete
>
> **Estimated Total Time:** 8-12 hours
>
> **Expected Impact:** Very High - Critical for discoverability

---

## Table of Contents

1. [Current State Assessment](#current-state-assessment)
2. [Critical Issues (Week 1)](#week-1-critical-foundation)
3. [Social & Analytics (Week 2)](#week-2-social--analytics)
4. [Technical Optimization (Week 3)](#week-3-technical-optimization)
5. [Monitoring Setup (Week 4)](#week-4-monitoring--refinement)
6. [Implementation Code](#implementation-code)
7. [Testing Checklist](#testing-checklist)
8. [Ongoing Maintenance](#ongoing-maintenance)

---

## Current State Assessment

### What's Missing (Critical)
- ❌ No meta descriptions on any page
- ❌ No Open Graph tags for social media sharing
- ❌ No Twitter Card markup
- ❌ No Schema.org structured data (Person, Organization, CreativeWork)
- ❌ No robots.txt or sitemap.xml
- ❌ No canonical URLs
- ❌ Outdated Google Analytics (Universal Analytics is deprecated)
- ❌ Minimal alt text on images
- ❌ Generic/long page titles (home page is 95 characters - too long!)
- ❌ No favicon implementation
- ❌ SQL injection vulnerability in project.php (security impacts SEO trust signals)

### What's Working
- ✅ Mobile viewport meta tag present
- ✅ Semantic HTML structure with proper headings
- ✅ Clean URL structure for portfolio items
- ✅ HTTPS-ready architecture

---

## Implementation Priority Matrix

| Priority | Task | Time | SEO Impact | Files |
|----------|------|------|------------|-------|
| **CRITICAL** | Add meta descriptions & OG tags | 2h | Very High | seo-meta.php, html-head.php, all pages |
| **CRITICAL** | Fix SQL injection vulnerability | 30m | High | project.php |
| **CRITICAL** | Implement Schema.org markup | 1h | Very High | schema-markup.php |
| **HIGH** | Create robots.txt | 10m | High | robots.txt |
| **HIGH** | Create XML sitemap | 1h | High | sitemap.php |
| **HIGH** | Upgrade to GA4 | 30m | Medium | analytics.php |
| **HIGH** | Create OG share images | 2h | High | Design work |
| **MEDIUM** | Add breadcrumb navigation | 1h | Medium | breadcrumbs.php |
| **MEDIUM** | Implement lazy loading | 30m | Medium | Image tags |
| **MEDIUM** | Add .htaccess optimizations | 20m | Medium | .htaccess |
| **LOW** | Clean URL structure | 4h | Low-Med | Multiple files |
| **LOW** | Add RSS feed | 1h | Low | feed.php |

---

## Week 1: Critical Foundation

### 1. Create SEO Meta Tags System

**File:** `src/includes/seo-meta.php`

```php
<?php
/**
 * SEO Meta Tags Generator
 * Generates comprehensive meta tags for SEO and social sharing
 */

// Default values
$seo = [
    'title' => $pageTitle ?? 'Jonny Pyper | Carbontype | Award-Winning Designer & Engineering Manager',
    'description' => $pageDescription ?? 'Portfolio of Jonny Pyper (Carbontype): Award-winning UI designer and Software Engineering Manager at R7. View creative projects spanning web design, branding, and digital experiences.',
    'keywords' => $pageKeywords ?? 'UI designer, software engineering manager, portfolio, web design, branding, creative director, Jonny Pyper, Carbontype',
    'og_type' => $ogType ?? 'website',
    'og_image' => $ogImage ?? ($pathPrefix ?? '') . 'img/og-default.jpg',
    'og_image_width' => $ogImageWidth ?? '1200',
    'og_image_height' => $ogImageHeight ?? '630',
    'canonical' => $canonicalURL ?? 'https://carbontype.co' . $_SERVER['REQUEST_URI'],
    'author' => 'Jonny Pyper',
    'twitter_card' => 'summary_large_image',
    'twitter_handle' => '@carbontype' // Update with actual Twitter handle
];

// Build full title with branding
$fullTitle = $seo['title'];
if (strpos($fullTitle, 'Carbontype') === false && $fullTitle !== 'Carbontype') {
    $fullTitle .= ' | Carbontype';
}

// Ensure title is under 60 characters for optimal display
if (strlen($fullTitle) > 60) {
    $fullTitle = substr($fullTitle, 0, 57) . '...';
}

// Ensure description is 150-160 characters
$description = $seo['description'];
if (strlen($description) > 160) {
    $description = substr($description, 0, 157) . '...';
}
?>

<!-- Primary Meta Tags -->
<meta name="title" content="<?php echo htmlspecialchars($fullTitle); ?>">
<meta name="description" content="<?php echo htmlspecialchars($description); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($seo['keywords']); ?>">
<meta name="author" content="<?php echo htmlspecialchars($seo['author']); ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?php echo htmlspecialchars($seo['canonical']); ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?php echo htmlspecialchars($seo['og_type']); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($seo['canonical']); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($seo['og_image']); ?>">
<meta property="og:image:width" content="<?php echo htmlspecialchars($seo['og_image_width']); ?>">
<meta property="og:image:height" content="<?php echo htmlspecialchars($seo['og_image_height']); ?>">
<meta property="og:site_name" content="Carbontype Portfolio">

<!-- Twitter -->
<meta property="twitter:card" content="<?php echo htmlspecialchars($seo['twitter_card']); ?>">
<meta property="twitter:url" content="<?php echo htmlspecialchars($seo['canonical']); ?>">
<meta property="twitter:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
<meta property="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
<meta property="twitter:image" content="<?php echo htmlspecialchars($seo['og_image']); ?>">
<?php if (!empty($seo['twitter_handle'])): ?>
<meta name="twitter:creator" content="<?php echo htmlspecialchars($seo['twitter_handle']); ?>">
<?php endif; ?>

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo ($pathPrefix ?? '') . 'favicon-32x32.png'; ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo ($pathPrefix ?? '') . 'favicon-16x16.png'; ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo ($pathPrefix ?? '') . 'apple-touch-icon.png'; ?>">
<link rel="manifest" href="<?php echo ($pathPrefix ?? '') . 'site.webmanifest'; ?>">

<!-- Additional SEO -->
<meta name="theme-color" content="#000000">
<meta name="format-detection" content="telephone=no">
```

**Update `src/includes/html-head.php`** - Add after `<meta charset>`:

```php
<?php include __DIR__ . '/seo-meta.php'; ?>
```

---

### 2. Update Page-Specific SEO Variables

**Homepage (`src/index.php`)** - Add before html-head include:

```php
// Set page variables - SEO OPTIMIZED
$pageTitle = 'Jonny Pyper | UI Designer & Engineering Manager';
$pageDescription = 'Award-winning UI designer and Software Engineering Manager at R7. Explore my portfolio of creative projects, web design, and digital experiences.';
$pageKeywords = 'UI designer, engineering manager, portfolio, web design, creative director, R7, Jonny Pyper, Carbontype';
$canonicalURL = 'https://carbontype.co/';
$ogImage = 'https://carbontype.co/img/og-home.jpg';
```

**Portfolio Page (`src/portfolio/index.php`):**

```php
// Count projects for description
$projectCount = $result->num_rows;

// Set page variables - SEO OPTIMIZED
$pageTitle = 'Portfolio Projects | Carbontype';
$pageDescription = "Browse {$projectCount} creative projects including UI design, web development, branding, and digital experiences.";
$pageKeywords = 'portfolio, projects, UI design, web design, case studies, creative work';
$canonicalURL = 'https://carbontype.co/portfolio/';
$ogType = 'website';
$ogImage = 'https://carbontype.co/img/og-portfolio.jpg';
```

**Project Page (`src/portfolio/project.php`)** - CRITICAL: Fix SQL injection:

```php
$projectIDurl = intval($_GET["id"] ?? 0); // FIX SQL INJECTION

if ($projectIDurl <= 0) {
    header("Location: ./");
    exit;
}

// Use prepared statement to prevent SQL injection
$stmt = $mysqli->prepare("SELECT * FROM projects WHERE projectID = ? LIMIT 1");
$stmt->bind_param("i", $projectIDurl);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ./");
    exit;
}

$row = $result->fetch_assoc();
$projectHeading = $row["projectHeading"];
$projectDescription = $row["projectDescription"];
$projectTeaser = $row["projectTeaser"];

// Set page variables - SEO OPTIMIZED
$pageTitle = $projectHeading;
$pageDescription = strip_tags(substr($projectDescription, 0, 155)) . "...";
$pageKeywords = $projectHeading . ', portfolio project, case study, UI design, web design, Carbontype';
$canonicalURL = "https://carbontype.co/portfolio/project.php?id={$projectIDurl}";
$ogType = 'article';
$ogImage = "https://carbontype.co/img/uploads/{$projectTeaser}";
```

---

### 3. Create Schema.org Structured Data

**File:** `src/includes/schema-markup.php`

```php
<?php
/**
 * Schema.org Structured Data for SEO
 */

$schemaOrg = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => 'Jonny Pyper',
    'alternateName' => 'Carbontype',
    'url' => 'https://carbontype.co',
    'image' => 'https://carbontype.co/logo.png',
    'jobTitle' => 'Software Engineering Manager',
    'worksFor' => [
        '@type' => 'Organization',
        'name' => 'R7'
    ],
    'description' => 'Award-winning UI designer and Software Engineering Manager specializing in web design, user experience, and creative direction.',
    'knowsAbout' => [
        'UI Design',
        'Web Development',
        'Software Engineering',
        'User Experience',
        'Creative Direction',
        'Branding'
    ],
    'sameAs' => [
        // Add your social profiles here
        'https://github.com/yourusername',
        'https://linkedin.com/in/yourusername',
        'https://twitter.com/carbontype'
    ]
];

// Page-specific schema additions
if (isset($projectHeading) && isset($projectDescription)) {
    // Project page - use CreativeWork schema
    $schemaOrg = [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $projectHeading,
        'description' => strip_tags($projectDescription),
        'author' => [
            '@type' => 'Person',
            'name' => 'Jonny Pyper',
            'url' => 'https://carbontype.co'
        ],
        'image' => isset($ogImage) ? $ogImage : 'https://carbontype.co/logo.png',
        'url' => isset($canonicalURL) ? $canonicalURL : 'https://carbontype.co'
    ];
}
?>

<script type="application/ld+json">
<?php echo json_encode($schemaOrg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>
```

**Add to `src/includes/html-head.php`** before closing `</head>`:

```php
<?php include __DIR__ . '/schema-markup.php'; ?>
```

---

### 4. Create robots.txt

**File:** `dist/robots.txt`

```txt
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /includes/
Disallow: /login.php
Disallow: /forgot-password.php
Disallow: /reset-password.php
Disallow: /logout.php

# Sitemap
Sitemap: https://carbontype.co/sitemap.xml

# Crawl delay (optional)
Crawl-delay: 1
```

**Update `build.js`** to copy robots.txt:

```javascript
// After other file copying, add:
copyFile('robots.txt');
```

---

### 5. Create Dynamic XML Sitemap

**File:** `src/sitemap.php`

```php
<?php
require_once 'includes/init.php';

header('Content-Type: application/xml; charset=utf-8');

$baseURL = 'https://carbontype.co'; // UPDATE THIS

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    <!-- Homepage -->
    <url>
        <loc><?php echo $baseURL; ?>/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Portfolio Index -->
    <url>
        <loc><?php echo $baseURL; ?>/portfolio/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <?php
    // Get all public projects
    $sql = "SELECT projectID, projectHeading, projectTeaser FROM projects WHERE blobEntry = 0 ORDER BY projectID ASC";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $projectID = $row['projectID'];
            $projectHeading = htmlspecialchars($row['projectHeading']);
            $projectTeaser = htmlspecialchars($row['projectTeaser']);
            ?>
    <url>
        <loc><?php echo $baseURL; ?>/portfolio/project.php?id=<?php echo $projectID; ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
        <image:image>
            <image:loc><?php echo $baseURL; ?>/img/uploads/<?php echo $projectTeaser; ?></image:loc>
            <image:caption><?php echo $projectHeading; ?></image:caption>
        </image:image>
    </url>
    <?php
        }
    }

    $mysqli->close();
    ?>
</urlset>
```

---

## Week 2: Social & Analytics

### 6. Create Social Media Share Images

**Required Images (Design in Figma/Photoshop):**

- `dist/img/og-default.jpg` - Generic portfolio (1200x630px)
- `dist/img/og-home.jpg` - Homepage specific (1200x630px)
- `dist/img/og-portfolio.jpg` - Portfolio page (1200x630px)
- `dist/favicon-32x32.png` - 32x32 favicon
- `dist/favicon-16x16.png` - 16x16 favicon
- `dist/apple-touch-icon.png` - 180x180 Apple icon

**OG Image Design Guidelines:**
- Size: 1200x630px (Facebook/LinkedIn standard)
- Include your logo/branding prominently
- Add text overlay with name/tagline
- Use high-contrast, visually striking imagery
- Keep important content in center "safe zone"
- Test at thumbnail size - text must be readable
- Save as JPG, optimize for web (<200KB)

**Tools:**
- Figma OG template: https://www.figma.com/community/file/823373862874232558
- Canva OG templates: https://www.canva.com/templates/s/open-graph/
- Preview tool: https://www.opengraph.xyz/

---

### 7. Upgrade to Google Analytics 4

**File:** `src/includes/analytics.php`

```php
<!-- Google tag (gtag.js) - Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-XXXXXXXXXX', {
    'send_page_view': true,
    'page_title': '<?php echo htmlspecialchars($pageTitle ?? "Carbontype"); ?>',
    'page_location': window.location.href
  });
</script>
```

**Setup Steps:**
1. Go to https://analytics.google.com/
2. Create new GA4 property
3. Get Measurement ID (starts with G-)
4. Replace G-XXXXXXXXXX with your ID
5. Remove old Universal Analytics code

---

## Week 3: Technical Optimization

### 8. Add Performance Optimizations

**Update `src/includes/html-head.php`** - Add before stylesheet links:

```php
<!-- Preconnect to external domains -->
<link rel="preconnect" href="https://use.typekit.net" crossorigin>
<link rel="preconnect" href="https://maxst.icons8.com" crossorigin>
<link rel="dns-prefetch" href="https://www.google-analytics.com">
```

---

### 9. Create .htaccess Optimizations

**File:** `dist/.htaccess`

```apache
# Enable Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Force HTTPS (enable in production)
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteCond %{HTTPS} off
#     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
# </IfModule>
```

---

### 10. Improve Image Alt Text

**Portfolio carousel (`src/portfolio/index.php`):**

```php
<img src="uploads/<?php echo $projectTeaser; ?>"
     alt="<?php echo htmlspecialchars($projectHeading); ?> - UI design and development case study"
     class="holder-img"
     loading="lazy"
     decoding="async">
```

**All images should follow pattern:**
```php
alt="[Project Name] - [Brief Description of Content]"
```

---

### 11. Add Breadcrumb Navigation (Optional)

**File:** `src/includes/breadcrumbs.php`

```php
<?php
/**
 * Breadcrumb navigation with Schema.org markup
 */

$breadcrumbs = $breadcrumbs ?? [];

if (!empty($breadcrumbs)) {
    echo '<nav aria-label="Breadcrumb" class="breadcrumbs">';
    echo '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

    $position = 1;
    foreach ($breadcrumbs as $crumb) {
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

        if (!empty($crumb['url'])) {
            echo '<a itemprop="item" href="' . htmlspecialchars($crumb['url']) . '">';
            echo '<span itemprop="name">' . htmlspecialchars($crumb['name']) . '</span>';
            echo '</a>';
        } else {
            echo '<span itemprop="name">' . htmlspecialchars($crumb['name']) . '</span>';
        }

        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';

        $position++;
    }

    echo '</ol>';
    echo '</nav>';
}
?>
```

**Usage in project pages:**

```php
// In portfolio/project.php
$breadcrumbs = [
    ['name' => 'Home', 'url' => '../../'],
    ['name' => 'Portfolio', 'url' => '../'],
    ['name' => $projectHeading]
];

// Then include:
include '../includes/breadcrumbs.php';
```

---

## Week 4: Monitoring & Refinement

### 12. Set Up Google Search Console

**Steps:**
1. Go to https://search.google.com/search-console
2. Add property (use domain or URL prefix)
3. Verify ownership (HTML file or DNS)
4. Submit sitemap: `https://yourdomain.com/sitemap.php`
5. Monitor indexing and errors

**Key Reports to Watch:**
- Coverage (indexed pages)
- Performance (search queries)
- Core Web Vitals
- Mobile usability

---

### 13. Test with SEO Tools

**Required Testing:**

1. **Google Rich Results Test**
   - URL: https://search.google.com/test/rich-results
   - Test homepage and project pages
   - Verify structured data appears

2. **PageSpeed Insights**
   - URL: https://pagespeed.web.dev/
   - Test mobile and desktop
   - Aim for 90+ score

3. **Mobile-Friendly Test**
   - URL: https://search.google.com/test/mobile-friendly
   - Ensure all pages pass

4. **Schema Markup Validator**
   - URL: https://validator.schema.org/
   - Paste page URL, check for errors

5. **Facebook Sharing Debugger**
   - URL: https://developers.facebook.com/tools/debug/
   - Test OG tags on all pages

6. **Twitter Card Validator**
   - URL: https://cards-dev.twitter.com/validator
   - Test Twitter card markup

---

## Testing Checklist

Before considering SEO complete, verify:

**Week 1 Implementation:**
- [ ] seo-meta.php created and included in html-head.php
- [ ] All pages have unique titles under 60 characters
- [ ] All pages have unique descriptions 150-160 characters
- [ ] schema-markup.php created and included
- [ ] SQL injection fixed in project.php (uses prepared statements)
- [ ] robots.txt created in dist/
- [ ] sitemap.php created and accessible at /sitemap.xml
- [ ] sitemap.php copied to dist/ by build process

**Week 2 Implementation:**
- [ ] OG images created (1200x630px) for home, portfolio, default
- [ ] Favicons created (16x16, 32x32, 180x180)
- [ ] site.webmanifest created
- [ ] Google Analytics 4 measurement ID added
- [ ] Old UA code removed

**Week 3 Implementation:**
- [ ] Preconnect tags added for external resources
- [ ] .htaccess created with compression and caching
- [ ] All images have descriptive alt text
- [ ] Lazy loading added to all images
- [ ] Image dimensions specified (width/height)

**Week 4 Testing:**
- [ ] Google Search Console verified and sitemap submitted
- [ ] Rich Results Test passes for all page types
- [ ] PageSpeed score 90+ on mobile and desktop
- [ ] Mobile-Friendly Test passes
- [ ] Schema validator shows no errors
- [ ] Facebook debugger shows correct OG image
- [ ] Twitter validator shows correct card

---

## Keyword Strategy

### Primary Keywords
- "UI designer portfolio"
- "engineering manager portfolio"
- "[Your name] designer"
- "creative director portfolio"

### Secondary Keywords
- "web design case studies"
- "UI/UX design projects"
- "award-winning designer"
- "software engineering manager"

### Long-tail Keywords
- "interactive portfolio with case studies"
- "software engineering manager with design background"
- Specific project types/industries

### Implementation
Add relevant keywords naturally to:
- Page titles
- Meta descriptions
- H1/H2 headings
- Project descriptions
- Image alt text
- URL slugs (if using clean URLs)

---

## Content Strategy Recommendations

### 1. Expand Project Pages
Add to each project:
- **Challenge:** What problem was being solved?
- **Solution:** Your approach and methodology
- **Technologies:** Tools and languages used
- **Results:** Measurable outcomes if available
- **Role:** Your specific contribution
- **Testimonial:** Client quote (if available)

### 2. Consider Adding
- **About Page:** Full bio, skills, timeline, awards
- **Blog/Articles:** Design process, industry insights
- **Contact Page:** Clear CTA with form
- **Services Page:** What you offer to potential clients

### 3. Content Best Practices
- Write for humans first, search engines second
- Use clear, descriptive headings (H1, H2, H3)
- Keep paragraphs short (2-3 sentences)
- Include relevant keywords naturally
- Add internal links between related projects
- Update regularly with new work
- Use bullet points for scanability

---

## Ongoing Maintenance

### Monthly Tasks
- [ ] Check Google Search Console for errors
- [ ] Review top performing pages/keywords
- [ ] Analyze traffic sources in GA4
- [ ] Update underperforming content
- [ ] Add new projects to portfolio
- [ ] Monitor competitors' rankings
- [ ] Check broken links
- [ ] Optimize meta descriptions based on CTR

### Quarterly Tasks
- [ ] Full content audit
- [ ] Refresh OG images if brand changes
- [ ] Update about/bio information
- [ ] Review and update keywords
- [ ] Competitor SEO analysis
- [ ] Technical SEO audit
- [ ] Core Web Vitals review

### Annual Tasks
- [ ] Complete site-wide content refresh
- [ ] Reevaluate SEO strategy
- [ ] Update Schema.org markup
- [ ] Redesign OG images if needed
- [ ] Review all third-party integrations
- [ ] Security audit

---

## Key Metrics to Track

### Search Performance
- Organic search traffic (monthly)
- Top performing keywords
- Average position in search results
- Click-through rate (CTR)
- Impressions vs clicks

### User Engagement
- Bounce rate by traffic source
- Average session duration
- Pages per session
- Conversion rate (contact form submissions)

### Technical SEO
- Indexed pages count
- Page load speed (aim: <3 seconds)
- Core Web Vitals scores
  - LCP (Largest Contentful Paint) - <2.5s
  - FID (First Input Delay) - <100ms
  - CLS (Cumulative Layout Shift) - <0.1

### Social Signals
- Social shares per project
- Referral traffic from social
- OG image performance

---

## Common Portfolio SEO Mistakes to Avoid

1. **Over-optimization** - Don't stuff keywords unnaturally
2. **Missing alt text** - Every project image needs descriptive alt text
3. **Slow image loading** - Compress images (aim for <200KB per image)
4. **No social optimization** - OG images are critical for shares
5. **Generic project titles** - Each needs unique, descriptive title
6. **Ignoring mobile** - 60%+ of searches are mobile
7. **No internal linking** - Link between related projects
8. **Outdated content** - Update portfolio with new work regularly
9. **No clear CTA** - Make it easy for people to contact you
10. **Writing for bots** - Always write for humans first

---

## Tools & Resources

### Free SEO Tools
- **Google Search Console:** https://search.google.com/search-console
- **Google Analytics 4:** https://analytics.google.com
- **PageSpeed Insights:** https://pagespeed.web.dev/
- **Rich Results Test:** https://search.google.com/test/rich-results
- **Mobile-Friendly Test:** https://search.google.com/test/mobile-friendly
- **Schema Validator:** https://validator.schema.org/

### Social Preview Tools
- **Facebook Debugger:** https://developers.facebook.com/tools/debug/
- **Twitter Card Validator:** https://cards-dev.twitter.com/validator
- **LinkedIn Inspector:** https://www.linkedin.com/post-inspector/
- **OpenGraph Preview:** https://www.opengraph.xyz/

### Image Optimization
- **TinyPNG:** https://tinypng.com/
- **ImageOptim:** https://imageoptim.com/
- **Squoosh:** https://squoosh.app/

### OG Image Creation
- **Figma OG Template:** https://www.figma.com/community/file/823373862874232558
- **Canva OG Templates:** https://www.canva.com/templates/s/open-graph/

---

## Domain Configuration Checklist

**Before going live, ensure:**

- [ ] Update all instances of `https://carbontype.co` to your actual domain
- [ ] Update `$baseURL` in sitemap.php
- [ ] Update canonical URLs in seo-meta.php
- [ ] Update OG image URLs to use full domain
- [ ] Update Schema.org URLs
- [ ] Test all social sharing with live domain
- [ ] Enable HTTPS redirect in .htaccess
- [ ] Verify SSL certificate is valid
- [ ] Test all pages with live domain
- [ ] Submit live sitemap to Google Search Console

---

## Quick Reference: Implementation Order

**Day 1 (2-3 hours):**
1. Create seo-meta.php
2. Update html-head.php to include it
3. Add SEO variables to index.php, portfolio/index.php
4. Fix SQL injection in project.php
5. Add SEO variables to project.php

**Day 2 (2-3 hours):**
6. Create schema-markup.php
7. Update html-head.php to include schema
8. Create robots.txt
9. Create sitemap.php
10. Update build.js to copy robots.txt

**Day 3 (2-3 hours):**
11. Design and create OG images (1200x630)
12. Create favicons (16x16, 32x32, 180x180)
13. Create site.webmanifest
14. Set up Google Analytics 4
15. Update analytics.php with new GA4 code

**Day 4 (2-3 hours):**
16. Add preconnect tags to html-head.php
17. Create .htaccess with optimizations
18. Update all images with proper alt text
19. Test with PageSpeed Insights
20. Test with Rich Results Test

**Day 5 (1-2 hours):**
21. Set up Google Search Console
22. Verify domain ownership
23. Submit sitemap
24. Test all social sharing previews
25. Create baseline analytics report

---

## Support & Questions

If you run into issues during implementation:

1. **Google Search Central:** https://developers.google.com/search
2. **Schema.org Documentation:** https://schema.org/docs/gs.html
3. **Open Graph Protocol:** https://ogp.me/
4. **Web.dev SEO Guide:** https://web.dev/learn-seo/

---

**Last Updated:** 2025-01-08
**Version:** 1.0
**Status:** Ready for implementation when portfolio is complete

---

## Notes

- All file paths assume standard project structure
- Update domain `carbontype.co` to your actual domain
- Replace social media handles with your actual profiles
- Test everything in development before deploying to production
- Keep this file updated as you implement changes
- Consider hiring an SEO consultant for competitive analysis after initial implementation
