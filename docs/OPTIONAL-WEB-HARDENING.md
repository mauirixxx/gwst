# Optional Web Server and Cloudflare Hardening

GWTTT includes application-level security controls and does **not** require Apache-specific hardening or Cloudflare to operate. The measures in this document are optional defense-in-depth for administrators who deploy GWTTT on a public web server.

All hostnames and paths below are examples. Adapt them to your environment.

## 1. Apache application-directory hardening

The following example assumes GWTTT is installed at `/var/www/example.com/gwttt` and served over HTTPS.

```apache
<Directory /var/www/example.com/gwttt>
    # Harden PHP sessions.
    php_value session.cookie_secure 1
    php_value session.cookie_httponly 1
    php_value session.cookie_samesite Lax
    php_value session.use_strict_mode 1
    php_value session.use_only_cookies 1

    # Never allow direct web access to database configuration files.
    <FilesMatch "^connect\.php(?:-sample|-testing)?$">
        Require all denied
    </FilesMatch>

    # Repository/documentation files are not part of the public application.
    <FilesMatch "^(README(?:\..*)?|LICENSE|composer\.(?:json|lock)|\.gitattributes|\.gitignore)$">
        Require all denied
    </FilesMatch>

    # Never expose common environment, backup, dump, editor, or log files.
    <FilesMatch "(^\.env(?:\..*)?$|~$|\.(?:bak|old|orig|save|swp|sql|log)$)">
        Require all denied
    </FilesMatch>

    # Browser security headers.
    <IfModule mod_headers.c>
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "DENY"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
        Header always set Strict-Transport-Security "max-age=86400"
    </IfModule>
</Directory>

# Never expose the Git repository.
<Directory /var/www/example.com/gwttt/.git>
    Require all denied
</Directory>

# Documentation is repository content, not part of the public application.
<Directory /var/www/example.com/gwttt/docs>
    Require all denied
</Directory>

# SQL initialization files are deployment files, not web content.
<Directory /var/www/example.com/gwttt/sql-init>
    Require all denied
</Directory>

# Scheduled/maintenance scripts are intended for CLI execution only.
<Directory /var/www/example.com/gwttt/scripts>
    Require all denied
</Directory>

# Composer dependencies are server-side application code.
<Directory /var/www/example.com/gwttt/vendor>
    Require all denied
</Directory>
```

Do **not** blanket-deny the `includes` directory. GWTTT contains browser-facing POST handlers there, including the preferred-account and preferred-character handlers. Those endpoints are protected at the application layer with authentication/CSRF/validation controls as appropriate.

After changing Apache configuration, validate it before reloading:

```bash
apachectl configtest
systemctl reload apache2
```

`apachectl configtest` should report `Syntax OK` before Apache is reloaded.

### HSTS note

The example uses a conservative one-day HSTS lifetime (`max-age=86400`). Increase this only after confirming that HTTPS is reliable for the affected hostname(s). HSTS is remembered by browsers, so long durations should be deployed deliberately.

## 2. Cloudflare is optional

Cloudflare can provide an additional reverse-proxy/WAF layer, but GWTTT does not depend on Cloudflare and should not contain Cloudflare-specific trust logic in application code.

When Cloudflare proxies the site, the TCP peer seen by Apache is normally a Cloudflare edge server. GWTTT's login and registration throttling uses PHP's `REMOTE_ADDR`, so Apache should restore the real visitor address before PHP runs.

### Apache `mod_remoteip`

Enable Apache's `remoteip` module and configure it to accept `CF-Connecting-IP` **only from trusted Cloudflare proxy networks**.

Conceptually:

```apache
RemoteIPHeader CF-Connecting-IP

# Add Cloudflare's currently published IPv4 and IPv6 proxy networks here.
RemoteIPTrustedProxy <cloudflare-network-1>
RemoteIPTrustedProxy <cloudflare-network-2>
# ...
```

Do not copy a static list of Cloudflare networks from this document. Obtain the current network list from Cloudflare when configuring the server and keep it current.

The important trust boundary is:

```text
Visitor -> Cloudflare -> Apache mod_remoteip -> PHP REMOTE_ADDR -> GWTTT throttling
```

GWTTT itself should **not** blindly read or trust a client-supplied `CF-Connecting-IP`, `X-Forwarded-For`, or similar header. Without a trusted-proxy boundary, a direct client could spoof those headers and undermine IP-based throttling or logging.

After enabling `mod_remoteip`, verify that Apache/PHP observes individual visitor addresses as `REMOTE_ADDR` rather than a shared Cloudflare edge address.

## 3. Optional Cloudflare defense-in-depth

Administrators using Cloudflare may additionally enable appropriate protections for the application, such as managed WAF rules, bot protections, and rate limiting for authentication/registration paths. These should complement—not replace—GWTTT's own application-level controls.

Avoid making Cloudflare rules so aggressive that normal login, registration, password-reset, or administrator workflows are unintentionally blocked. Test rules in the actual deployment environment before relying on them.

## 4. What remains application security

Web-server or CDN restrictions are not substitutes for application authorization. Browser-accessible GWTTT handlers still need their normal controls, including authentication, authorization/ownership checks, CSRF protection for state-changing browser requests, input validation, prepared database operations, and safe output encoding.

In particular, the `includes` directory contains application handlers and must not be assumed to be private merely because of its name.

## 5. Suggested verification

After applying optional Apache hardening, verify that sensitive resources are denied while normal application resources remain reachable. Examples of resources that should normally return HTTP 403 include:

```text
connect.php
connect.php-sample
connect.php-testing
README.md
LICENSE
composer.json
composer.lock
.git/config
docs/INSTALL.md
docs/OPTIONAL-WEB-HARDENING.md
sql-init/gwttt-initialization.sql
scripts/send-reminders.php
vendor/autoload.php
```

Normal public pages and assets should remain available. Some handler endpoints may intentionally return redirects or application-specific 4xx responses when requested directly without the expected method, session, CSRF token, or form data; that does not by itself indicate an Apache failure.

For HTTPS responses, verify the configured browser-security headers, for example:

```bash
curl -sSI https://example.com/gwttt/register.php | \
  grep -Ei '^(HTTP/|x-content-type-options:|x-frame-options:|referrer-policy:|strict-transport-security:)'
```

The exact status code depends on the requested endpoint, but the expected security headers should be present on HTTPS responses.
