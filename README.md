# Louie's Cocktail Lounge — WordPress site

For **louiescocktails.com**. Mobile-first, tap-to-call, Google Maps, local SEO,
and recurring events that you create **once**.

Four pieces:

| | |
|---|---|
| `wp-content/themes/louies` | The look. Templates, CSS, the one small JS file. |
| `wp-content/plugins/louies-core` | The content. Events, recurrence, the food & drink menu, the bar's details, and the SEO. |
| `photos/` | Photographs of the bar, cleaned up and captioned. Two are the bar's own — the building at dusk and the neon sign at night with customers outside. The rest were recovered from the Wayback Machine: the karaoke stage, tables with the game on, the back bar, the pool room, and crowd shots. |
| `brand/` | The logo converted out of the supplied CMYK `.eps` into web-ready RGB — an SVG that stays sharp at any size, PNGs at three widths, a 1200×630 share card and a 512px icon. All transparent except the share card. |

Content lives in the plugin on purpose. If the site is ever restyled, every
event and every price survives it.

## Colours

Sampled from the badge logo rather than invented, so the site and the sign
match:

| | | |
|---|---|---|
| Plum | `#241018` | Dark bands, footer, headings |
| Teal | `#2f9d92` | Primary buttons, links, "open now" |
| Coral | `#ef8b2c` | Prices, date chips, the call button |
| Sunset cream | `#f0e49c` | Highlights on dark |
| Sand | `#fdf7ea` | Page background |

The page is **warm and light by default**; plum is an accent band, never the
whole site.

---

## Recurring events without a licence fee

The old site used The Events Calendar. Repeating events on that plugin need the
**Pro** version, which is a recurring annual cost. This replaces it with exactly
what a bar needs and nothing more:

* **Every week** — tick the days. "Karaoke, Wed/Thu/Fri/Sat, 9pm" is one event, forever.
* **Once a month** — "the last Saturday of every month".
* **Just once** — a band, a party, a fight night.
* **Stop repeating on** a date, or leave blank and it runs indefinitely.
* **Skip these dates** — cancel Christmas Day without deleting the series.

Dates are worked out when the page loads. Nothing is pre-generated, so there is
no "regenerate recurring events" button to remember and nothing to go stale.

### Proving it

`tests/recurrence-tests.php` is 24 assertions over the repeat logic. From the
WordPress root:

```
wp eval-file tests/recurrence-tests.php
```

It covers weekly, weekly-with-an-end-date, skipped dates, last-Saturday-of-the-
month, a "5th Sunday" in a month that hasn't got one, one-offs, and that an
open-ended weekly event actually terminates rather than looping forever.

It also covers **Sunday specifically**, because Sunday is weekday `0` and the
first version of the parser used `array_filter()`, which throws away every falsy
value — so a Sunday event produced no dates, never appeared in the weekly grid,
and showed its own checkbox unticked in the editor. `louies_weekday_list()`
exists to make that impossible to write again.

---

## Adding a night

**Events → Add Event.** Title, date, time, price. If it repeats, tick *Every
week* and choose the days. Save. That's the whole job.

The Events list shows *Next: Fri, Sep 4* for repeating nights rather than the
date the series began — so the list reads like a diary, not a database.

**"Next up" on the home page shows only the one-offs and the monthly nights** —
the bands, the fights, the parties, Geo Jam. The weekly regulars are already in
the grid directly above it, and repeating them underneath just pushes the one
date that is actually news off the screen. If nothing is booked, the section
says so rather than padding itself out with karaoke.

## The karaoke band at the top of the home page

Karaoke leads the page, above everything else, because it is what Louie's is
best known for and it was previously reaching people as one word in the hero
tag list.

**Nothing in that band is typed into the template.** The nights, the times and
the links are all read from the karaoke events themselves — the four
*Wednesday/Thursday/Friday/Saturday Night Karaoke* posts, the ones with the
**Karaoke** event type and *Every week* ticked. So:

* Add a fifth karaoke night in **Events** and a fifth chip appears.
* Move karaoke to 8:30pm and the band says 8:30pm.
* Unpublish all four and the band disappears entirely, rather than promising
  karaoke the bar has stopped running.

The one thing that is written by hand is the paragraph ("Some of the best KJs
in Sacramento…"), which is copy, not data. It lives in
`wp-content/themes/louies/parts/karaoke.php`.

**Swap the photo** in **Bar Details → Karaoke photo (media ID)**. Upload a new
picture to Media, open it, copy the number out of the URL, paste it in. A
landscape shot of someone on the stage works best.

### The line on the photo

It reads one of three ways, and it changes on its own:

* **"Karaoke is on right now — the mic is open"**, lit teal, while it is
  actually running.
* **"Karaoke tonight from 9:00 pm"** on a karaoke day before it starts.
* **"Next karaoke: Wednesday from 9:00 pm"** the rest of the week.

Tonight's chip is highlighted too, so the day is readable at a glance.

Two things this gets right that are easy to get wrong. Karaoke finishes at
**1:30am**, so at 1am on a Saturday the singing that is happening belongs to
*Friday* — the band says "on now", not "tonight from 9". And it is worked out
**in the browser, in Rancho Cordova's timezone**, for exactly the reason the
open/closed light is (see below): a cached page would otherwise sit there
saying "Karaoke tonight" on a Monday.

## Changing the menu

**Food & Drink.** Each item is a title plus a price. Group them with **Menu
Sections** (Nightly Drink Specials, Pub Food, Beer On Tap, Whiskey & Bourbon…).
Use the *Order* field to move an item up or down within its section.

A section where no item has a price renders as a tidy multi-column bottle list
instead of a price list — that's how the back-bar spirits display.

## The league logos on the game-day section

Each league on the "On the screens" row shows **an uploaded logo if Bar Details
has a media ID for it, and a typeset badge in the bar's own colours if not**.
Nothing is hard-coded into the theme.

As shipped: NFL, NBA, MLB and NHL use logo files supplied by the bar on
3 September 2026, cut out of the sheet it sent. NCAA and FIFA are still typeset
badges because no artwork was supplied for them.

**To change one:** upload the image, copy its media ID, paste it into
**Bar Details → NFL logo (media ID)** and so on. **To remove one:** clear that
field and the typeset badge comes back. One field, reversible, no code.

On the trademark question, which hasn't changed: these are registered marks.
Naming a league — "we show NFL games" — is ordinary descriptive use and is
fine. Reproducing the artwork is a different thing, and whether to do it is the
bar's call, which is exactly why it lives in a settings field rather than in
the theme.

Logos sit on a **white plaque**. Several of these marks are largely white
themselves — the NFL shield and the MLB batter are both white shapes — so
dropping them straight onto the dark band would lose half of each one.

## Phone, address, hours

**Bar Details.** One screen. Change the phone number there and it changes in the
header, the footer, the sticky bar on phones and every tap-to-call link at once.

The **Banner message** field puts a strip across the top of every page — useful
for "Closed Thanksgiving" or "Kitchen closed tonight". Leave it blank to hide it.

---

## What's automatic

* **Open now / Closed** in the header, from the bar's hours — see below.
* **Happy hour is on right now** on the front page, during either window.
* **Tonight at Louie's** — whatever is actually booked for today.
* **The regular line-up** — the seven-day grid builds itself from the weekly
  events, with tonight highlighted. It cannot drift out of sync with the
  calendar because it *is* the calendar.
* **The karaoke band** — nights, times, links and the "on now / tonight / next"
  line, all off the karaoke events.

### Why the open/closed light is worked out in the browser

It would be simpler to decide "open or closed" on the server while building the
page. That is what it used to do, and it was **wrong** — the bar reported the
site saying *Closed* on an evening it was open.

The reason is that a built page gets **kept**. Hosts put a page cache in front
of WordPress, CDNs hold copies, and the static preview is flat files with no PHP
at all. Whatever the light said at the moment the page was generated is what
every later visitor sees. A page built at 3am says "Closed" until something
clears the cache.

So the decision moved into `assets/js/main.js`, which re-checks it on load and
every minute after. Two consequences worth knowing:

* It is **pinned to the bar's timezone** (`America/Los_Angeles`), not the
  viewer's. Someone looking the bar up from another state sees whether *Louie's*
  is open, not whether it would be open where they are standing.
* The happy-hour flag is **always in the HTML** and switched on with a class,
  rather than only being printed when it applies. A conditional render cannot be
  corrected after a cache has frozen it: if the element isn't there, there is
  nothing for the browser to switch on.

PHP still renders a first guess, because that is the correct fallback with
JavaScript off and it is what search engines read. The hours live in one place
(`louies_open_time()` / `louies_close_time()`) and are handed to the browser as
data attributes, so the two can't disagree.

The trading window **wraps past midnight** — 6am to 2am — so the obvious test
("after opening AND before closing") is false for every hour the bar is
actually busy. Both implementations handle the wrap, and the test suite pins it.

---

## Installing

1. Copy `wp-content/themes/louies` and `wp-content/plugins/louies-core` into the
   WordPress install.
2. **Plugins → Louie's Core → Activate.** Do this before the theme.
3. **Appearance → Themes → Louie's Cocktail Lounge → Activate.**
4. **Settings → Permalinks → Save** once, so event URLs work.
5. **Settings → General**: set the timezone to *America/Los_Angeles*.
6. Create the pages and assign templates:

   | Page | Slug | Template |
   |---|---|---|
   | Home | `home` | *(default — the theme uses `front-page.php`)* |
   | What's On | `events` | Events Calendar |
   | Food & Drink | `menu` | Food & Drink Menu |
   | About Us | `about` | *(default)* |
   | Contact & Venue Hire | `contact` | Contact |
   | Photos | `gallery` | Photo Gallery |
   | Book a Private Event | `private-events` | Private Events |

   **Settings → Reading**: front page displays a static page → *Home*.
7. **Appearance → Menus**: build the Primary and Footer menus.
8. **Appearance → Customize → Site Identity**: upload the logo.

### Or seed it in one command

With [WP-CLI](https://wp-cli.org) on the server, from the WordPress root:

```
wp plugin activate louies-core
wp theme activate louies
wp eval-file seed.php
```

That creates every page, assigns the templates, sets the front page, timezone and
permalinks, and loads the events, drink specials, food menu and spirits list
recovered from the archived site. It is safe to run twice — everything is matched
on slug and updated in place rather than duplicated.

---

## SEO

No Yoast, no subscription. `includes/seo.php` does the parts that matter for a
bar and stops there:

* **Titles and descriptions** per page, written for a search result rather than
  "Home | Site". Descriptions are trimmed to 160 characters on a word boundary.
* **`BarOrPub` structured data** — name, address, phone, price range, opening
  hours (including the 6am–2am overnight wrap) and amenities: karaoke, live
  music, pool, darts, patio, free parking, WiFi, ATM.
* **`Event` structured data** for every upcoming occurrence, so Google can list
  what's on directly in the results. Overnight finishes get the following day's
  date on `endDate`, which is what the spec requires.
* **Open Graph and Twitter cards**, so a link posted to Facebook shows the logo
  card instead of a blank box.
* **Canonical URLs** on every page.
* **Sitemap** at `/wp-sitemap.xml`, linked from `robots.txt`. Menu items, menu
  sections and author archives are excluded — they'd only dilute the pages that
  matter.
* **Finished events go `noindex, follow`** automatically, so the site doesn't
  fill up with dead pages competing against the live ones.

### After it goes live

1. Add the site to [Google Search Console](https://search.google.com/search-console)
   and submit `https://louiescocktails.com/wp-sitemap.xml`.
2. Claim the **Google Business Profile** for the bar. For a venue this is worth
   more than everything above put together — it is what fills the map pack when
   somebody searches "karaoke near me". Make the name, address, phone and hours
   *character-for-character identical* to the site.
3. Put the site link on the Facebook and Instagram profiles, then paste those
   URLs into **Bar Details** so they go into the structured data as `sameAs`.

## The contact forms

Two of them, both built into the theme. No plugin, no third-party service, no
data leaving the site. Each has a honeypot field, a nonce, and server-side
validation.

* **Contact** — name, email, phone, subject, message.
* **Private Events** — adds the date wanted, rough headcount and the occasion.
  Those three extra fields are appended to the email body, so nothing a customer
  types is silently dropped.

It sends with `wp_mail()`. On most shared hosting that works out of the box; if
messages don't arrive, the host is blocking PHP mail and you'll want an SMTP
plugin (WP Mail SMTP, free) pointed at the bar's mailbox. Where it sends is set
in **Bar Details → Contact email**.

---

## Hosting

WordPress.com's cheaper plans — Free, Personal, Premium — **cannot run this**.
They only allow themes and plugins from their own catalogue. Custom code needs
their **Business** plan (about $300/year billed annually), which is also where
their SFTP / SSH / WP-CLI / GitHub Deployments bundle appears. That list isn't
a requirement in itself; it's just the marker of the tier that can run custom
code at all. Of it, only **SFTP** actually matters — that's how the theme and
plugin get installed.

Everything here is a normal WordPress theme and plugin, so any host that gives
you real WordPress will do — a $5–12/month plan runs it comfortably, at about a
fifth of the price. Look for: cPanel or similar, SFTP or file-manager access,
PHP 8, MySQL, free SSL.

**Storage:** the whole site — WordPress, this theme, the plugin, and the images
— is well under 200 MB. 13 GB is far more than enough; a few hundred
full-resolution event posters would still leave most of it free.

### Hostinger, if that's the one

Prices as advertised on 4 September 2026 — check them at the till, they move.

| Plan | Intro | Renews at | Storage | Sites |
|---|---|---|---|---|
| Premium | $2.99/mo | **$10.99/mo** | 20 GB | 3 |
| Unlimited | $3.99/mo | $16.99/mo | 50 GB | unlimited |
| Cloud Startup | $7.99/mo | $25.99/mo | 100 GB | unlimited |

**Premium is the one.** The only real difference at the Unlimited tier is the
number of *websites*, and Louie's has one. 20 GB against a site under 200 MB is
already about a hundred times more room than it needs.

Two things worth knowing before buying:

* **The intro price is the 48-month price.** Shorter terms cost more per month.
  Every host prices this way; it isn't a Hostinger quirk.
* **The renewal is the real price.** Budget $10.99/mo, not $2.99/mo. Nothing is
  locked in either — at renewal the whole site moves to another host in an
  afternoon, because it is a normal theme and plugin with no licence attached.

The free domain for the first year is worth taking **if `louiescocktails.com`
isn't registered yet**. If it already is somewhere else, leave it there and
just point the DNS.

## Notes

* No page builder. Pages load as plain HTML and CSS, one small JS file, no
  jQuery — which is most of why it's quick on a phone in a car park.
* Fonts come from Google Fonts. To remove that external request entirely,
  self-host the three families and drop the `louies-fonts` enqueue in
  `functions.php`.
* The map is a plain Google Maps embed keyed off **Bar Details → Google Maps
  search**. No API key required.
* Prefers-reduced-motion is respected; animations turn themselves off.
