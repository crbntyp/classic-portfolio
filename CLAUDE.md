# portfolio — crbntyp.com

## The live homepage is not the file you would expect

`behance-preview/index.html` **is** the page served at `https://crbntyp.com/`.
Verified byte-identical to the live root (md5 match), 3 Sep. It is not a
preview and not a draft, despite the folder name.

`https://crbntyp.com/portfolio/` is a *different, older* page. Editing it does
nothing to the homepage.

**Two deploy destinations, and they are not the same tree:**

| Local | Remote |
|---|---|
| `behance-preview/` | `/var/www/crbntyp/behance-preview/` → served at `/` |
| `api/` | `/var/www/crbntyp/portfolio/api/` |

The homepage calls `/portfolio/api/{site,behance,blog,work}.php` — the API
lives under `portfolio/`, the page it serves under `behance-preview/`. Get this
backwards and the site half-works.

**Because source path and served path differ, "the file on disk is correct"
says nothing about what is live.** Always `curl https://crbntyp.com/`.

## Deploy — manual only

No workflow. `_tools/deploy/deploy.sh portfolio` targets `/portfolio/`, which
is the API tree, **not** the homepage. Deploying the homepage means rsyncing
`behance-preview/` to its own path. Confirm which one you mean first.

## Branch and build

Branch `master`. `npm run build` is `vite build`, but the live homepage is a
hand-authored file Vite does not produce — `dist/index.html` differs from live
and is not what ships.

## Landmines

- Behance scrapes paginate at 12, so a scrape only ever sees page one.
  `markMissing` must never hide a project on a partial list — that has already
  lost two real projects.
- A mature-flagged Behance project returns everything except its modules; only
  Behance's own setting can fix it. An unhandled module type is dropped
  silently and the project looks empty (`MediaCollectionModule` = image grids).
- This file is tracked. Never put credentials in it.
