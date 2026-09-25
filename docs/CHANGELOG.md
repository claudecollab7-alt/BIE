# BIE - Change Log

Running log of code and database changes. Newest entries at the top.
The same list is kept in `docs/BIE_Change_Log.xlsx` (tabs: **File Changes**, **Table Changes**).

Comment convention in code: short single-line `//` notes only. Each changed file carries a
dated one-liner near the top, just above the commented-out `ini_set` lines. Details live here.

---

## 2026-09-25 - Cash denominations: 2000 retired, 1 / 2 / 5 added

The denomination list is data, not code. **Two tables hold the same list** and both were
updated so the screens stay in step:

| Table | Used by |
|---|---|
| `tbl_cash_details` | Sales Receipt (`pay_receipt.php`) |
| `mst_cash_details` | Invoice (`mng_invoice.php`, `quo_invoice.php`), Credit (`mng_credit.php`) |

**Data** - run `db/cash_denomination_update.sql` once (safe to re-run).

- 2000 set to `cash_status = 0` in both tables.
- 1.00, 2.00, 5.00 added as `cash_id` 8, 9, 10 in both tables.
- `db/bie.sql` updated to match, so a fresh install gets the same list.

**Code** - the dropdown was `ORDER BY cash_id`, so the new rows (ids 8-10) would have
appeared *below* 500. Changed to `ORDER BY cash_name` in `pay_receipt.php`,
`mng_invoice.php`, `quo_invoice.php` and `mng_credit.php`, giving
1, 2, 5, 10, 20, 50, 100, 200, 500.

**Points to know**

- 2000 is deactivated, **not deleted**. Old receipts store `cash_id` 7, and the screens that
  print them (`jquery_modal_cash_dts.php`, `jquery_modal_cash_invoice.php`,
  `modal_so_cash_details.php`, `fancybox_so_cash_details.php`) look the name up **without**
  checking `cash_status`, so history still shows 2000 correctly. Deleting the row would
  blank those out.
- `jquery_cash_receipt_details.php` does filter on `cash_status = '1'` when adding a row,
  which is correct - a retired denomination cannot be entered on a new receipt.
- `dc_invoice.php` saves denomination rows but has no dropdown of its own, so nothing to change there.

---

## 2026-09-24 - CSRF tokens, duplicate-submit guard, transaction rollback

### What changed

Every main document form now carries a CSRF token and a single-use form token, and every
POST handler runs inside a database transaction that rolls back on error.

**New files**

| File | Purpose |
|---|---|
| `inc/common/csrf.php` | CSRF token + single-use form tokens. `csrf_fields()`, `csrf_check()`, `csrf_fail()`, `csrf_check_ajax()`, `csrf_meta()` |
| `inc/common/db_txn.php` | `db_begin()`, `db_commit()`, `db_rollback()` - each a no-op if there is nothing to do, so nesting is safe |

**Forms covered** (csrf guard on every POST handler, hidden token fields in the form,
`db_begin` / `db_commit` / `db_rollback` around every try block)

| File | Handlers | Form token name |
|---|---|---|
| `gen_so.php` | SAVE, UPDATE, ACCOUNTS | `gen_so` |
| `quotation.php` | SAVE, UPDATE, FINALIZE | `quotation` |
| `quo_proforma.php` | SAVE | `quo_proforma` |
| `quo_invoice.php` | SAVE, UPDATE, FINALIZE | `quo_invoice` |
| `dc_add.php` | SAVE, UPDATE, FINALIZE | `dc_add` |
| `dc_invoice.php` | SAVE, UPDATE, FINALIZE | `dc_invoice` |
| `mng_invoice.php` | Draft, UPDATE, FINALIZE | `mng_invoice` |
| `po_prepare.php` | SAVE, UPDATE, send_to_admin | `po_prepare` |
| `direct_purchase_order.php` | Draft, UPDATE, FINALIZE | `direct_po` |
| `grn_add.php` | UPDATE, FINALIZE | `grn_add` |
| `store_indent_add.php` | SAVE, UPDATE, FINALIZE | `store_indent` |
| `repair_indent_add.php` | SAVE, UPDATE | `repair_indent` |
| `purchase_return_add.php` | SAVE, UPDATE, FINALIZE | `purchase_return` |
| `mst_item_details.php` | SAVE, UPDATE | `mst_item_details` |
| `mst_itemprice_history.php` | SAVE, SAVE1, UPDATE | `itemprice_history` |
| `stock_adjustment.php` | SAVE | `stock_adjustment` |

**Also changed**

- `inc/common/userclass.php` - loads `stock_flow.php`, `csrf.php`, `db_txn.php`.
- `inc/common/css-js.php` - renders the `csrf-token` meta tag and an `$.ajaxSetup` that
  attaches the token to every ajax POST, so existing scripts need no edit.
- `inc/cis_ajax/jquery_store_location_dets.php` - login + csrf check added. It had **no**
  login check at all before.

### Points to know

- **`quo_invoice.php`, `dc_invoice.php`, `gen_so.php`, `quo_proforma.php` had no try/catch
  at all.** In `quo_invoice.php` the `try {` was commented out. Ten handlers across those
  four files were running bare; try/catch was added along with the transaction.
- **`dbhandler` opens its own database connection**, separate from `$conn`. A `$dbconn`
  read inside a `$conn` transaction does not see uncommitted rows. The stock loops in
  `grn_add.php`, `mng_invoice.php`, `quo_invoice.php` and `dc_invoice.php` used to read
  stock through `$dbconn` and write through `$conn`; with a transaction that would have
  computed the wrong balance for a document listing the same item twice. Those loops now
  call `fnApplyStockMovement()`, which does everything on `$conn`. `$dbconn` is still used
  for master-data lookups (prices, ref codes), which is safe.
- **`db_commit()` placement.** In `direct_purchase_order.php` and `store_indent_add.php`
  the success path ends with `header(...); die();` *inside* the try. The commit has to come
  before that `die()`, otherwise PDO rolls the transaction back when the script ends and the
  whole document is lost.
- **Form tokens are single use.** Re-posting a form (back button, double click, refresh)
  is rejected with "This form was already submitted or your session expired."
- **`FORM_TOKEN_KEEP`** (`inc/common/csrf.php`) holds the newest 5 tokens per form name, so
  the same form works in several browser tabs. Set it to 1 for strict single-form behaviour.
- Not covered: `index.php` (login) and the other master/list forms.

---

## 2026-09-23 - Item Stock List + stock flow modal

**New files**

| File | Purpose |
|---|---|
| `lst_item_stock.php` | Item Masters > Item Stock List. Item stock by branch, server-side DataTable |
| `inc/datatable/ajaxItemStockList.php` | Rows for the above. One stock column per active branch |
| `modal_item_stock_flow.php` | Modal shell |
| `inc/cis_ajax/jquery_modal_item_stock_flow.php` | Modal body - last 10 movements for one item at one branch |

**Changed**

- `rpt_item_stock_history_branch_wise.php` - runs from `$_REQUEST` instead of `$_POST` so the
  modal's **View All** link can open it already filtered. Its query took `item_id` and
  `branch_id` straight into the SQL string; now bound and cast, since the page is reachable
  by GET.

**Points to know**

- Branch columns are read from `mst_branch.branch_stock_field`, so a new branch appears on
  its own. `rpt_all_store_stock_list.php` hardcodes `ho_stock` and `kl_stock` and prints a
  header for Rajapalayam with no data under it - that report was left as it is.
- Sorting is restricted to a known column map, because the sort column arrives from the browser.

---

## 2026-09-22 - Stock ledger + Stock Adjustment

Every change to `tbl_item_stock` now writes a matching `tbl_stock_flow` row.

**New files**

| File | Purpose |
|---|---|
| `inc/common/stock_flow.php` | `fnApplyStockMovement()` and helpers. The only place stock qty is changed |
| `stock_adjustment.php` | Stores > Stock Adjustment. Manual increase/decrease with a mandatory reason |
| `inc/cis_ajax/jquery_get_item_stock.php` | Current branch stock for one item |
| `db/stock_flow_upgrade.sql` | Database migration (see Table Changes below) |

**Changed**

| File | Change |
|---|---|
| `grn_add.php` | Stock block replaced with `fnApplyStockMovement()` |
| `mng_invoice.php` | Same |
| `quo_invoice.php` | Same |
| `dc_invoice.php` | Same |
| `mst_itemprice_history.php` | Current Stock box made read only; the price save no longer writes the qty column |
| `mst_item_details.php` | New item's stock row created through `fnEnsureItemStockRow()` instead of raw SQL |
| `update_stock.php`, `update_stock copy.php` | Non-zero opening stock logged to the ledger |
| `inc/cis_ajax/jquery_store_location_dets.php` | Column names validated; cannot touch a qty column |
| `rpt-item-stock-history.php` | Reason column, ADJ rows, signed quantities, per-row variable reset |
| `rpt_item_history.php` | Same. Its ADJ branch was a stub against an `hk_*` schema that does not exist here |
| `rpt_item_stock_history_branch_wise.php` | Same |

**Points to know**

- Balances move with a relative update (`col = col +/- :delta`), not read-then-overwrite,
  so two saves cannot lose each other.
- The branch column name is put into the SQL string, so it is validated against `mst_branch`
  before use.
- `mst_itemprice_history.php` compared the posted stock qty against
  `mst_branch.branch_stock_field` - a column name, not a quantity - so that condition was
  always true. Fixed.
- `tbl_item_details.item_curr_stock` is dead; every write to it was already commented out.
- `inc/common/table_class.php` `update_stock_new()` was left alone: the file is not included
  anywhere and the function sits inside a block comment spanning lines 28-358.
- Not covered: purchase return, store indent and repair indent still move goods without
  touching stock or the ledger; cancelling a finalised invoice or GRN does not reverse the qty.

---

## Database changes

Run `db/stock_flow_upgrade.sql` once. Sections 1 and 4 are listed below.

### `tbl_stock_flow` (altered)

| Column | Change | Notes |
|---|---|---|
| `trans_dir` | added `CHAR(1)` | `I` = in (+), `O` = out (-). Backfilled: GRN = I, INV/SALE = O |
| `trans_remarks` | added `VARCHAR(255)` | Reason / narration. Mandatory for ADJ rows |
| `stock_field` | added `VARCHAR(30)` | Which `tbl_item_stock` column was changed. Backfilled from `mst_branch` |
| `before_qty` | `INT` to `DECIMAL(12,3)` | Was truncating decimal stock |
| `trans_qty` | `INT` to `DECIMAL(12,3)` | Always a positive magnitude, see `trans_dir` |
| `rcvd_qty` | `INT` to `DECIMAL(12,3)` | |
| `after_qty` | `INT` to `DECIMAL(12,3)` | |
| `reje_qty` | `INT` to `DECIMAL(12,3)` | |
| `pend_qty` | `INT` to `DECIMAL(12,3)` | |
| `trans_type` | `VARCHAR(8)` to `VARCHAR(10)` | GRN, INV, ADJ, OPEN, RET |
| `idx_sf_item_branch_date` | index added | `(item_id, branch_id, trans_date)` |
| `idx_sf_trans` | index added | `(trans_type, trans_id)` |

### `tbl_stock_adjustment` (new)

`adj_id`, `adj_refno`, `adj_slno`, `adj_finyr`, `adj_date`, `branch_id`, `stock_field`,
`item_id`, `adj_type` (`I`/`D`), `adj_qty`, `before_qty`, `after_qty`, `item_price`,
`adj_reason`, `adj_status`, `created_by`, `created_dtm`.

### `mst_sub_menu` / `tbl_user_rights` (rows added)

| sm_id | mm_id | Name | URL |
|---|---|---|---|
| 147 | 20 (Stores) | Stock Adjustment | `stock_adjustment.php` |
| 148 | 3 (Item Masters) | Item Stock List | `lst_item_stock.php` |

Rights rows are granted to users who already have the parent menu.
