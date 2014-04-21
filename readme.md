### Manages
* Varnish Purging
* URL Rewrites
* vElasic Search

### Instance Properties
Properties set by Bootstrap class to the global $wp_veneer object.

* site      - Current site's domain.
* apex      - Current site's apex domain.
* network   - Network's (if applicable) apex domain.
* site_id   - Current site's blog ID.

### W3 Total Cache Issues & Notes
* Verified for seperate Networks: Plugin Activation,
* Ideally "cache" should be located in static/storage/{domain}/cache
* Only single hostname is allowed in settings - stereolivehouston.com images go to direct.nightculture.com
* Files uploaded to "CDN" must be somehow organized by domain.
* Theme files are uploaded into a /system/themes directory. Where does "system" come from?
* The system/.htaccess file.
* Theme file upload only looks for minified files.
* Upgrade Network must be ran after W3 is activated to create tables.
* minified cache broken because URLs go to http://direct.nightculture.com/cache/minify/... while uploads go to /system/minify/... (could be fixed w/ "static" hostname)
* supports_full_page_mirroring is disabled on FTP... why?

