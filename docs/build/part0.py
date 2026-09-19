# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 0  Before you start: how BIE is built")]
    b += [lead("Read this once. It explains the handful of patterns every screen follows - and one design "
               "choice that makes BIE genuinely different from anything else you may have seen.")]

    b += [h2("0.1  What BIE is")]
    b += [p("BIE is an in-house ERP for Benzear Industrial Enterprises: customers and suppliers, items and "
            "stock, quotations, sales orders, purchase, stores, invoicing, accounts, HR and payroll, and "
            "after-sales service.")]
    b += [p("It is a **classic PHP + MySQL web application** - no framework. Each screen is one `.php` file "
            "in the web root, and that file does everything for that screen: it takes the form post, writes "
            "to the database, then draws the page. That is why this documentation is organised by menu and "
            "by file - **the file is the screen**.")]
    b += [table([30, 70], ["Layer", "What it is"], [
        ["Language", "PHP (procedural), with `dbconnect` and `dbhandler` wrapping PDO"],
        ["Database", "MySQL / MariaDB - **105 tables**"],
        ["Front end", "Bootstrap 4 / Limitless theme, jQuery, DataTables. **Heavily modal-driven** - many "
         "screens open a `modal_*.php` fragment rather than a new page"],
        ["Branches", "**Three: Head Office (HO), Kerala (KL), Rajapalayam (RJPM).** See 0.3 - this shapes "
         "the whole system"],
        ["Menu", "Built from `mst_main_menu`, `mst_sub_menu` and `tbl_user_rights`"],
        ["Live screens", "**63** across **11 menus**"],
    ], bold_first=True)]

    b += [h2("0.2  The menu is data, not code")]
    b += [p("The sidebar is drawn at every page load from three tables, so two users can see two different "
            "systems.")]
    b += [table([26, 74], ["Table", "Role"], [
        ["`mst_main_menu`", "The top-level menus. `mm_show = 1` to appear, `mm_index` orders them"],
        ["`mst_sub_menu`", "The items inside each menu. `sm_url` is the file that opens, "
         "**`sm_show = 1` is required** or the item is hidden"],
        ["`tbl_user_rights`", "One row per (user, menu, sub-menu) a user may see"],
    ], bold_first=True)]
    b += [p("`inc/common/sidebar.php` branches on who you are:")]
    b += [code([
        "if ($_SESSION['_user_type'] == 'A')        // administrator",
        "    SELECT * FROM mst_sub_menu",
        "     WHERE mm_id = <menu> AND sm_show = 1        -- sees everything",
        "",
        "else                                        // everyone else",
        "    sm_ids = GROUP_CONCAT(sm_id) FROM tbl_user_rights",
        "              WHERE mm_id = <menu> AND usr_id = <this user>",
        "    SELECT * FROM mst_sub_menu",
        "     WHERE mm_id = <menu> AND sm_id IN (sm_ids) AND sm_show = 1",
    ])]
    b += [tip("**Administrators bypass rights entirely** - `_user_type = 'A'` sees every screen. Everyone "
              "else sees only what `tbl_user_rights` grants. Giving someone a screen is a data change, not "
              "a code change.")]
    b += [note("`mst_sub_menu` holds **71 rows but only 63 open anything**. Eight have `sm_url = NULL`; "
               "seven of those are hidden with `sm_show = 0`, and one - **Bank Account Details** under "
               "Settings - is set to show but has no URL, so it appears in the menu and goes nowhere. "
               "Set its `sm_show` to `0` to tidy the menu.")]

    b += [h2("0.3  The one thing that makes BIE different: branch columns")]
    b += [p("Most systems with multiple branches add a `branch_id` column and store one row per branch. "
            "**BIE does not.** It stores one row per item and gives each branch **its own set of "
            "columns**.")]
    b += [p("`tbl_item_stock` has 41 columns - one block per branch:")]
    b += [code([
        "tbl_item_stock",
        "  stock_id, item_id",
        "",
        "  ho_stock,   ho_loc_row,   ho_loc_rack,   ho_item_price,   ho_item_discount, ...",
        "  kl_stock,   kl_loc_row,   kl_loc_rack,   kl_item_price,   kl_item_discount, ...",
        "  rjpm_stock, rjpm_loc_row, rjpm_loc_rack, rjpm_item_price, rjpm_item_discount, ...",
        "",
        "  each block: stock, rack, row, price, discount, min/max discount,",
        "              cost price, selling price, margin, min/order/max quantity",
    ])]
    b += [p("So how does the code know which column to read? **`mst_branch` tells it.** That table's 57 "
            "columns are mostly not data - they are **column names**:")]
    b += [table([34, 66], ["`mst_branch` column", "Its value for Head Office"], [
        ["`branch_stock_field`", "`ho_stock`"],
        ["`branch_stock_location_rack_field`", "`ho_loc_rack`"],
        ["`branch_stock_location_row_field`", "`ho_loc_row`"],
        ["`branch_item_price`", "`ho_item_price`"],
        ["`branch_item_selling_price`", "`ho_item_selling_price`"],
        ["... and about forty more", "the same pattern, one per figure"],
    ], bold_first=True)]
    b += [p("The code then reads the name and uses it **as a column name** in the SQL it builds:")]
    b += [code([
        "$field_name = $dbconn->GetSingleReconrd(",
        "        \"mst_branch\", \"branch_stock_field\",",
        "        \"branch_id\", $_SESSION['_user_branch']);",
        "",
        "// $field_name is now the string 'ho_stock', and goes straight into the query",
        "SELECT ... , $field_name AS stock FROM tbl_item_stock ...",
    ])]
    b += [warn(["**Adding a fourth branch is a schema change, not a data entry job.** You would have to "
                "`ALTER TABLE tbl_item_stock` to add roughly thirteen new columns, then add an "
                "`mst_branch` row naming each of them.",
                "This has already happened once: `tbl_item_stock_22092023` is an older copy of the table "
                "with only the HO and KL blocks - Rajapalayam was added afterwards, and the repository "
                "still contains the `ALTER TABLE tbl_item_stock ADD` script that did it."])]
    b += [tip("**When a figure looks wrong for one branch but right for another**, this is why. The two "
              "branches are literally different columns, and a screen that hardcodes `ho_stock` instead of "
              "looking the name up will be right at Head Office and wrong everywhere else.")]

    b += [h2("0.4  Who you are, and which branch you are in")]
    b += [p("What `login` puts into the session, and which you will see all through the code:")]
    b += [table([26, 74], ["Session value", "Meaning"], [
        ["`_user_id`", "`tbl_user.usr_id` - **the audit trail**, written into `created_by`, `modify_by` and "
         "every approval column"],
        ["`_user_type`", "**`A` means administrator** - bypasses the menu rights check entirely"],
        ["`_user_branch`", "**The branch you are working in.** Decides which set of item columns every "
         "screen reads and writes"],
        ["`_user_name`", "Shown in the top bar"],
        ["`_msg`, `_msg_err`", "The green and red banners - set before a redirect, shown once, cleared"],
    ], bold_first=True)]
    b += [note("Benzear, the sister system, is multi-**company** and keys on `company_id`. BIE is "
               "multi-**branch** and keys on `branch_id`. If you move between the two codebases, this is "
               "the first thing that will trip you up.")]

    b += [h2("0.5  Five rules that hold nearly everywhere")]

    b += [h3("Rule 1  Nothing is really deleted")]
    b += [p("The dustbin icon sets a status column to `0`. Lists then filter on `... _status = 1`. It is "
            "done by one shared endpoint, `inc/cis_ajax/jquery_delete_records.php`, which is handed the "
            "table, the column and the key:")]
    b += [code(["UPDATE <table> SET <status column> = <value> WHERE <key column> = '<id>'"])]

    b += [h3("Rule 2  Customers and suppliers share one table")]
    b += [p("There is no customer table. Both live in **`mst_supplier_new`**, told apart by `supp_type`. "
            "Suppliers additionally need approving before purchase screens will offer them.")]
    b += [note("Note the table name: **`mst_supplier_new`**, not `mst_supplier`. An older `mst_supplier` "
               "may still exist in the database; the live screens use the `_new` one.")]

    b += [h3("Rule 3  A party or an employee is also a ledger")]
    b += [p("You never create an accounts ledger by hand for a customer, supplier or employee. The master "
            "screen inserts into **`mst_ledger`** first, takes the new `ledger_id`, and stores it on the "
            "master row. From then on every transaction for that party posts against that ledger.")]

    b += [h3("Rule 4  Documents with lines use a `_temp` staging table")]
    b += [p("While you are still typing a document there is no header id to attach lines to, so the lines "
            "are staged in a matching `*_temp` table tagged with your session. On save the header is "
            "written, the staged rows are copied under the new id, and the staging rows are cleared.")]
    b += [note("Abandoning a half-typed document leaves orphan rows in the `_temp` tables. They are "
               "filtered by session and never read again, but they accumulate - clearing old ones is safe "
               "housekeeping.")]

    b += [h3("Rule 5  Most forms write themselves")]
    b += [p("Very few screens contain a hand-written `INSERT` or `UPDATE`. Instead they call two helpers in "
            "`inc/common/functions.php`:")]
    b += [code([
        "$sql = CIS_InsertRecord(\"tbl_user\", $_REQUEST);   // build the INSERT",
        "$conn->query($sql);",
        "",
        "$sql = CIS_UpdateRecord(\"tbl_user\", $_REQUEST);   // build the UPDATE",
    ])]
    b += [p("Both helpers `DESCRIBE` the table, then keep only those posted values whose **name matches a "
            "real column**, and silently drop the rest. So in BIE:")]
    b += [bul([
        "**The name of the input box on the form is the name of the column in the table.** If you see "
        "`name=\"usr_email\"` on the form, it lands in `tbl_user.usr_email`.",
        "Adding a column to the table and an input with the same name is all it takes to store a new "
        "field - no code change.",
        "A typo in a field name does not error. The value is simply dropped and the field stays empty.",
    ])]
    b += [warn("Because the helpers take **the whole `$_REQUEST`**, anything posted that happens to match a "
               "column name is written - including columns the form was not meant to expose. When you add a "
               "column, check that no screen posting to that table could set it by accident.")]

    b += [h2("0.6  How to read the screen sections")]
    b += [table([30, 70], ["Heading", "What it tells you"], [
        ["The grey box", "Menu path, the file or files, and what kind of screen it is"],
        ["**What it does**", "Plain language. Read this if you are a user"],
        ["**Tables**", "Every table touched, and what for"],
        ["**Flow**", "What you do, in order"],
        ["**What changes**", "The exact inserts and updates"],
        ["**Shows up in**", "Which other screens change once this one is saved"],
        ["Coloured boxes", "Green = useful. Amber = note. Red = a real trap - read it"],
    ])]

    return {"heading": "Part 0", "blocks": b}
