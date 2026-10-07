# BIE - Change Log

Running log of code and database changes. Newest entries at the top.
The same list is kept in `docs/BIE_Change_Log.xlsx` (tabs: **File Changes**, **Table Changes**).

Comment convention in code: short single-line `//` notes only. Each changed file carries a
dated one-liner near the top, just above the commented-out `ini_set` lines. Details live here.

---

## 2026-10-07 - Item search boxes: suggestions in milliseconds instead of seconds

The type-ahead item boxes on Quotation and Store Indent (and the smaller ones on Repair
Indent and Spare Mapping) waited seconds before showing names.

Measured on the 06-10-26 data, PHP 8: one letter took 8.6 s on Quotation (2,865 items,
476 KB back) and 5 s on Store Indent. Typing "cp-7" at normal speed, the list showed after
8.3 s even though "cp-7" alone took 19 ms. Four causes:

1. Every match was returned, no limit - one letter matches most of the 4,225 active items.
2. Each match ran three to five more queries (stock, price, discounts, unit, GST) - tens of
   thousands of queries for one keystroke.
3. The session stayed open for the whole request, and PHP lets only one request per
   session run at a time, so each keystroke's search queued behind the slow one before it.
4. The typed text went straight into the SQL - a `'` crashed it (`inc/auto/error_log`).

Now (`inc/auto/item_search_lib.php`, used by `select_quotation_items.php` and
`select_store_indent_items.php`): the session is released as soon as the branch is read; one
query returns the best 30 matches - item code starting with the text first, then
description, then the rest - with unit and GST joined; one more query fetches the stock
fields for just those items, first stock row per item as before; the text is bound. The
branch visibility rule is unchanged. `select_repair_indent_items.php` and
`select_spare_mapping_items.php` release the session early too, and spare mapping binds its
text. Item Grouping's search box is commented out and was left alone.

Checked: old against new for 15 search terms on both pages, as a branch 1 and a branch 2
user - 60 comparisons, every suggestion identical field for field, identical lists up to 30
matches, the best 30 above that. One letter now 9 ms; typing "cp-7" the list is ready 9 ms
after the last key. On the real Quotation page, picking a suggestion still fills item, price,
unit and GST.

Correction: the "store indent item search fails, plugin include commented out" item from
earlier was wrong. These pages load jQuery UI from cdnjs, which the test machine could not
reach. On a normal connection the search box works.

---

## 2026-10-07 - Details rows: count() on a single value is fatal on PHP 8

The server runs PHP 8 - the error wording `count(): Argument #1 ($value)` only exists from
8.0 - although the database dumps report 7.4.6.

Every details save counted its posted rows with `count($_REQUEST['x'])`. When a field came in
as a single value instead of `x[]`, PHP 8 stops with "count(): Argument #1 ($value) must be of
type Countable|array, string given"; PHP 7 counted it as 1 row.

New `fnRowCount('x')` in `inc/common/functions.php` returns the number of posted rows, or 0
when the field is missing, empty or a single value. All 109 such counts in 20 files use it:
dc_add, dc_invoice, direct_purchase_order, gen_so, grn_add, grn_pay_receipt, mng_credit,
mng_invoice, mst_customer_new, mst_employee_add, mst_item_grouping, mst_itemprice_history,
pay_receipt, purchase_return_add, quo_invoice, quo_proforma, quotation, repair_indent_add,
spare_mapping, store_indent_add.

Where it came from in `quotation.php` and `dc_invoice.php`: the package dropdown was named
`pack_id`, the same as the posted package rows `pack_id[]`. With a package picked but not
added, only the dropdown's value arrived. PHP 8 crashed; PHP 7 saved a junk package row
built from it. The dropdown is now `name="pack_pick"` (id unchanged, the scripts read it by
id), so it can never stand in for the rows.

Checked on the real quotation screen (quotation 1357, a package picked but not added,
Update): old code on PHP 8 - the fatal above; old code on PHP 7.4 - one junk package row;
new code on both - saved, no stray row.

---

## 2026-10-06 - 06-10-26 database checked, principal master, every onclick button proven

### Database (`bie_db_061026.sql`)

Every change from this work is live: `tbl_stock_flow` columns, decimals and indexes,
`tbl_stock_adjustment`, menu rows 147 / 148 with six rights rows, `tbl_user_rights.sm_id` /
`mm_id` widened with the clamped rows gone, cash denominations (2000 off, 1 / 2 / 5 on in both
tables), no zeroed invoice or proforma amounts, the four temp tables dropped.

Every `INSERT` column list and `UPDATE ... SET` column in the code was then checked against
this schema. One real gap: **`mst_principal` has no `created_by`, `created_dtm`, `modify_by`,
`modify_dtm`**, which `mst_principal.php` writes, so adding or editing a principal always
failed with "Unknown column 'modify_by'". Same in the original code - the comparison had
marked it SAME because both versions failed alike. **`db/post_upload_061026.sql`** adds the
four columns (types as in the other masters, existing rows get 0 / NULL, safe to run twice).
Checked: update records who and when, a new principal saves.

The other unknown references are old and dead: `inc/cis_ajax/Untitled-1.php`,
`mst_product_add.php` (`mst_products`), `user_actions.php` (`tbl_user_actions`).

### Buttons with their own onclick - full list

The first inventory searched for `onclick=` case-sensitively and could not see buttons with
PHP inside the tag. Redone: 21 buttons in 10 files - the nine already listed plus
`mst_supplier_approve.php` (Approve / Reject, written `onClick`). With the rewritten lock
each one now posts its own name on the current code: Send to Purchase, Generate PO
(`po_prepare_print.php`, a purchase order created and its lines marked generated), GRN Draft
and Save GRN, invoice Update / Finalize, direct PO Update / Finalize, credit Update,
quotation Finalize, employee Update, supplier Approve / Reject, purchase return Finalize.
Draft on a brand new invoice or direct PO needs an item row first; it shares its
validation with the Update / Finalize buttons above.

### Correction

The "DataTables warning: Invalid JSON response" listed earlier as an old GRN list problem
was the test machine: its timezone database lacked the old name `Asia/Calcutta` that
`functions.php` sets, so every json response began with a notice. XAMPP has the name.
With it installed, every screen's ajax responses were rechecked on the current code - all
clean apart from `lst_employee.php` asking for `ajaxEmployeeList.php` while the file is
`ajaxEmployeelist.php`, which only matters on a case-sensitive (Linux) server.

---

## 2026-10-06 - Audit of every changed screen: save buttons, GRN rollback, stale reads, DC packing

Started from "Send to Purchase not working". Every screen changed since 22 Sep was then
checked by running the original code and the current code side by side on PHP 7.4 and
MariaDB, each against a copy of the live database (`bie_db_051026.sql`), opening every
screen and clicking every save button on real documents, then comparing the rows each
version wrote.

### Fixed

| Screen | What was wrong | Since |
|---|---|---|
| `inc/common/css-js.php` | Double-submit lock rewritten. Buttons whose own `onclick` calls `fnValidate()`, which calls `form.submit()`, posted without their name: Send to Purchase, invoice Draft / Update / Finalize, direct PO Draft / Update / Finalize, GRN Draft and Save GRN, credit Update, Generate PO. The page just reloaded. The lock no longer touches `form.submit()` or cancels a submission; it only disables the buttons once a submit is under way and drops later clicks. The single-use form token is the real guard. | 1 Oct |
| `grn_add.php` | New GRN from a PO: `db_commit` sat after `header(); die;`, so the GRN, its lines, the stock-in and the PO status were rolled back every time while the screen said "GRN Successfully Recorded". | 24 Sep |
| `grn_pay_receipt.php` | Paid total summed through `$dbconn`, a second connection that cannot see the payment just inserted, so a GRN paid in full was marked partly paid. Now read on `$conn`. | 24 Sep |
| `po_prepare.php` | Same - prepared qty summed without the lines just saved, so a complete PO went to admin as partial. | 24 Sep |
| `emp_advance_return_payment.php` | Same - repaid total left out the repayment being saved. | 24 Sep |
| `import_attendance.php` | Same, worse - the "already imported?" check still saw punches deleted a line earlier, skipped the re-insert, and the delete was committed. Re-importing an overlapping file dropped those punches. | 1 Oct |
| `mst_itemprice_history.php` | The multi-UOM form (`thisForm2`) had no csrf token, so its Save and Update were always rejected. Behind that, its insert read `branch_new_min_discount[]` / `branch_new_max_discount[]`, which those rows have never had, so it would have failed on a NOT NULL column anyway - true since the first commit. Now binds the column default 0 when the field is absent. Checked: the multi-UOM row now saves. | 24 Sep |
| `dc_add.php` | Packing took its box type and dispatch qty from the visible row. On a DC whose line it had itself completed, the page's own load-time check blanks those inputs (old behaviour), so saving it zeroed the packing and the box counts. Packing now carries its own box type and qty, loaded from the saved packing and set when packing, as the temp table did. | 1 Oct |
| `modal_so_det.php`, `modal_so_reject_dets.php`, `modal_grn_reject_dets.php` | Opened on their own they died on `csrf_fields()`. They now load the csrf and txn helpers themselves. In normal use they are included by their list pages and worked. | 1 Oct |

`inc/common/db_txn.php` gains `db_value($conn, $sql, $params)` - one value read on the
connection doing the work. Making `$dbconn` share `$conn` was tried and rejected: on PHP 7.4
any statement, even a SELECT, resets `lastInsertId()` to 0, so a lookup between an insert and
`lastInsertId()` would have saved detail rows against id 0.

### Data lost on the live server

GRN ids 1563, 1564, 1565, 1566, 1569, 1570, 1572, 1573 and 1576 are missing - each is a GRN
entered between 25 Sep and 3 Oct that was rolled back. Ids up to 1559 have no gaps. Their
stock was never added, so re-entering them after the upload is correct and safe.

### Checked and fine

- PHP 7.4 compatibility of the whole tree (PHPCompatibility, testVersion 7.4): nothing I wrote
  needs PHP 8. The one `match` is in the old `update_stock.php` import script.
- Every form that posts to a csrf-checked handler carries the matching token.
- No `die` / `exit` in any form between a write and its commit, anywhere.
- 183 screens and edit views opened in both versions: no new PHP, JS or AJAX errors.
- Save buttons compared old vs new: store indent, invoice, DC invoice, quotation invoice,
  GRN update - same rows, or differing only by the intended fixes (invoice Amount no longer
  zeroed, ledger rows carry direction / column / remark).

### Also confirmed end to end on the deployed vs fixed code

- New GRN from a PO: deployed code wrote nothing and burned a GRN id; fixed code writes the
  same rows as the original.
- Paying a GRN in full, PO prepare Draft / Send to Admin: fixed code matches the original.
- Advance repayment: deployed left the advance open with balance 0, fixed closes it.
- Attendance re-import: deployed 3 punches -> 0, fixed keeps 3 on every re-import. No
  attendance has been imported on the live server since June, so nothing was lost.
- Stock Adjustment: 202 -> 200 with an ADJ ledger row and the reason.
- Masters (brand, category, colour, department, designation, division, HSN, labour, principal,
  UOM, branch, city, state, district, salary package, item, item group, supplier, employee):
  same rows as the original. Customer differs only in that a trailing space on a branch name
  is now trimmed.
- No advances or GRN payments in the live data were affected by the stale reads.

### Left as it was (older than these changes)


- Opening a DC whose line that same DC completed shows "Dispatch Qty Must be Less Than the
  SO Qty" and blanks the qty, because "Despatched" includes the DC's own qty. Saving it then
  zeroes the qty in `tbl_dc_details`. Same in the original code.
- `user_actions.php` queries a column `inst_id` that `tbl_user_rights` does not have.

---

## 2026-10-06 - Stock List: Rajapalayam had a header but no cells

`rpt_all_store_stock_list.php`

The branch column headers were built by looping `mst_branch` - three active branches, so
three headers - but the row loop wrote exactly two cells, hardcoded:

```php
while ($obj1 = $result1->fetch()) {
    echo '<td align="right">' . $obj1->ho_stock . '</td>';
    echo '<td style="text-align: right;">' . $obj1->kl_stock . '</td>';
}
```

Every row was one cell short of its header, so the browser left the last column with
nothing under it - which is what looked like a merged Rajapalayam column. The figures were
not wrong, that column simply was never written.

Now the branch list is read once into `$rpt_branches` and both the header and the rows walk
it, each row taking that branch's own `branch_stock_field` from the item's stock row. A
branch added to `mst_branch` gets its column with no code change. Also:

- The branch list used to be a live cursor that the header loop drained, so it could only
  ever be read once. It is an array now.
- An item with no `tbl_item_stock` row used to produce **no** branch cells at all, leaving
  that row short by two. It now shows `0.000` under every branch.
- The per-item stock lookup is a prepared statement rather than the item id concatenated
  into SQL.
- The "No History found..!" colspan follows the branch count instead of being fixed at 10.

### Checks

`test_stocklist_cols.php` (19) renders the page's own table block against real tables:
header and body cell counts agree with three branches, with a fourth added to `mst_branch`,
and with only two; the values land under the right branch each time (`17|5|2`, then
`17|5|2|9`); an inactive branch is left out; an item with no stock row stays full width and
reads `0.000`; and the empty-state colspan spans every column.

---

## 2026-10-06 - Save and Generate Report stopped working - double-submit lock regression

My double-submit lock broke roughly 50 screens: every master add/edit, every report
filter, and the main document screens. Reported as "report not working" on Stock List and
"unable to create new items" in Item Masters; the cause is the same for both.

### What happened

Nearly every screen validates like this:

```html
<form name="thisForm" onSubmit="return fnValidate();">
```
```js
function fnValidate() {
    if (isNull(...)) { return false; }
    document.thisForm.submit();      // and then returns true, or nothing
}
```

That `document.thisForm.submit()` lands while the browser is already submitting the form.
The browser ignores it - a form cannot start a second submission while the submit event is
still being dispatched - and then carries on with its own submission, which is the one that
includes the clicked button, `SAVE=SAVE` or `Report=Report`. Every php handler is gated on
`isset($_POST['SAVE'])` or `isset($_POST['Report'])`, so that button name is what makes the
page do anything.

The lock wrapped `HTMLFormElement.prototype.submit`, so that ignored call still marked the
form as locked. The browser's real submission then reached the lock's submit handler, which
saw a locked form, took it for a second click and cancelled it. What reached php was the
programmatic submission - the whole form **except** the button name. So the page posted,
reloaded, and did nothing.

### The fix

`inc/common/css-js.php` now notes, in a capture-phase listener that runs before any inline
`onSubmit`, which form is mid-submit. A `form.submit()` for that same form is left alone:
no lock, no native call, so the browser's own submission proceeds carrying the button. A
`form.submit()` with no submit event behind it - from a link or a select - still locks as
before.

### Why the first test missed it

The old browser test replaced `HTMLFormElement.prototype.submit` with a counter, so nothing
was ever posted and it could only count submissions, not see what they contained. It
counted one submission and passed. Rewritten against a real server that records POST
bodies, so every case now asserts the button name and field values php would actually
receive.

### Checks

`jstest/run.js` (22) against Chromium and a real server: the `form.submit()` validation
pattern in both its shapes (returning true, and returning nothing) posts once with the
button name; double-clicking either still posts once; a failed validation posts nothing and
leaves the buttons usable, and submits properly once fixed; a plain `return true`
validation still works; a programmatic submit behind a link still posts once and is still
locked against a second click; two forms on a page stay independent; buttons still disable
once a submit goes through.

---

## 2026-10-05 - Server queries for the 05-10-26 database

Checked the new live dump (`db/bie_db_051026.sql`) against what the uploaded code
needs. Everything to run is in **`db/post_upload_051026.sql`**, step by step, with the
row count each step should report.

Already live, nothing to do: the `tbl_stock_flow` columns and indexes,
`tbl_stock_adjustment`, menu rows 147 / 148, and the cash denominations.

### What still has to be run

1. **`tbl_user_rights.sm_id` is a signed `TINYINT`.** It stops at 127, so the rights rows
   for sm_id 147 and 148 were both silently clamped to 127 when they were inserted -
   MariaDB is not in strict mode. The two new menus therefore never appeared for the
   branch users, and because 127 is a real menu (Employee Salary Setting under main menu
   23) those users now show it wrongly ticked on the rights screen. Widened to
   `SMALLINT`, `mm_id` with it. This had to be first: `mst_users_rights.php` deletes and
   re-inserts a user's whole rights set on save, so any save would have clamped them
   again.
2. **The six clamped rows** (auto_id 662-667, users 2 / 6 / 7, sm_id 127 under main menus
   3 and 20) are deleted. auto_id 507 - user 4, sm_id 127 under main menu 23 - is genuine
   and is left.
3. **Rights granted properly** for sm_id 147 and 148 to whoever holds the parent menu.
   Three users each. Admin needs no row.
4. **Invoice line Amount repair.** 111 lines across 35 invoices.
5. **Optional**: the GST split columns on those same 111 lines.
6. **Drop the four temp tables** once the uploaded screens are tested. Three are empty;
   `tbl_package_box_details_temp` holds 12 abandoned scratch rows. No code refers to any
   of them now.

### `db/invoice_value_repair.sql` was wrong - superseded

It rebuilt the zeroed `inv_value` as `net_value - tax_value`. Against the real data that
is wrong twice over: `tax_value` is also 0 on 106 of the 111 rows, and `net_value` is the
gross, so the subtraction hands back the gross. Its own cross-check then rejected all 111
rows, so it would have repaired nothing - it was written from the column names rather
than from the data.

The taxable value is `net_value / (1 + vat/100)`. That agrees with
`qty x unit_price` less the discount percent on all 111 rows, so step 4 uses it and
rebuilds `tax_value` in the same statement. Five rows (details_id 2183, 2424, 17242,
17246, 17725) carry a `tax_value` worked out on the undiscounted price; they are
corrected too.

Worth knowing: `invoice_print.php` recomputes the line value, tax and GST split from qty,
price, discount and the HSN master, so printed invoices were always correct, and
`tbl_invoice.inv_tot_value` was never affected. This repairs the stored columns the list
and edit screens read.

### Fixes after the first run on the server

- **Step 7 failed with `#1109 Unknown table 'tbl_user_rights' in information_schema`.**
  It was one `UNION ALL` whose first branch read `information_schema.COLUMNS`; MariaDB
  then looked for the plain table names in `information_schema` as well. Split into five
  separate queries, which also read better in phpMyAdmin. Steps 1 to 6 had already run -
  step 7 only reports.
- **Step 3 would have inserted duplicates on a second run.** There is no unique key on
  `(usr_id, mm_id, sm_id)`, so re-running the file would have doubled the rights rows.
  Both grants now skip anyone who already has the row.
- **Step 4b would have failed on a second run**, or overwritten the backup. Changed to
  `CREATE TABLE IF NOT EXISTS`, so the original backup is kept. Every step in the file is
  now safe to run twice.

### Checks

`test_postupload.php` (24) replays every data-changing statement against the real rows
parsed out of `bie_db_051026.sql`: 6 rights rows deleted and the genuine one kept, 3 + 3
granted with no duplicates, the sidebar query then returning both menus for users 2, 6 and
7, 111 invoice lines repaired with `inv_value + tax_value = net_value` and
`inv_value = qty x price less discount` on every one, no healthy row altered, 0 proforma
rows, and the optional step 5 confined to the same 111.

---

## 2026-10-02 - Item Stock History report: item optional, newest row on top

`rpt_item_stock_history_branch_wise.php`

- **Item is no longer mandatory.** Leaving the item blank now reports every item for the
  chosen branch and date range. `fnValidate()` was blocking the submit with an "Item ..!"
  alert, and the result block was guarded by `$_REQUEST['item_id'] != ''`, so a blank item
  silently rendered nothing. Both removed.
- **An Item column appears on the all-items run** (between Date and Type), showing
  `item_code ~ item_description`. A row whose item was removed from the master falls back
  to `Item <id>`. The single-item run keeps its nine columns as before - the heading
  colspan and the "No History found..!" colspan follow the column count instead of being
  hardcoded.
- **Item name comes from a join**, `LEFT JOIN tbl_item_details`, not a `GetSingleReconrd`
  per row. On an all-items run that would have been one query per line.
- **Newest first.** `ORDER BY auto_id ASC` became
  `ORDER BY sf.trans_date DESC, sf.auto_id DESC`, so the latest movement is on top and
  several movements on the same date are still in the order they were recorded, reversed.
- The item filter is appended to the `WHERE` only when an item is chosen; the parameter is
  bound either way. Branch, date range, `stock_status = 0` and `trans_qty > 0` are
  unchanged.
- `$_REQUEST['item_id']` is read once into `$rpt_item_id`, so the page no longer warns on a
  first visit with no item in the request.

The View All link from the Item Stock List modal still arrives with `item_id` set, so that
path is unchanged.

### Checks

`test_stockhist_rpt.php` (20) - a blank item returns every item in the branch and range,
rows come back date-descending then auto_id-descending, the join supplies the item name and
an orphan row falls back to its id, a chosen item still filters, and branch / zero-qty /
out-of-range rows are still excluded. `test_stockhist_render.php` (18) - the page's own
table block rendered against real tables: header and body cell counts agree in both modes,
the Item column only appears on the all-items run, remarks stay escaped, and the empty-state
colspan matches.

---

## 2026-10-01 - Login pages: query bound, csrf, session id regenerated

### `index.php`

The login query was built by string concatenation straight from `$_REQUEST`:

```php
$sql = "SELECT * FROM tbl_user WHERE usr_status = 1 AND usr_access=1
        AND usr_logname = '".$_REQUEST['txt_username']."'
        AND usr_logpwd LIKE BINARY '".StandardHash($_REQUEST['txt_userpwd'])."' ";
```

The username went in raw, so the password half of the `WHERE` could be commented out or
`OR`-ed away. All three of these logged in as the first active user before the fix:

| Username typed | Password |
|---|---|
| `admin@bie.com' -- ` | anything |
| `' OR '1'='1` | anything |
| `' UNION SELECT * FROM tbl_user WHERE usr_id=1 -- ` | anything |

Now a prepared statement with both values bound. Nothing else about who may log in changed:
same `usr_status = 1 AND usr_access = 1`, same `LIKE BINARY` hash compare, username trimmed.

Also on this page:

- `csrf_check('login')` on the post. A stale or replayed login post is bounced back to the
  form with a message instead of being run again.
- `session_regenerate_id(true)` on success, and the csrf / form tokens are dropped, so a
  session id fixed before login cannot be used after it.
- Failed logins are written to the error log with the username that was tried.
- The username field no longer ships `value="admin@bie.com"` prefilled.
- `autocomplete="username"` / `"current-password"` so password managers behave.

Messages on this page use `$_SESSION['_msg']` - that is the only key the login card renders.

### `admin_multi_logins.php`

Second login path, admin only. Same concatenated query, and it also did this:

```php
echo $sql = "SELECT * FROM tbl_user WHERE ... AND usr_logpwd = '".trim($_REQUEST['txt_userpwd'])."' ";
```

The query, with the password in it, was printed to the page. Removed. Query bound,
`csrf_check('admin_multi_logins')`, session id regenerated and tokens rotated on success.

**Left alone on purpose:** this page compares the typed password against `usr_logpwd`
without `StandardHash()`, while `index.php` hashes first. So the feature only succeeds if
somebody types the stored hash. Looks wrong, but changing it changes who can use the
screen, so it is a decision for the team, not a side effect of this fix.

### Not touched

Report filter forms. They only read, and they work.

### Checks

`test_login.php` (16) - real credentials still work, the five attack strings all fail
against the bound query and succeed against the old one, `tbl_user` survives an injected
`DROP`. `test_loginflow.php` (13) - first visit, retry after a wrong password, back-button
resubmit rejected, two tabs both work, tokens cleared at login, bare and forged posts
rejected.

---

## 2026-10-01 - Error log, double-submit lock, csrf and rollback everywhere

### Error log

`inc/common/error_log.php` (new). `fnLogError($e)` writes one line per error to
`logs/error_YYYY-MM.log`:

```
2026-10-01 10:41:38 | grn_add.php:412 | grn_add.php | user 7 Storekeeper | SQLSTATE[23000]: Duplicate entry...
```

Date, the file and line that threw, the page, the user, the message. One file per month.
Takes an exception or a plain string, plus an optional context note. It never throws - a
failed write must not break the page.

Added as the first statement of **136 catch blocks across 69 files**, so an error is
recorded even where the handler only set a session message or swallowed it. Several
handlers echoed the raw SQL error to the screen; the log now captures it properly.

`logs/` is created on first write with an `.htaccess` denying web access, and
`logs/.gitignore` keeps the files out of the repository.

Loaded from `inc/common/dbconnect.php`, not only `userclass.php`, so `fnLogError()` also
exists in the ajax endpoints - they include dbconnect directly and never load userclass.
`dbhandler.php` and `functions.php` require it too. Two places are deliberately not
logged: `error_log.php`'s own internal catch, which would recurse, and a commented-out
block in `inc/cis_ajax/Untitled-1.php`.

### Double-submit lock

`inc/common/css-js.php` - one handler covering every form, no page needed changing.

Once validation has let a submit through, the form is flagged and its buttons disabled. A
second submit while the flag is set is dropped. An alert from `fnValidate()` leaves the
buttons usable so the field can be fixed and retried.

Two details that matter:

- Buttons are disabled on a **zero timeout**, after the browser has read the form.
  Disabling them inside the submit event drops the clicked button's name from the post,
  and every handler here keys off `isset($_POST['SAVE'])`.
- **37 screens end `fnValidate()` with `document.thisForm.submit()`**, which does not fire
  the submit event, so a plain submit handler would miss them.
  `HTMLFormElement.prototype.submit` is wrapped to lock those too. Those screens also had
  a latent double submit: `fnValidate()` calls `submit()` and then returns `undefined`, so
  the native submission went ahead as well. The lock closes that.

The back button restores a cached page with its buttons disabled, so `pageshow` re-enables
them.

### CSRF, form tokens and rollback on the rest of the forms

Extended from the 16 main document forms to **all 56 forms that write to the database** -
every `mst_*` master, the payroll and attendance screens, `mng_credit.php`,
`pay_receipt.php`, `grn_pay_receipt.php`, `user_actions.php`, `mst_users_rights.php`,
`supp_items.php`, `spare_mapping.php` and the three approval modals.

Each gets a `csrf_check()` guard on every POST handler, `csrf_fields()` in the form, and
`db_begin` / `db_commit` / `db_rollback` around every try.

**Points to know**

- `mst_employee_add.php` needed hand work. Its UPDATE handler has a `}` at column zero
  inside the handler, which hid the end of the block from the bulk pass, and its SAVE
  handler ends with a three-way redirect by employee type - the commit had to go above the
  whole chain or only one of the three types would have committed.
- The three approval modals (`modal_grn_reject_dets.php`, `modal_so_det.php`,
  `modal_so_reject_dets.php`) close their handler with an indented brace, so they were
  also done by hand. They had no try/catch at all before.
- `checkattendance.php`, `emp_advance_return_payment.php` and `grn_pay_receipt.php`
  redirect to a URL with a query string; the failure redirect was pointed at a real listing
  page instead of a truncated one.
- **Not covered: `index.php`, the login page.** It is the highest-value csrf target, but
  breaking it locks everyone out of the application, so it is left alone pending a decision.
- Report filter forms that only read (the attendance reports, `admin_multi_logins.php`,
  `myprofile.php`, `mst_users_add.php`) are not covered either - a single-use token on a
  filter form gets in the way and there is nothing to double-enter.

---

## 2026-10-01 - Temp tables replaced by page rows

All four `*_temp` tables are gone. Form rows now live in the page as hidden array
inputs and post back with the form, following the GRN pattern from the other project.
Ajax endpoints only render markup; they write nothing.

| Temp table | Screens | Replaced by |
|---|---|---|
| `mst_customer_branch_temp` | `mst_customer_new.php` | `fnBranchRow()` + `br_*[]` arrays |
| `tbl_item_group_details_temp` | `mst_item_grouping.php` | `fnItemGroupRow()` + `ig_*[]` arrays |
| `tbl_dc_details_temp` | `dc_add.php` | `$dc_rows` array + `dc_item_id[]`, `dc_qty_h[]`, `dc_unit_h[]` |
| `tbl_package_box_details_temp` | `dc_add.php`, modal | CSV hidden fields on the item row |

**New** - `inc/common/form_rows.php` holds the shared row renderers, loaded from
`inc/common/userclass.php`. The page and the ajax endpoint render through the same
function, so a saved row and a just-added row cannot drift apart.

**Removed** - `add_packing_box.php`. The packing modal no longer writes to the
database, so the file had no callers left.

### Bugs this removed

Every temp table was shared across users, with no filter on the statements that
mattered:

- `DELETE FROM ..._temp` with **no WHERE** ran on page load in `mst_customer_new.php`
  (twice), `mst_item_grouping.php` and `dc_add.php`. Opening any of those screens wiped
  every other user's in-progress rows.
- The listing `SELECT`s had no filter either, so users saw each other's half-entered
  branches, group items and DC lines.
- Abandoned rows accumulated with nothing to clean them up.
- `trade_items` was read by `fnValidate()` in `mst_item_grouping.php` but never existed
  as a form field, so the "Please add Items to Group" guard threw a TypeError and never
  fired. The field now exists and holds the row count.
- A read-only user (type S) got no qty or position inputs on the group screen, so a save
  from there wrote blanks. Those values now post as hidden fields.
- The DC save handlers mixed temp values with posted arrays and matched them by array
  position, so the temp row order had to line up with the form row order.
- `jquery_modal_dc_pack_dets.php` counted poly bags into `$boxtype3`, overwriting the
  gunny bag count. Box types are now counted once, in javascript, from the rows on screen.
- `add_packing_box.php` counted box types across the whole sales order while the modal
  counted them per DC. Now consistently per DC.

### Behaviour changes to test

- **Customer branches** - Edit marks the row and ADD replaces it in place. The old code
  matched on name plus contact number, so editing a branch name silently created a duplicate.
- **Item groups** - pulling in an existing group adds the typed quantity to whatever the
  row already shows. The old code set an already-listed item to that group's own quantity
  plus the typed one, ignoring what was on screen, while a new item got only the typed one.
- **DC packing** - the modal's SAVE no longer round-trips to the server; it writes back
  into the row and recounts the box types in the browser.

**Data** - run `db/drop_temp_tables.sql` once the three screens are confirmed working.

---

## 2026-09-30 - Invoice Amount zeroed on edit, percentage charges computed 0

**Symptom** - on an invoice opened for edit (`dc_invoice.php?inv_id=...`), the Amount column
read 0.00 and any percentage-based charge (transport, packing) came out with GST Amount 0,
even with the HSN picked and GST % showing correctly.

**Cause 1 - the edit view posted a column that does not exist.** The edit branch renders the
Amount cell from `$obj->inv_value` but filled the hidden field that gets re-posted from
`$obj->quo_value`. The query is `SELECT * FROM tbl_invoice_details` with no join, and that
table has no `quo_value` column, so the field rendered empty. Saving wrote `''` into
`inv_value decimal(12,2)`, which MySQL stores as 0.00. Every other field in that branch reads
a real column, which is why only the Amount column was wrong.

Same defect in three places, all fixed:

| File | Was | Now |
|---|---|---|
| `dc_invoice.php` | `$obj->quo_value` | `$obj->inv_value` |
| `quo_invoice.php` | `$obj->quo_value` | `$obj->inv_value` |
| `quo_proforma.php` | `$obj->quo_value` | `$obj->pro_value` |

The create-from-quotation branches in the same files keep `$obj->quo_value` - they read
`tbl_quotation_details`, which does have that column.

**Cause 2 - the percentage base was read as formatted text.** `get_value()` in
`dc_invoice.php` sent `$("#quo_total_amt").text()`, the displayed value with its thousands
separator. PHP casting `"8,125.00"` stops at the comma and yields 8, so a 10% charge computed
on 8 instead of 8125. Now reads the raw hidden `#txt_quo_total_amt`, which the page already
carried and which the row recalc was already using. Fixing only cause 1 would have produced a
GST amount of 0.14 instead of 146.25, so both were needed.

**Data** - run `db/invoice_value_repair.sql`. Any invoice or proforma saved from its edit
screen before this fix already has `inv_value` / `pro_value` zeroed. `qty`, `unit_price`,
`vat`, `tax_value` and `net_value` were never affected, so the value is rebuilt as
`net_value - tax_value`, cross-checked against `qty x unit_price` less the discount. Rows
where the two disagree by more than a rupee are listed and left alone. Steps 1 and 2 are
read-only; read them before running step 3, and back the tables up first.

**Points to know**

- The save handlers were left as they are - they were correct once the field feeding them is.
- The invoice total was never wrong: `net_value` is stored separately, so the bug was silent.
  Only the Amount column and charges derived from it went to zero.
- Not changed: `inc/cis_ajax/jquery_quotation_package_cal.php` returns
  `gst_per ~ package_gst_val ~ package_gst_val` - the second slot should be
  `$package_taxable_val`, which is computed but never sent. In `dc_invoice.php` the element it
  feeds does not exist, so it is harmless there. In `quotation.php` it fills
  `#quo_pack_taxable_value`, which is forwarded to the row-add call, so that page stores the
  GST amount where the taxable value belongs. Left alone because it changes quotation
  behaviour and was outside this fix.
- `quotation.php` has the same formatted-text base as cause 2. Left alone for the same reason.

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
