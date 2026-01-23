# /new-page - Create WPAMesh Preview Page

Create a new preview page in `pages/previews/` based on user-provided content brief.

## Triggers
- `/new-page <brief>` - User provides topic/content description as argument

## Workflow

1. **Parse the brief** - Extract topic, key sections, target audience from user input
2. **Generate content** - Write full guide content (not placeholders) in WPAMesh voice
3. **Create HTML file** - Use the preview page template structure
4. **Save to `pages/previews/`** - Use kebab-case filename matching the topic

## Preview Page Template Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{Page Title} – WPAMesh.net</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Include full CSS from existing preview pages */
    </style>
</head>
<body>
    <div class="page-layout">
        <main class="main-content">
            <header class="content-header">
                <h1>{Title}</h1>
                <p class="subtitle">{Subtitle}</p>
            </header>
            <div class="content-body">
                <article class="article-content">
                    <!-- Content here -->
                </article>
            </div>
        </main>
        <aside class="toc-container">
            <nav class="toc">
                <div class="toc-title">On This Page</div>
                <ul class="toc-list">
                    <!-- TOC items -->
                </ul>
            </nav>
        </aside>
    </div>
    <script>
        /* TOC scroll tracking script */
    </script>
</body>
</html>
```

## Available Content Components

Use these CSS classes (copy styles from existing preview pages):

### Text & Structure
- `h2` with `id` attribute - Section headings (add to TOC)
- `h3` - Subsection headings
- `p`, `ul`, `ol`, `li` - Standard content
- `code` - Inline code
- `blockquote` - Notes/quotes

### Boxes & Cards
- `.intro-box` - Gold-bordered intro paragraph at top
- `.info-card` / `.info-card.highlight` - Information cards with emoji header
- `.alert.alert-{danger|warning|info|success}` - Colored alert boxes with `.alert-title`
- `.summary-box` - Green success box for conclusions

### Data Display
- `.settings-table` - Technical settings tables
- `.steps` - Numbered step lists with `.steps-title`

### Interactive
- `.cta-btn` - Call-to-action button (typically Discord link)
- `.toc-list a` - TOC links with scroll tracking

## Content Voice & Style

- **Friendly and approachable** - Write like helping a neighbor
- **Technical but accessible** - Explain jargon when first used
- **Pittsburgh flavor** - Reference local geography/community when relevant
- **Practical focus** - Lead with "what to do", explain "why" after
- **Use emojis sparingly** - In h2 headings and alert titles only

## File Naming

- Use kebab-case: `antenna-tuning-guide.html`, `troubleshooting-connections.html`
- Prefix with `wpamesh-` for brand guides: `wpamesh-getting-started.html`

## After Creation

Inform user:
1. File location: `pages/previews/{filename}.html`
2. Open in browser to preview
3. Run `/wp-convert` when ready to migrate to WordPress
