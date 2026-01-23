# Release Process

## Version Bumping

When creating a new release, bump the version in ALL THREE places:

1. `style.css` - Theme header `Version:` field
2. `CHANGELOG.md` - Add new version section at top
3. `readme.txt` - Add entry in Changelog section

This is required to bust CDN caches - the site uses Cloudflare caching.

## Creating a Zip

```bash
zip -r ../wpamesh-theme-X.Y.Z.zip . -x "*.git*" -x "*.DS_Store" -x "scripts/*" -x "*.baked"
```

Exclude:
- `.git/` - Version control
- `scripts/` - Development utilities
- `*.baked` - Temporary preview files

## WAF Considerations

The live site (wpamesh.net) has aggressive ModSecurity + Cloudflare WAF rules:
- Scripts fetching the REST API may get 403/406 errors
- Use curl with output to temp file rather than piping
- Query parameters on API endpoints may trigger blocks
- Python `requests` library often fails; curl subprocess works better
