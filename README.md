# GeoWall

GeoWall restricts access to selected content for visitors outside allowed countries. It relies on the Cloudflare `CF-IPCountry` header and works with Drupal page caching.

## Configuration

1. Enable the module and visit `/admin/config/system/geowall`.
2. Enter one ISO country code per line in **Allowed countries**.
3. Choose content types and add path patterns that should be restricted.
4. Add any paths that must remain accessible in **Excluded paths** (administration paths are prefilled).
5. Provide an internal **Redirect path** where disallowed visitors are sent.
6. Select how to handle requests when the `CF-IPCountry` header is missing.

## Cloudflare Setup

Ensure Cloudflare is configured to pass the `CF-IPCountry` header to Drupal. The module uses this value to determine the visitor's location and adds `Vary: CF-IPCountry` to responses so caches can store country‑specific pages.

## Caching

GeoWall adds a custom cache context (`geowall.geo_access`) so that Drupal's page cache and CDNs can vary on whether a visitor is allowed or disallowed. Make sure upstream caches respect the `Vary` header to avoid serving restricted content to the wrong audience.
