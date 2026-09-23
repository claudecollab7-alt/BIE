# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 1  Settings")]
    b += [lead("Three screens, and they decide everything else: who may log in, which branches exist, and "
               "which suppliers get a discount. Only an administrator can open them.")]
    b += [table([26, 74], ["Screen", "In one line"], [
        ["User", "Create logins, set the branch a user belongs to, and tick which screens they may see"],
        ["Branch", "The list of branches - and **the names of the item columns each branch uses**"],
        ["Common Settings", "Tick the suppliers whose discount should be applied automatically"],
    ], bold_first=True)]
    b += [note("A fourth item, **Bank Account Details**, appears in this menu but has no file behind it. "
               "Clicking it goes nowhere. See Part 0, section 0.2.")]

    # ------------------------------------------------------------------ 1.1
    b += form2(
        "1.1", "User",
        [("Menu", "Settings > User"),
         ("Files", "`mst_user.php` (list) - `mst_users_add.php` (add / edit) - `mst_users_rights.php` (rights)"),
         ("Type", "Master, with a second screen for permissions")],
        ["Every person who logs into BIE has a row here. The row carries their login and password, the "
         "**branch** they work in, and whether they are an administrator.",
         "Rights are set on a separate screen reached from the key icon on the list. Administrators do not "
         "need rights - they see every screen regardless."],
        [["`tbl_user`", "The login itself - name, email, mobile, login name, password, type, branch"],
         ["`tbl_user_rights`", "One row for every screen this user may open. Written only by the rights screen"],
         ["`mst_main_menu`, `mst_sub_menu`", "Read, to draw the tick-box tree on the rights screen"],
         ["`mst_branch`", "Read, to fill the branch dropdown"]],
        ["Open **Settings > User**. The list shows active users only (`usr_status = 1`).",
         "**Add New** opens `mst_users_add.php` with an empty form.",
         "Fill in name, email, mobile, login name and password, pick the **branch**, pick the **user type**, "
         "and optionally upload a photo.",
         "Save. The screen first checks that no other active user already has that email; if one does you "
         "are bounced back with *User details already exist*.",
         "The password is hashed and the row is inserted. The photo, if any, is written to "
         "`project_img/usr_photo/`.",
         "Back on the list, click the **rights** icon to open `mst_users_rights.php`. You get every menu "
         "with its screens as tick boxes, already ticked to match what the user has now.",
         "Tick and untick, then Update. Their sidebar changes the next time they load a page."],
        [["INSERT", "`tbl_user`", "One row. `usr_logpwd` is hashed; `branch_id` fixes the branch; "
          "`usr_status` defaults to `1`"],
         ["UPDATE", "`tbl_user`", "The same row on edit. If no new photo is chosen the old file name is kept"],
         ["DELETE", "`tbl_user_rights`", "**A hard delete of every row for this user**, done first, every "
          "time the rights screen is saved"],
         ["INSERT", "`tbl_user_rights`", "One row per ticked screen - `(usr_id, mm_id, sm_id)` - inserted "
          "straight after the delete"],
         ["Soft delete", "`tbl_user`", "The dustbin sets `usr_status = 0`. The login stops working and the "
          "row leaves the list, but the rights rows stay behind"]],
        ["The user's own sidebar - immediately, because it is rebuilt from `tbl_user_rights` on every page load",
         "The login screen - only an active user with a matching hash gets in",
         "`created_by` and `modify_by` on every record they touch afterwards",
         "Every branch-aware screen, which reads their `branch_id` from the session to pick the right item "
         "columns"],
        fields=[
            ["`usr_type`", "**`A` = administrator.** An `A` user bypasses `tbl_user_rights` completely and "
             "sees every screen. Anything else is a normal user"],
            ["`branch_id`", "Which branch this user works in. Copied into the session at login and used by "
             "every stock and pricing screen"],
            ["`usr_logpwd`", "The hashed password - `md5(password + fixed salt)`"],
            ["`pw_hint`", "**The password again, in plain text.** See the warning below"],
            ["`usr_email`", "Must be unique among active users. The check is on email, not on login name"],
            ["`usr_status`", "`1` active, `0` deleted"],
        ],
        notes=[
            warn("**The password is stored twice - once hashed, once in clear.** `mst_users_add.php` does "
                 "`$_REQUEST['pw_hint'] = $_REQUEST['usr_logpwd'];` before hashing, so `pw_hint` keeps the "
                 "typed password as typed. Anyone who can read `tbl_user` can read every password. The hash "
                 "itself is `md5()` with one shared salt, which is also too weak to rely on. Treat this "
                 "table as highly sensitive and fix it when you can."),
            warn("**Rights are rebuilt, not amended.** Saving the rights screen deletes every row for that "
                 "user before inserting the ticked ones. If the page loads with some boxes not yet drawn, "
                 "or you save while the tree is still loading, the user loses the rights that were not "
                 "ticked. There is no undo - re-tick and save again."),
            note("**Uniqueness is checked on email only.** Two users can end up with the same "
                 "`usr_logname`. If that happens, whichever row the login query returns first wins. Keep "
                 "login names unique by hand."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 1.2
    b += form2(
        "1.2", "Branch",
        [("Menu", "Settings > Branch"),
         ("Files", "`mst_branch.php` (list and form in one file)"),
         ("Type", "Master - **and the most important table in the system**")],
        ["This is the list of branches: Head Office, Kerala, Rajapalayam. Each row also carries that "
         "branch's letterhead - address, phone numbers, email, GST number, PAN - which is what gets printed "
         "on that branch's quotations and invoices.",
         "It also carries something far less obvious. Most of the row is not data at all: it is **the names "
         "of the columns in `tbl_item_stock` that belong to this branch**. See Part 0, section 0.3 - this is "
         "the table that makes that design work."],
        [["`mst_branch`", "The branch row: name, code, state code, printing details, and the fifty column "
          "names described below"],
         ["`tbl_item_stock`", "Not touched here, but every column name stored here points into it"]],
        ["Open **Settings > Branch**. Three rows, one per branch.",
         "**Add New** or the pencil icon opens the same page with the form filled.",
         "The form asks for only four things: **branch name, branch code, state code and address.**",
         "Save or Update. That is all the screen writes.",
         "The letterhead fields and all the column-name fields are **not on this form.** They were loaded "
         "when the database was built and are edited directly in the database when a branch is added."],
        [["INSERT", "`mst_branch`", "One row with `branch_name`, `branch_code`, `branch_state_code`, "
          "`company_address`, `created_by`, `created_dtm`. **Every column-name field is left empty**"],
         ["UPDATE", "`mst_branch`", "The same four fields plus `modify_by` and `modify_dtm`"],
         ["Soft delete", "`mst_branch`", "Sets `branch_status = 0`"]],
        ["The top of every print - quotation, sales order, purchase order, GRN, invoice - which reads the "
         "logged-in user's branch row for the letterhead",
         "The branch dropdown on the user screen",
         "**Every stock figure in the system**, indirectly: screens read `branch_stock_field` from this row "
         "and then build their SQL around whatever column name they find"],
        fields=[
            ["`branch_code`", "`HO`, `KL`, `RJPM`. Short, and used as a prefix in document numbers"],
            ["`branch_stock_field`", "**The name of the stock column for this branch** - `ho_stock`, "
             "`kl_stock`, `rjpm_stock`. The single most-read value in the system"],
            ["`branch_stock_location_rack_field`, `..._row_field`", "The rack and row column names for this "
             "branch's bin locations"],
            ["`branch_item_price`, `branch_item_discount`, `branch_item_cost_price`, `branch_item_selling_price`, "
             "`branch_item_margin`, `branch_item_msq` / `maq` / `moq`", "The **current** pricing column names "
             "for this branch"],
            ["`branch_new_*` (13 fields)", "The **proposed** pricing column names - where a price change is "
             "parked until it is approved"],
            ["`branch_old_*` (13 fields)", "The **previous** pricing column names - where the superseded "
             "price is kept"],
            ["`company_gst`, `company_pan`, `company_state_code`", "Printed on documents; the state code "
             "decides whether tax comes out as CGST + SGST or as IGST"],
        ],
        notes=[
            tip("Read the row for Head Office once, side by side with `tbl_item_stock`, and the whole design "
                "clicks into place. `branch_stock_field = 'ho_stock'`, and `tbl_item_stock` has a column "
                "called `ho_stock`. Every branch-aware query in BIE is that substitution."),
            warn("**Adding a branch through this screen will not work.** The form saves four fields and "
                 "leaves all fifty column-name fields empty, so the new branch has no stock column, no "
                 "price column and no location columns. Adding a branch properly means: `ALTER TABLE "
                 "tbl_item_stock` to add the new block of columns, then set all fifty names on the new "
                 "`mst_branch` row by hand. The repository still contains the `ALTER TABLE` script that "
                 "added Rajapalayam - use it as the pattern."),
            warn("**Check Rajapalayam's tax details.** Its `branch_state_code` is `33` (Tamil Nadu) but its "
                 "`company_state_code` is `32` and its `company_gst` is Kerala's number. Those two fields "
                 "look copied from the Kerala row. Because the state code decides CGST/SGST versus IGST, "
                 "this can put the wrong tax on a Rajapalayam document. Verify against the real "
                 "registration before trusting a Rajapalayam print."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 1.3
    b += form2(
        "1.3", "Common Settings",
        [("Menu", "Settings > Common Settings"),
         ("Files", "`mst_common_settings.php`"),
         ("Type", "A single switch board - one tick box per supplier")],
        ["One question, asked once: **for which suppliers should the item discount be applied "
         "automatically?** You get the list of approved suppliers with a tick box each. Tick them, Update, "
         "done.",
         "When a supplier is ticked, picking one of their items on a quotation or an invoice brings the "
         "discount in by itself. When they are not ticked, the discount column stays at zero and the person "
         "typing has to enter it."],
        [["`mst_supplier_new`", "Read for the supplier list; **written** - the `discount_apply` flag on each "
          "supplier row"]],
        ["Open **Settings > Common Settings**. Every active supplier (`supp_type = 'S'`) is listed with a "
         "tick box, already ticked if `discount_apply` is `1`.",
         "Tick and untick as you want.",
         "Update. The screen **clears the flag on every supplier first**, then sets it to `1` on the ones "
         "you ticked.",
         "The change is live at once - the next item you pick on a quotation behaves the new way."],
        [["UPDATE", "`mst_supplier_new`", "`SET discount_apply = 0 WHERE supp_type = 'S' AND supp_status = 1` "
          "- **clears the flag on every active supplier**"],
         ["UPDATE", "`mst_supplier_new`", "`SET discount_apply = 1 WHERE supp_id IN (...)` - only the "
          "suppliers you ticked"]],
        ["**Quotation** - the discount fills in by itself for a ticked supplier's items",
         "**Invoice** - the same",
         "The item-picker endpoint `jquery_select_item.php`, which returns the flag along with the item so "
         "the screen knows whether to apply the discount"],
        notes=[
            note("There is no `mst_common_settings` table. Despite its name the screen is only a different "
                 "way of editing one column on `mst_supplier_new`. If you go looking for a settings table, "
                 "you will not find one."),
            warn("**Untick everything and Update, and every supplier loses the flag.** The screen always "
                 "runs the clear-all statement first, so saving an empty page is the same as switching "
                 "automatic discounts off for the whole company. Only the suppliers visible and ticked at "
                 "the moment you press Update keep the flag."),
        ])

    return {"heading": "Part 1", "blocks": b}
