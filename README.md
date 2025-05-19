# GeoWall

GeoWall restricts access to selected content for visitors outside allowed countries. It relies on the Cloudflare `CF-IPCountry` header and works with Drupal page caching.

## Installation

Install the module with Composer:

```bash
composer require drupal/geowall
```

After installing, enable the module through the administration UI or with Drush.

## Configuration

1. Enable the module and visit `/admin/config/system/geowall`.
2. Enter one ISO country code per line in **Allowed countries**.
3. Choose content types and add path patterns that should be restricted.
4. Add any paths that must remain accessible in **Excluded paths** (administration paths are prefilled).
5. Provide an internal **Redirect path** where disallowed visitors are sent.
6. Select how to handle requests when the `CF-IPCountry` header is missing.

## Cloudflare Setup

Ensure Cloudflare is configured to pass the `CF-IPCountry` header to Drupal. The module uses this value to determine the visitor's location and adds `Vary: CF-IPCountry` to responses so caches can store country‑specific pages.

## Trusting the CF-IPCountry header

GeoWall assumes the `CF-IPCountry` value is reliable and does not verify that requests originate from Cloudflare. If this header is critical, restrict direct access or validate Cloudflare's IP ranges.

## Caching

GeoWall adds a custom cache context (`geowall.geo_access`) so that Drupal's page cache and CDNs can vary on whether a visitor is allowed or disallowed. Make sure upstream caches respect the `Vary` header to avoid serving restricted content to the wrong audience.

## Benefits

- **Works on Cloudflare Free & Pro plans**
  Uses the built-in `CF-IPCountry` header — no need for Workers, Page Rules, or Enterprise features.
- **No external GeoIP dependencies**
  Unlike Smart IP or MaxMind-based solutions, it doesn't require a GeoIP database, license, or API.
- **Runs entirely in Drupal — version-controlled and contextual**
  Manage geo-restriction in Drupal configuration and apply content-specific logic.
- **Redirects before page rendering — efficient and cache-safe**
  Fires early in the Symfony request cycle and properly sets `Vary: CF-IPCountry`.
- **Lightweight and targeted**
  Focuses on the common use case of redirecting disallowed visitors away from select content.
- **Fills a gap in contrib**
  No current Drupal 9/10 module offers this exact mix of Cloudflare-native, lightweight, redirect-based control.

This makes GeoWall ideal for:

- Geo-restricted videos or downloads
- Simple legal compliance gating
- Simple staging rollouts by country
