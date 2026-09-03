# Louie's Cocktail Lounge — WordPress site

For **louiescocktails.com**. Mobile-first, tap-to-call, Google Maps, local SEO,
and recurring events that you create **once**.

Three pieces:

| | |
|---|---|
| `wp-content/themes/louies` | The look. Templates, CSS, the one small JS file. |
| `wp-content/plugins/louies-core` | The content. Events, recurrence, the food & drink menu, the bar's details, and the SEO. |
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

---

## Adding a night

**Events → Add Event.** Title, date, time, price. If it repeats, tick *Every
week* and choose the days. Save. That's the whole job.

The Events list shows *Next: Fri, Sep 4* for repeating nights rather than the
date the series began — so the list reads like a diary, not a database.

## Changing the menu

**Food & Drink.** Each item is a title plus a price. Group them with **Menu
Sections** (Nightly Drink Specials, Pub Food, Beer On Tap, Whiskey & Bourbon…).
Use the *Order* field to move an item up or down within its section.

A section where no item has a price renders as a tidy multi-column bottle list
instead of a price list — that's how the back-bar spirits display.

## Phone, address, hours

**Bar Details.** One screen. Change the phone number there and it changes in the
header, the footer, the sticky bar on phones and every tap-to-call link at once.

The **Banner message** field puts a strip across the top of every page — useful
for "Closed Thanksgiving" or "Kitchen closed tonight". Leave it blank to hide it.

---

## What's automatic

* **Open now / Closed** in the header, from the bar's hours.
* **Happy hour is on right now** on the front page, during either window.
* **Tonight at Louie's** — whatever is actually booked for today.
* **The regular line-up** — the seven-day grid builds itself from the weekly
  events, with tonight highlighted. It cannot drift out of sync with the
  calendar because it *is* the calendar.

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

## The contact form

Built into the theme. No plugin, no third-party service, no data leaving the
site. It has a honeypot field, a nonce, and server-side validation.

It sends with `wp_mail()`. On most shared hosting that works out of the box; if
messages don't arrive, the host is blocking PHP mail and you'll want an SMTP
plugin (WP Mail SMTP, free) pointed at the bar's mailbox. Where it sends is set
in **Bar Details → Contact email**.

---

## Hosting

WordPress.com's cheaper plans **cannot run this**. Custom themes and plugins
need their **Business** plan (about $300/year billed annually). Everything here
is a normal WordPress theme and plugin, so any host that gives you real
WordPress will do — a $5–12/month plan from a normal host runs it comfortably.

**Storage:** the whole site — WordPress, this theme, the plugin, and the images
— is well under 200 MB. 13 GB is far more than enough; a few hundred
full-resolution event posters would still leave most of it free.

## Notes

* No page builder. Pages load as plain HTML and CSS, one small JS file, no
  jQuery — which is most of why it's quick on a phone in a car park.
* Fonts come from Google Fonts. To remove that external request entirely,
  self-host the three families and drop the `louies-fonts` enqueue in
  `functions.php`.
* The map is a plain Google Maps embed keyed off **Bar Details → Google Maps
  search**. No API key required.
* Prefers-reduced-motion is respected; animations turn themselves off.
