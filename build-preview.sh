#!/bin/bash
# Build the static clickable preview that lives at
# https://anirudhatalmale6-alt.github.io/louies-preview/
#
# Not wget. wget --convert-links rewrites absolute URLs to PAGE-RELATIVE ones,
# and a url() inside a CSS custom property resolves against the STYLESHEET, not
# the page - so the hero image silently disappeared while every request still
# returned 200. And --reject-regex '\?' drops every ?ver= asset, which quietly
# removed all the CSS and JS. Fetching each page and doing one explicit rewrite
# to an absolute /louies-preview/ path avoids both.
set -euo pipefail

SRC="${SRC:-http://localhost:8134}"
SITE="${SITE:-/var/lib/freelancer/projects/40687911/site}"
OUT="${1:?usage: build-preview.sh <output-dir>}"
PREFIX="/louies-preview"

PAGES=( "" "events/" "menu/" "about/" "contact/" "gallery/" "private-events/" "privacy-policy/" )

rm -rf "$OUT"
mkdir -p "$OUT"
touch "$OUT/.nojekyll"

# Assets straight off disk - no query strings, no guessing.
mkdir -p "$OUT/wp-content/themes/louies"
cp -r "$SITE/wp-content/uploads" "$OUT/wp-content/uploads"
cp -r "$SITE/wp-content/themes/louies/assets" "$OUT/wp-content/themes/louies/assets"

# Every event that is actually published, so the "Details" links work.
while read -r slug; do
	[ -n "$slug" ] && PAGES+=( "event/$slug/" )
done < <(cd "$SITE" && "${WP:-wp}" post list --post_type=louies_event --post_status=publish --field=post_name 2>/dev/null || true)

rewrite() {
	python3 - "$1" "$SRC" "$PREFIX" <<'PY'
import re, sys
path, src, prefix = sys.argv[1], sys.argv[2], sys.argv[3]
html = open(path, encoding='utf-8').read()

# Absolute site URLs -> absolute preview URLs. Absolute, never relative: a
# relative asset path inside a CSS custom property resolves against the
# stylesheet and breaks in a way that still returns HTTP 200.
html = html.replace(src + '/', prefix + '/').replace(src, prefix + '/')

# Cache-buster query strings would become part of the filename on a static host.
html = re.sub(r'(\.(?:css|js|jpg|jpeg|png|svg|webp|ico))\?[^"\'\s>]*', r'\1', html)

# WordPress advertises its REST and oEmbed endpoints in <head>. There is no PHP
# here to answer them, so they are four guaranteed 404s in the browser console
# on every page. Drop the tags rather than ship known-broken links.
html = re.sub(r'[ \t]*<link[^>]*(?:wp-json|api\.w\.org|oembed)[^>]*>\n?', '', html)
html = re.sub(r'[ \t]*<link rel="EditURI"[^>]*>\n?', '', html)

# admin-post.php cannot run here. Say so on the page rather than letting a
# visitor type a message into a form that silently goes nowhere.
if 'admin-post.php' in html:
    html = html.replace('action="' + prefix + '/wp-admin/admin-post.php"', 'action="#" onsubmit="return false"')
    html = re.sub(r'(<form[^>]*class="[^"]*louies-form[^"]*"[^>]*>)',
                  r'\1<p class="form-note" style="background:#f0e49c;color:#241018;padding:.8rem 1rem;'
                  r'border-radius:8px;font-weight:600;margin:0 0 1rem">This is a static preview, so this '
                  r'form is switched off. On the live site it emails louiescocktails@gmail.com.</p>',
                  html, count=1)

open(path, 'w', encoding='utf-8').write(html)
PY
}

for p in "${PAGES[@]}"; do
	dir="$OUT/$p"
	mkdir -p "$dir"
	code=$(curl -sL -o "$dir/index.html" -w '%{http_code}' "$SRC/$p")
	if [ "$code" != "200" ]; then
		echo "FAILED $code  /$p" >&2
		exit 1
	fi
	rewrite "$dir/index.html"
	echo "  ok $code  /$p"
done

echo "built $(find "$OUT" -name index.html | wc -l) pages into $OUT"
