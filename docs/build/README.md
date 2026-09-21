# BIE ERP documentation generator

Builds the two deliverables in `docs/`:

| File | What it is |
|---|---|
| `../BIE_ERP_Documentation.docx` | The Word walkthrough, menu by menu |
| `../BIE_ERP_Documentation.xlsx` | The workbook — one row per screen, one sheet per menu |

## Build

```
cd docs/build
python3 build_doc.py                                     # writes content.json
node   render_docx.js content.json ../BIE_ERP_Documentation.docx
python3 build_xlsx.py                                    # writes the workbook
```

`node_modules` holds the `docx` package. Re-install with `npm install docx` if missing.

## Files

| File | Role |
|---|---|
| `content_common.py` | Block helpers and the three section templates (`form`, `form2`, `mini`) |
| `part0.py` | Part 0 — the patterns every screen follows, including branch columns |
| `part1.py` … `part3.py` | One file per menu, each returning `{"heading", "blocks"}` |
| `build_doc.py` | Assembles the parts into `content.json` |
| `render_docx.js` | Renders that JSON to `.docx` (docx-js) |
| `xlsx_data_p1.py` | The workbook's Phase 1 row data |
| `build_xlsx.py` | Builds the workbook (openpyxl) |
| `menu.json` | The live menu tree, extracted from `mst_main_menu` / `mst_sub_menu` |
| `schema.py` | Reads `db/bie.sql` — table and column lookups |
| `analyze.py` | Helper for scanning a screen's SQL |

## Adding a menu

1. Write `partN.py` with a `chapter()` returning `{"heading": "Part N", "blocks": [...]}`.
2. Import it in `build_doc.py` and add `partN.chapter()` to `chapters`.
3. Add the row data to a `xlsx_data_pN.py` and a `menu_sheet(...)` call in `build_xlsx.py`.
4. Flip that menu's row to `Done` in the `COV` table — the totals are formulas.

## Scope

Live menu-reachable files only: 63 screens across 11 menus. Schema source is `db/bie.sql`.
