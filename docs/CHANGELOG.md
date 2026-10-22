# BIE - Change Log

Running log of code and database changes. Newest entries at the top.
The same list is kept in `docs/BIE_Change_Log.xlsx` (tabs: **File Changes**, **Table Changes**).

Comment convention in code: short single-line `//` notes only. Each changed file carries a
dated one-liner near the top, just above the commented-out `ini_set` lines. Details live here.

---

## 2026-10-22 - Sales Order Report: Partial opens the SO's items and their stage

- Clicking **Partial** opened `dc_add_view.php`, a page that does not exist. It now opens a popup with the SO's
  items: ordered, DC qty (approved; DCs waiting approval shown apart), invoiced, still to DC, still to invoice,
  branch stock, stage (Not dispatched / DC in approval / Part dispatched / Dispatched, not invoiced / Part invoiced /
  Completed) and the DC and invoice numbers with their qty. Pending items by default, **Show all items** for all.
- **Status fix:** the report added up `dc_qty`, which holds the ordered qty again on every DC - an SO sent in two or
  more DCs never showed Completed. It now adds up the dispatched qty (`dc_dispatch_qty`). On the local copy 10 of 596
  SOs change: 6 Partial -> Completed (e.g. SO/0080/BIE/KL/2627: 6 ordered, 3 + 3 sent and invoiced) and 4 Completed ->
  Partial (one DC raised for the whole order but only part sent).
- The report form called a `fnValidate()` that does not exist (a script error on every Generate Report) - removed.

---

## 2026-10-22 - GRN payment: status in the GRN payment list, bill amount edit, multiple GRN bills

Customer: the status in the GRN payment list was not right, and a bill amount could not be entered on edit.

**Multiple GRN bills never linked their GRNs (main cause of wrong status).** "Multiple GRN" lists every unbilled
GRN of the supplier, but the save only linked GRNs on the same PO. Of 23 multiple bills on live only 1 was
linked; the other GRNs stayed "Not Yet" though their bill was entered and paid on the main GRN, and some were
later given a bill of 1 to clear them. GRNs are now linked by supplier and branch (any PO); the dropdown leaves out
GRNs already on another bill; the main GRN lists only the GRNs actually linked; un-picking a GRN releases it.

**Editing a bill amount** set the status back to Pending even when part or fully paid, and kept the old balance;
the first bill amount was saved with balance 0. Status and balance now come from the payments made
(1 nothing paid, 2 part paid, 3 fully paid); a bill below the paid amount is refused on the server too.
Paying also works the balance out on the server. The payments table "Balance" took off only the first payment
and rounded off the paise - now all payments, to the paisa. The list shows status as saved and paid 0.00
instead of blank.

**"Not able to enter the bill amount on edit":** the Edit button is for GRN Payment approvers in Task Settings
(admin only today - it was hidden for everyone before 13-10, the old check used a session value never set).
Give the accounts user "GRN Payment - Approver" in Task Settings to let them correct a bill amount.

Also: GRN payment list page set its status filter before use (PHP 8 printed a warning inside the page script);
list filters / sort and the multiple-GRN save take ids only as numbers and quote the search text.

**Bill amount locked on a "Not Yet" GRN** (same day, from the customer's screen): the page took "no bill yet" to
mean the amount text was exactly `0.000`. A database holding the amount with 2 decimals sends `0.00`, so the page
thought a bill existed - bill amount read-only, payment section and Update shown instead of Save. The check is now
the number 0 (this check was in the page from the start). "Multiple" rows in the list now have the edit icon too;
it opens the payment page of the GRN they are billed on.

`db/grn_pay_status_fix_221026.sql` (run after uploading, safe to run again): links the GRNs of the old multiple
bills (22 on live), then sets status and balance from the payments; lists the 4 GRNs that sit on a multiple bill
but also got a bill of their own (GRN/0447, 0695, 0696 /2425-2526, GRN/0116/2627) for the customer to check.

---

## 2026-10-16 - Loader on the price screens, branches line, principal filter on the item list

- Bulk Discount / Margin and Item Price Update show a small "Please wait.." over the page while items load, a row or
  the bulk change saves, and (bulk) while a big list redraws after the value is typed - the preview now redraws
  300 ms after typing stops instead of on every key.
- Bulk Discount / Margin: Branches moved to their own (third) line, full width.
- Item Details list: Principal filter (name - code) next to Brand, works together with Brand and the search box.
  The list call now takes brand / principal only as numbers and quotes the search text - they went into the sql as
  typed, so a quote in the search broke the list.

---

## 2026-10-16 - Item prices: margin on cost everywhere, Bulk Discount / Margin and Item Price Update screens

**One rule on every screen** (`js/price_calc.js`, used by the item form, Item Price History and the two new screens):
cost price = item price less discount; margin % = (selling - cost) / cost x 100. Changing item price or discount
changes cost and margin only - the selling price changes only when margin or selling price is edited.
Item Price History used margin on item price and both screens rewrote the selling price whenever price or discount
was typed; both fixed. The margin shown after a branch is loaded is worked out on cost.

**Item Masters > Bulk Discount / Margin** (`item_bulk_discount.php`): choose principal / brand / division / item
type (any together) and branches (all by default); every matching item is listed per branch. Enter one discount %
(cost and margin follow, selling stays) or one margin % (selling follows, cost stays); the list shows old and new
values, rows can be removed, one Save changes them all in one transaction. Example: price 1000, selling 1000,
discount 28 -> 30: cost 720 -> 700, margin 38.89 -> 42.86. 10,740 rows save in under a second.

**Item Masters > Item Price Update** (`item_price_list.php`): same filters plus code / name search, one branch; each
item shows item price, discount, cost, margin and selling as boxes with a Save button on the row (Enter saves too).
Only that item in that branch is saved; the row turns green, unsaved rows yellow, negative margin red.

Both screens: Item creator in Task Settings (ITEM C) or admin. Every change writes a `tbl_itemprice_history` row per
item per branch (old and new). Head Office values are also kept in the item master copy (`tbl_item_details`), which
GRN uses to value stock.

`db/post_upload_161026.sql` (run before uploading): the two menus (given to Item creators - tick them in User Rights
for anyone made an Item creator later), an index on `tbl_item_stock.item_id` (every item price / stock lookup scanned
the whole table - a 10,740-row save went from 145 s to 0.7 s), and stored margins worked out again on cost (prices
untouched; about 2,800 items held 0, some held margin on item price; no cost or no selling price = 0).

---

## 2026-10-15 - SPAC items: item discount 28% to 30%

`db/spac_discount_30_151026.sql` (run in phpMyAdmin, safe to run again - only rows still at 28% change).
For every Shree Prayag Air Controls (SPAC) item, in each branch's prices (`tbl_item_stock`, HO / KL / RJPM) and the
item master copy (`tbl_item_details`): discount 30, cost price = item price less 30%, margin % = (selling - cost) /
cost x 100. Item price, selling price, min / max discount and stock stay as they are. One `tbl_itemprice_history`
row per item per branch with the old and new values. Example: price 715, selling 715 - cost 514.80 -> 500.50,
margin 38.89% -> 42.86%. Documents already made keep their own line prices.

---

## 2026-10-15 - Revoke: undo an approve / verify done by mistake

Customers kept asking to undo a wrong approval so the document can be corrected. Every task in Task Settings
that has an approve / verify step now also has a **Revoker** role (R). Nobody is given it yet, so only admin
users can revoke until users are added in Settings > Task Settings (per branch, like the other roles).

Where an approved document showed its Approve / Verify button, a **Revoke** button now shows instead (only to
Revokers of that document's branch). It asks for a reason, puts the document back and writes a row in
`tbl_revoke_log` (old values, reason, who, when); the print page then shows "Revoked by ... on ... - reason".

| Screen | Revoke takes it back to | Refused while |
|---|---|---|
| PO print | Draft - creator edits and sends again | a GRN is made against the PO (cancel the GRN first) |
| Supplier approve | Not approved | the supplier is used in a PO, GRN or purchase return |
| Purchase return view | Draft (a rejected return cannot be edited, so reject is no way back) | - |
| Quotation print | Draft | a sales order, invoice, proforma or requote is made from it |
| Sales order print | In approval (credit sales: back to credit approved) - approve again, or reject to send it back for editing | a DC or invoice is made |
| Receipt screen - credit approval | Credit requested | the sales order is approved (revoke that first), a DC / invoice is made |
| Receipt screen / receipt list - payment confirm, mark as credit | Payment pending | the sales order or the credit is approved (revoke those first), a DC / invoice is made |
| DC print | Draft | the DC is invoiced |
| Sales repair print - approve | In approval | - |
| Sales repair print - send for approval | Draft | the repair is approved (revoke that first) |
| Attendance reports - salary Issued | Not issued (Issue shows again); its accounts entry is deleted | - |

**Stock:** no approve or verify step moves stock - stock moves only when a GRN is finalized and when an invoice
is made. So a revoke never touches stock or the stock history; instead it is refused once a GRN, DC or invoice
has been made on top of the approval, and says which one.

Not given a revoke: GRN cancel, sales order cancel, GRN payment edit and the store indent approver are not
approvals; an item's branches can be changed again any time with the branch-wise link on the item list.

Run `db/post_upload_151026.sql` (after `post_upload_131026.sql`) before uploading the files.

---

## 2026-10-14 - Live error log (October): pages opened without a BIE login

The October log from live had two real errors and some failed logins.

**174 x `gen_so.php` and 7 x `modal_items_dets.php`: `SELECT  FROM tbl_item_stock ...` (empty column).**
The column is the branch's stock field, read for `$_SESSION['_user_branch']`, and every one of these lines
was logged with no user. Both times (09-10 15:51 and 12-10 18:37) it was Generate SO for quotation
083/BIE/HO/C0946 (87 items - one error per item). A normal BIE login cannot do this: it always sets the user
and the branch. What does is a session holding only `_user = "crm_user"` - the value another app built on
the same framework sets (`inc/dashboard/actions_default.php` in this project comes from such an app and uses
`_userid`). `isAdmin()` only checked `_user`, so such a session got into BIE pages with no user and no
branch. Reproduced: with that session the old Generate SO page showed 91 sql errors; now it goes to login.

- `isAdmin()` now also needs the user id and the branch (`fnIsLoggedIn()` in `functions.php`). Real BIE
  logins are unaffected.
- `inc/cis_ajax/modal_items_dets.php` had no login check at all (item prices and stock to anyone); it now
  needs a BIE login.

**Failed logins (33):** `admin@benzear.com`, `marketing`, `production`, `accounts1`, `ker` are not BIE users -
most likely people of the other app on the same server. `accounts` (16) is a BIE user: wrong passwords - the
stored hashes are clean and the login compares them the same way as before.

---

## 2026-10-14 - Invoice report showed a customer branch the customer does not have

`tbl_invoice` has two branch columns: `branch_id` is our branch that raised the invoice (1 = Head Office,
2 = Kerala) and `cus_branch_id` is the customer's branch / delivery address (0 = none). Saving is correct -
checked on PHP 7.4 and 8: a customer with no branches saves `cus_branch_id = 0`, a customer with a branch
saves the chosen one, and `branch_id` is always our branch.

`invoice_report.php` looked the customer branch up with `branch_id` instead of `cus_branch_id`, so every
Head Office invoice showed "Branch Name: BIKERZ YAMAHA - MUDICHUR" (customer branch 1) and every Kerala
invoice showed customer branch 2 - for customers like BENZEAR that have no branch at all. It now reads
`cus_branch_id`: no branch line when the customer has none, the real branch when one was chosen.
The invoice list, invoice prints and the other invoice screens already used `cus_branch_id`.

---

## 2026-10-13 - Fixes from the independent test report (critical and main only)

**Critical (in the code before Task Settings):** `inc/cis_ajax/jquery_delete_records.php` took the table,
column and value from the post with no login, so anyone reaching the site could change any value in any
table. It now needs a login and the csrf token, accepts only the 24 table / column pairs the delete links
use, always sets the status to 0 and binds the id. Every delete link still works.

Task Settings fixes:

- **Branch-only roles now work everywhere.** Sales repair checks its branch (`company_id`) on the buttons,
  the action and the dashboard count; salary Issue checks the employee's branch on the reports and now also
  on the server (`issue_salary.php` only hid the button before, and had no csrf check).
- **Named approver** on new POs, store indents and quotations takes an All-branch holder first, then a holder
  for the document's branch, else admin - a branch-only holder was ignored.
- **Task Settings** refuses user or branch ids that do not exist, and keeps Item and Stock Location rows at
  All branches (those screens are not per branch, so a branch row could never take effect).

Main fixes:

- **Approve / reject / verify only on a document that is waiting** - PO in approval, quotation / DC / purchase
  return sent and not decided, sales order verified and open, repair not yet sent / sent and not approved,
  credit SO waiting for credit approval. Otherwise nothing changes and the user is told it is not waiting.
- **Attendance work details** - users without the Employee viewer role now see only their own branch; the
  query had no branch filter, so every branch showed.
- **Employee list** called `ajaxEmployeeList.php` while the file is `ajaxEmployeelist.php` - fine on Windows,
  broken on a case-sensitive server. The url now matches.

Not changed, by agreement: edit screens that are protected only by hiding the button, repeat stock posting
on finalize, salary repeat issue, input validation and GRN-cancel stock reversal.

Checked on PHP 7.4 and 8, every task one by one for all 7 users, with the starting settings and again with
extra branch-only rows (Kerala as PO / repair / salary / credit / cancel / supplier / return / GRN approver for
Kerala, Stores as Item creator, viewers added and removed): every approve / verify / cancel action allowed
exactly when the settings say so for the document's branch, every button and list link shown exactly then,
viewer lists all-branch or own-branch as set; delete endpoint refuses no login, no token and other
columns while the colour master delete works; approving twice is refused; an inactive task stops
non-admins and its tile; named approver order and Task Settings validation as above.

---

## 2026-10-13 - A dashboard tile for every verify / approve job, counted for the user's branches

Rule: whoever is set in Task Settings to verify or approve something gets its tile, with the count of the
branches they hold it for, and the tile opens the list of exactly those documents. Tiles, counts and the
pages they open are defined once in `inc/common/task_queues.php`, so a tile and its page cannot differ.

| Tile | Task / role | Opens |
|---|---|---|
| Purchase Order InApproval | Purchase Order - approver | `lst_direct_po_approval.php` |
| Supplier InApproval | Supplier - approver | `lst_supplier.php?approve=0` |
| Quotation InApproval | Quotation - approver | `lst_quo_approval.php` |
| Sales Order InApproval (new) | Sales Order - approver | `lst_pending.php?q=SO_APPROVE` |
| Credit Sales Order InApproval | Credit Sales - approver | `lst_so_approval.php` |
| Sales Order Payment to Confirm (new) | Sales Order in Accounts - verifier | `lst_pending.php?q=PAY_CONFIRM` |
| Delivery Challan InApproval (new) | DC - approver | `lst_pending.php?q=DC_APPROVE` |
| Purchase Return InApproval (new) | Purchase Return - approver | `lst_pending.php?q=PR_APPROVE` |
| Sales Repair to Send for Approval (new) | Sales Repair - verifier | `lst_pending.php?q=REPAIR_SEND` |
| Sales Repair InApproval (new) | Sales Repair - approver | `lst_pending.php?q=REPAIR_APPROVE` |
| New Item InApproval | Item - approver | `lst_item_approval.php` |
| GRN Payment Pending | GRN Payment - approver | `grn_payment_details_pending.php` |

`lst_pending.php` (new) lists the waiting documents with an Open link to the screen where the job is
done. The existing approval lists now show only the branches the user approves for.

**Correction to the earlier entries.** Checking the live data for who really approves showed that the
sales order Approve / Reject buttons and the payment screen had **no user check** - only the remarks box
was admin-only. Kerala and Sales approve sales orders daily (301 and 294 so far), Purchase and Rajapalayam
have confirmed payments. The server check added earlier today would have stopped them. Now:

- Sales Order approver and Sales Order in Accounts verifier start with **every user** - head office users
  for all branches (they see every branch's orders), the others for their own branch. The buttons follow
  the same role, so a user removed in Task Settings no longer sees buttons that would be refused.
- **Sales Order Cancel** is its own task (admin), because cancel was admin-only while approve was open.
- `db/post_upload_131026.sql` has a second block for this; it runs once, also where the first version
  of the script was already run.

With the starting settings: admin sees all 12 tiles; Kerala, Rajapalayam and the head office users see
Quotation, Sales Order, Payment to Confirm and DC, counted for their branch (head office users: Sales
Order and Payment for every branch).

Checked on PHP 7.4 and 8, 12-10-26 live data plus test documents: for all 7 users every tile count equals
the rows on the page it opens and equals the count worked out in sql; Kerala opened the DC tile, opened
the DC, approved it and the tile went to 0; Accounts opened a payment from its tile; Sales was sent back
from a list that is not theirs and sees no rows on the PO approval list. Each action by each user: sales
order approve and payment confirm allowed for admin and head office users on a head office order,
cancel admin only, the rest unchanged. The script gives the same rows on a fresh copy of live and on a
copy where the first version had run, and adds nothing when run again.

---

## 2026-10-13 - Dashboard tiles checked: count = rows on the page it opens

Checked each tile as each of the 7 users on PHP 7.4 and 8 against the 12-10-26 live data: who sees it,
its count, where the link lands and how many rows that page lists.

| Tile | Shown to | Opens | Today |
|---|---|---|---|
| Purchase Order InApproval | Purchase Order approvers (All branches) | `lst_direct_po_approval.php` | 2 = 2 |
| Supplier InApproval | Supplier approvers | `lst_supplier.php?approve=0` | 1 = 1 |
| Quotation InApproval | Quotation approvers (All branches) | `lst_quo_approval.php` | 13 = 13 |
| Credit Sales Order InApproval | Credit Sales approvers | `lst_so_approval.php` | 1 = 1 |
| New Item InApproval | Item approvers | `lst_item_approval.php` | 9 = 9 |
| GRN Payment Pending | GRN Payment approvers | `grn_payment_details_pending.php` | 498 = 498 |

With today's settings only admin sees them, as before. Branch-only approvers (the quotation and DC
approvers of their own branch) do not get the tiles, which count every branch.

Three places where the tile and its page used different rules, fixed so they always agree:

- **Supplier** - the list opened at head office only, so a pending Kerala supplier was counted but not
  shown. Opened from the tile it now starts at All branches; opened from the menu it is unchanged.
- **Quotation** - the tile left out deleted quotations, the page did not. The page now leaves them out.
- **GRN Payment Pending** - the tile counted bill not done; the page lists bill and payment both not
  done. The tile now uses the page's rule.

Checked with a pending Kerala supplier, a deleted pending quotation and a GRN paid without a bill: 2 = 2,
13 = 13, 498 = 498.

---

## 2026-10-13 - Task Settings: who creates, verifies, approves and views, in one screen

**Run `db/post_upload_131026.sql` with these files.** It creates `mst_task` and `tbl_task_assign`,
adds the tasks below with starting rows that give every user exactly the access the code gave them,
and adds the menu Settings > Task Settings. Safe to run again (starting rows only go in once).

Before, these rules were typed into 70 files as user ids (`user_id == 1`, `== 9`), "head office"
(`branch == 1`) and the three `tbl_task_user` rows. Now every screen asks `fnTaskCan(task, role, branch)`
and the answer comes from Task Settings.

Roles: **Creator** creates / edits, **Verifier** sends for approval or confirms, **Approver** approves,
rejects and cancels, **Viewer** sees every branch on the lists. Each user row has a branch, or All.
Admin users (type A) can do every task and need no rows. Any one approver is enough.

| Task | Role - used for | Starting users (same as the old code) |
|---|---|---|
| Purchase Order | C - New PO button, edit links; A - approve / reject, dashboard tile; W - list | C, W: head office users. A: admin |
| Supplier | C - item mapping, delete any; A - approve, tile; W - list | C, W: head office. A: admin |
| Prepare Purchase Order | W - list | head office |
| Purchase Return | A - approve / reject; W - list | W: head office. A: admin |
| Quotation | A - approve / reject, tile; W - list | A: every user for their own branch. W: head office |
| Sales Order | A - approve / reject, tile; W - list | A: every user - head office for all branches, others their own (*corrected, see the tiles entry*). W: head office |
| Sales Order Cancel | A - cancel on the sales order list | admin |
| Credit Sales Approval | A - approve credit SO, tile | admin |
| Delivery Challan | A - approve / reject; W - list | A: every user for their own branch. W: head office |
| Invoice | W - list | head office |
| Sales Order in Accounts | V - confirm payment, mark credit, tile; W - list | V: every user - head office for all branches, others their own (*corrected*). W: head office |
| GRN | A - cancel GRN; W - list | head office |
| GRN Payment | A - edit paid amount, tile; W - list | W: head office. A: admin |
| Store Indent | A - named approver on new indents; W - list | W: head office. A: admin |
| Stock Location | C - update location | admin (user 9 in the old code does not exist) |
| Item | C - edit / price / branch / delete links; A - approve branch items, tile | admin |
| Sales Repair | V - send for approval; A - approve | admin |
| Employee / Staff / Labour | W - the HR lists | head office |
| Salary Issue | A - issue button on salary reports | admin |

Dashboard tiles show for users holding the approver role for All branches (a tile counts every branch).

Left as they are, on purpose: user-type checks (admin-only menus and user screen; the S / A checks on
the masters, which every user passes), new items from head office going straight in without approval,
and the stock column per branch on the stock list - these are about data, not people.
`tbl_task_user` is no longer read and can be dropped later.

One visible change: Purchase Return approve / reject now shows only to its approvers (admin); before,
it showed to anyone who opened the page, though the list is an admin-only menu.

Checked on PHP 7.4 and 8 against a copy of the 12-10-26 live data with the script run:
- every screen above opened as each of the 7 users on the old and the new code - 273 user / screen
  pairs, all the same apart from the three intended changes from the earlier entry (admin GRN amount
  Edit, csrf fields on supplier approve, item form user id) and row order the old code already varies;
- each approve / verify / cancel action called by each user - allowed exactly as before;
- changing the settings takes effect: Purchase user added as PO approver gets the tile and the button
  and can approve; Sales user's quotation viewer row removed shows only head office quotations;
- Task Settings: add task, add many users per role, viewer locked to All branches, duplicate code
  refused, non-admin sent back to the dashboard.

---

## 2026-10-13 - Approve / verify / reject checked on the server

Who sees each Approve, Reject, Verify and Cancel button is unchanged. What changed is that the
action behind the button now checks the same rule, so a user without the button cannot approve by
calling the page directly. Before, none of these checked who was calling, and the PO and purchase
return ones pasted the remarks straight into the sql.

| Action | Allowed (same as the button) | File |
|---|---|---|
| PO approve / reject | admin | `inc/cis_ajax/jquery_po_approval.php` |
| Quotation approve / reject | admin, or a user of the quotation's branch | `inc/cis_ajax/jquery_quo_approval.php` |
| Sales order approve / reject | see Task Settings - *corrected below: the buttons had no check, every user approves* | `inc/cis_ajax/jquery_so_approval.php` |
| DC approve / reject | admin, or a user of the DC's branch | `inc/cis_ajax/jquery_dc_approval.php` |
| Purchase return approve / reject | admin (the list is an admin-only menu) | `inc/cis_ajax/jquery_purchase_return_approval.php` |
| Sales repair send / approve | admin | `inc/cis_ajax/jquery_sales_repair_status.php` |
| Mark credit, confirm payment | see Task Settings - *corrected below: the screen had no check, every user confirms* | `jquery_so_pay_status.php`, `so_verify.php` |
| Supplier approve | admin | `mst_supplier_approve.php` |
| Credit SO approve | users of the credit-approval dashboard tile (admin) | `modal_so_det.php` |
| SO cancel | admin | `modal_so_reject_dets.php` |
| GRN cancel | admin or head office | `modal_grn_reject_dets.php` |
| Branch item update | admin at head office, or item-approval users | `branch_wise_item_update.php` |

Fixed on the way - these were broken before:

- **SO cancel, GRN cancel and credit SO approval never saved** since 2026-10-01. The modal forms post
  to themselves, the modal files had `session_start()` commented out, so the csrf check always
  failed. They now start the session when posted directly.
- **Ajax posts with their own `beforeSend`** (the approve buttons on PO, quotation and DC print, and
  others) sent no csrf token - the page's `beforeSend` replaced the global one. The token is now
  attached in `$.ajaxPrefilter`, which every ajax post goes through.
- **`$_SESSION['_userid']` is never set at login** (login sets `_user_id`). So DC verify-by, SO
  accounts verify-by, PO prepare / price history / GRN payment modified-by were saved empty, the
  admin Edit amount button on GRN payment never showed, and the admin price-required check on the
  item form never ran. Replaced by `_user_id`; admin now sees the GRN amount Edit button.
- Cancelled-by on SO and GRN, and approved-by on credit SO, were always saved as 1; now the user.

Checked on PHP 7.4 and 8 with users 1-7 calling each action: allowed exactly as listed above, a
post without the csrf token is refused; clicking the real buttons as an allowed user still works
(PO as admin, quotation and DC as Kerala, mark credit as accounts, credit SO approve as admin).

---

## 2026-10-12 - SPAC price list import

`db/import_spac_price_list_121026.sql` - run once in phpMyAdmin on the bie database. It adds the
4,702 items of `SPAC_PRICE_LIST_LP.xlsx` exactly as the item form would: principal Shree Prayag Air
Controls (SPAC), division Pneumatic, type Trading, all active branches, no multiple UOM, stock 0.

| Sheet column | Saved as |
|---|---|
| Category | category - AIR GUN, FRL, TUBING added; FITTING uses the existing FITTINGS |
| Description | item description, upper case |
| Supplier Item Code | supplier item code |
| Sales Item Code & Purchase Code | sales item code and purchase code |
| UOM | PCS = NOS, ROLL = ROLLS, SET = SET |
| HSN | 8 new codes added at 18% (9 + 9) |
| Item Price & selling price | item price and selling price |
| Item Discount | 0.28 = 28 %; cost = price less discount, margin from cost to selling (715 -> 514.80, 38.89 %) |
| SALE DISCOUNT | 0-10% = min 0, max 10 on every branch |

Row 442 (TG25-2331-08M) had `#N/A` for UOM and HSN: saved as NOT APPLICABLE and HSN NA (0 %).
P2S40X80 already exists as item 6626 under another principal; it is added again under SPAC.
A sales code already imported under SPAC is skipped, so a second run adds nothing.

Checked on a copy of the 12-10-26 live data, strict sql mode: 4,702 items and 4,702 stock rows
added, nothing on the second run; imported items open and update in the item form and come up in
the item search for every branch.

---

## 2026-10-12 - Item master: All branches, principal code, min / max discount

`mst_item_details.php`, new item and edit.

1. **Branch** has an **All** option. The branch picked on a new item is now saved - before,
   the dropdown was ignored and every item from a Head Office user was saved as branch 1.
   All saves every active branch (today `1,2,3`), the same value Branch Wise Item Update
   writes. Branch is required on a new item. On edit, an item mapped to every branch shows
   All; edit still does not change branches - that stays with Branch Wise Item Update.
   Items created by a non-Head-Office user still go to Item Approval as before.
2. **Principal** reads `Name - Code`, e.g. `Shree Prayag Air Controls Pvt Ltd - SPAC`.
3. **Min Discount / Max Discount** (from Item Price History) shown on a new item only,
   hidden on edit. Saved to every branch's min / max discount column in `tbl_item_stock`,
   alongside the price, discount and cost already written there. Blank saves 0. Min above
   max is refused.

No table changes.

Checked on PHP 7.4 and 8 against the 12-10-26 live data: new item with All saved
`branch_id 1,2,3`, with Kerala saved `2`; min 5 / max 30 landed in HO, KL and RJPM columns;
cost 514.80 from 715 less 28 %; min 40 / max 30 refused; edit shows All / Kerala and the
principal code, no discount fields, and Update still saves.

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
