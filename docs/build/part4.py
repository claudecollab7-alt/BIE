# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 4  Item Masters")]
    b += [lead("Ten screens. Seven are one-field lists. The other three are where the branch-column design "
               "from Part 0 stops being an idea and becomes code you can read.")]
    b += [table([26, 74], ["Screen", "In one line"], [
        ["Principal / Division / Category / Brand / UOM / Colour", "Six one-field lists that classify an item"],
        ["HSN", "The tax code - **and the CGST, SGST and IGST rates that go with it**"],
        ["Item Details", "The item record. Creating one also creates its stock row for **every** branch"],
        ["Item Grouping", "A kit: one group, many items, each with a quantity"],
        ["Item Price History", "Every price change ever made, with the old and new figures side by side"],
    ], bold_first=True)]

    # ------------------------------------------------------------------ 4.1
    b += [h2("4.1  Two tables, and why both exist")]
    b += [p("Before the screens, the thing that confuses everybody. An item lives in **two** tables.")]
    b += [table([26, 74], ["Table", "What it holds"], [
        ["`tbl_item_details`", "**What the item is.** Code, description, type, category, brand, colour, "
         "UOM, HSN, supplier, image. One row per item"],
        ["`tbl_item_stock`", "**What the item costs and how many there are** - per branch. One row per "
         "item, and a block of columns for each branch"],
    ], bold_first=True)]
    b += [p("The catch is that `tbl_item_details` **also** has `item_price`, `item_cost_price`, "
            "`item_curr_stock`, `item_min_qty` and the rest. Those are the original, single-value columns "
            "from before BIE had branches. They are written once when the item is created and then left "
            "behind.")]
    b += [warn("**The price and stock columns on `tbl_item_details` are stale. Do not read them.** The live "
               "figures are the per-branch columns on `tbl_item_stock`. In `grn_add.php` you can see the "
               "changeover: the block that would have updated `tbl_item_details.item_curr_stock` is still "
               "there, **commented out**, right below the block that updates the branch column. If a stock "
               "figure in a query looks frozen at whatever it was on the day the item was created, this is "
               "why.")]
    b += [p("What makes this hard to spot is that the code keeps the **old names**. Screens do not select "
            "`ho_stock`; they select it under an alias:")]
    b += [code([
        "-- inc/cis_ajax/jquery_get_item_details.php",
        "SELECT ho_item_price      AS item_price,",
        "       ho_item_cost_price AS item_cost_price,",
        "       ho_stock           AS item_curr_stock     -- the live figure",
        "  FROM tbl_item_stock",
        " WHERE item_id = ...",
    ])]
    b += [tip("So when you see `item_curr_stock` in BIE, ask **which table**. Out of `tbl_item_stock` it is "
              "this branch's live stock. Out of `tbl_item_details` it is a fossil.")]
    b += [pb()]

    # ------------------------------------------------------------------ 4.2 lookups
    b += [h2("4.2  The classification lists")]
    b += [p("Six screens with one field each (a couple have two), all built the same way: a list, a form "
            "on the same page, a pencil to edit, a dustbin that sets the status column to `0`. Together "
            "they are the dropdowns at the top of the item form.")]

    b += mini("4.2.1", "Principal", "`mst_principal.php`",
              "The principal - the manufacturer or brand owner whose products these are.",
              "`mst_principal`", "`principal_id`, `principal_name`, `principal_code`, `principal_status`",
              "**Item Details** - the Principal dropdown. A filter on the item list and on stock reports")
    b += [sp()]
    b += mini("4.2.2", "Division", "`mst_division.php`",
              "The product division an item belongs to.",
              "`mst_division`", "`division_id`, `division_name`, `division_code`, `division_status`, audit pair",
              "**Item Details** - the Division dropdown. Grouping on sales and stock reports")
    b += [sp()]
    b += mini("4.2.3", "Category", "`mst_category.php`",
              "The item category. Carries an `item_type_id` as well, so a category can be tied to a kind of item.",
              "`mst_category`",
              "`category_id`, `item_type_id`, `category_name`, `category_code`, `category_status`, audit pair",
              "**Item Details** - the Category dropdown. A filter on the item list, the stock reports and "
              "the cost worksheet")
    b += [sp()]
    b += mini("4.2.4", "Brand / Make", "`mst_brand.php`",
              "The brand or make.",
              "`mst_brand`", "`brand_id`, `brand_name`, `brand_code`, `brand_status`, audit pair",
              "**Item Details** - the Brand / Make dropdown. Printed on quotations and invoices")
    b += [sp()]
    b += mini("4.2.5", "Unit of Measurement (UOM)", "`mst_uom.php`",
              "Nos, Kg, Metre, Litre and so on. `uom_code` is the short form that prints on documents.",
              "`mst_uom`", "`uom_id`, `uom_name`, `uom_code` (5 characters), `uom_status`, audit pair",
              "**Item Details**; **Item Grouping**; and the UOM column on every quotation, sales order, PO, "
              "GRN, delivery challan and invoice line")
    b += [sp()]
    b += mini("4.2.6", "Colour", "`mst_color.php`",
              "The colour list.",
              "`mst_color`", "`color_id`, `color_name`, `color_code`, `color_status`, audit pair",
              "**Item Details** - the Colour dropdown")
    b += [sp()]
    b += [note("Every one of these has both a **name** and a **code**. The code is the short form; the item "
               "form shows the name, and prints tend to use the code. Neither is checked for uniqueness, so "
               "two categories can share a code - keep them distinct by hand.")]
    b += [pb()]

    # ------------------------------------------------------------------ 4.3 HSN
    b += form2(
        "4.3", "HSN",
        [("Menu", "Item Masters > HSN"),
         ("Files", "`mst_hsn.php` (list and form on one page)"),
         ("Type", "Lookup - **but it sets the tax**")],
        ["The HSN code is how the tax authority classifies goods. In BIE the HSN row carries more than the "
         "code: it carries **the three GST rates** that apply to it - CGST, SGST and IGST.",
         "That makes this the one screen in Item Masters with money riding on it. Change a rate here and "
         "every invoice raised afterwards for every item on that HSN charges the new rate."],
        [["`mst_hsn`", "The code, a description, and the three rates"]],
        ["Open **Item Masters > HSN**. Live codes listed with their rates.",
         "Type the **HSN code**, a **description**, and the **CGST**, **SGST** and **IGST** percentages.",
         "Save. The pencil loads a row back for correction.",
         "On the item form, the HSN dropdown now offers it. Choose it and the item is taxed at these rates.",
         "The dustbin sets `hsn_status = 0`."],
        [["INSERT", "`mst_hsn`", "`hsn_type`, `hsn_code`, `hsn_description`, `cgst`, `sgst`, `igst`"],
         ["UPDATE", "`mst_hsn`", "The same fields"],
         ["Soft delete", "`mst_hsn`", "Sets `hsn_status = 0`"]],
        ["**Item Details** - the HSN dropdown, and `tbl_item_details.item_hsn`",
         "**Quotation**, **Sales Order** and **Invoice** - the tax on every line",
         "**Purchase Order** and **GRN** - the tax on the buying side",
         "**Item Price History** - a price change records the HSN in force at the time"],
        fields=[
            ["`hsn_code`", "The HSN or SAC code itself, up to 10 characters"],
            ["`hsn_type`", "Three characters distinguishing an HSN (goods) from a SAC (services)"],
            ["`cgst`, `sgst`", "The intra-state pair. Used when the customer's state matches the branch's"],
            ["`igst`", "The inter-state rate. Used when the states differ. Normally `cgst + sgst`"],
            ["`hsn_status`", "`1` active, `0` deleted"],
        ],
        notes=[
            warn("**Changing a rate here changes it everywhere, including on reprints.** Documents store "
                 "the HSN id and look the rates up when they print, so reprinting last year's invoice after "
                 "a rate change shows the **new** rate against the **old** total - the two will not agree. "
                 "When a rate genuinely changes, add a new HSN row and move items onto it, rather than "
                 "editing the existing one."),
            note("**Whether CGST + SGST or IGST applies is decided elsewhere** - by comparing the "
                 "customer's `state_id` with the branch's `company_state_code`. This screen only supplies "
                 "the numbers. See the Rajapalayam warning in section 1.2: if a branch's state code is "
                 "wrong, the right rate is picked from the wrong column."),
            note("`cgst`, `sgst` and `igst` are `decimal(6,2)`, so 9 means 9.00 per cent. Enter the "
                 "percentage, not the fraction."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 4.4 Item Details
    b += form2(
        "4.4", "Item Details",
        [("Menu", "Item Masters > Item Details"),
         ("Files", "`lst_item_details.php` (list) - `mst_item_details.php` (add / edit, 1,000 lines) - "
                   "`inc/datatable/ajaxItemDetailsList.php` - `inc/cis_ajax/modal_items_dets.php` (view) - "
                   "`inc/cis_ajax/modal_update_items.php` (price and history modal) - "
                   "`inc/cis_ajax/jquery_modal_supp_map.php` (supplier mapping)"),
         ("Type", "Master - **and the screen that creates branch stock rows**")],
        ["The item record: code, description, type, the six classifications, UOM, HSN, supplier, image, and "
         "the opening prices and quantities.",
         "What is not obvious is what saving does. Alongside the `tbl_item_details` row it **creates the "
         "item's `tbl_item_stock` row and fills in the pricing columns for every branch at once** - by "
         "reading the column names out of `mst_branch` and building the SQL from them. This is the clearest "
         "place in the whole codebase to see how branches actually work."],
        [["`tbl_item_details`", "The item itself - what it is"],
         ["`tbl_item_stock`", "**Created here.** One row per item, holding every branch's prices and stock"],
         ["`mst_branch`", "**Read in a loop** - to get each branch's column names"],
         ["`mst_principal`, `mst_division`, `mst_category`, `mst_brand`, `mst_uom`, `mst_hsn`, `mst_color`",
          "Read, for the dropdowns"],
         ["`mst_supplier_new`", "Read - the supplier this item is bought from"],
         ["`tbl_item_group`", "Read - if the item belongs to a group"]],
        ["Open **Item Masters > Item Details**. Active items, filterable by category, brand, principal and "
         "branch.",
         "**Add New** opens `mst_item_details.php`.",
         "Enter the **item code** (you type it - it is not generated) and the description.",
         "Choose the **item type**: Consumable, Group Products, Raw Materials, Trading or Labour Charges.",
         "Pick principal, division, category, brand, colour, **UOM** and **HSN**. Pick the supplier.",
         "Enter the opening figures: price, discount, cost price, selling price, margin, and the minimum, "
         "maximum and re-order quantities.",
         "Upload an image if you have one - it is renamed after the item code and stored under "
         "`project_img/item_image/`.",
         "Save. The item row goes in; then an empty `tbl_item_stock` row; then **the branch loop** "
         "described below fills in the prices for every branch.",
         "Back on the list, three action icons matter: **view** (a read-only modal), **Item Price Update** "
         "(`mst_itemprice_history.php` - section 4.6), and **Branch Wise Item Update** "
         "(`branch_wise_item_update.php` - which branches may use this item)."],
        [["INSERT", "`tbl_item_details`", "The item. `branch_id` and `branch_status` are set from **who you "
          "are** - see the warning below"],
         ["INSERT", "`tbl_item_stock`", "`INSERT INTO tbl_item_stock (item_id) VALUES (...)` - a bare row. "
          "**All stock quantities start at zero**"],
         ["UPDATE", "`tbl_item_stock`", "**Once per branch**, in a loop over `mst_branch`, writing the eight "
          "pricing columns whose names that branch's row supplies"],
         ["UPDATE", "`tbl_item_details`", "On edit - the descriptive fields only"],
         ["UPDATE", "`tbl_item_details`", "From **Branch Wise Item Update** - `branch_id` (a comma-separated "
          "list) and `branch_status = 1`"],
         ["Soft delete", "`tbl_item_details`", "Sets `item_status = 0`. The `tbl_item_stock` row stays"]],
        ["**Quotation**, **Sales Order**, **Invoice** - the item picker, which returns this branch's price "
         "and stock",
         "**Purchase Order**, **GRN**, **Store Indent** - the same picker on the buying side",
         "**Item Grouping** - items you can put in a kit",
         "**Item Price History** - every change made afterwards",
         "**All the stock reports** - Store Stock, Branch Wise Stock, Item Stock History, Cost Worksheet"],
        fields=[
            ["`item_code`", "**Typed by you, not generated.** Nothing checks it for uniqueness"],
            ["`item_type`", "`6` Consumable, `7` Group Products, `3` Raw Materials, `2` Trading, `8` Labour "
             "Charges"],
            ["`branch_id`", "**A `varchar` holding a comma-separated list** of the branches this item is "
             "released to - `'1,3'` means Head Office and Rajapalayam"],
            ["`branch_status`", "`1` means the item is released and will appear in branch dropdowns; `0` "
             "means it is not"],
            ["`item_uom`, `item_hsn`", "Point at `mst_uom` and `mst_hsn`. The HSN carries the tax rates"],
            ["`item_price` and friends", "Written once at creation and then **stale** - see section 4.1"],
            ["`item_status`", "`1` active, `0` deleted"],
        ],
        extra=[
            h4("The branch loop, in full"),
            p("This is worth reading once, slowly. It is eight lines, and it is the whole design:"),
            code([
                "// mst_item_details.php",
                "INSERT INTO tbl_item_stock (item_id) VALUES ('<new item>');",
                "$stock_id = lastInsertId();",
                "",
                "SELECT * FROM mst_branch;                 // one row per branch",
                "foreach (branch as $value) {",
                "    UPDATE tbl_item_stock",
                "       SET {$value['branch_item_price']}        = :price,   // 'ho_item_price'",
                "           {$value['branch_item_discount']}     = :disc,    // 'ho_item_discount'",
                "           {$value['branch_item_cost_price']}   = :cost,",
                "           {$value['branch_item_selling_price']}= :sell,",
                "           {$value['branch_item_margin']}       = :margin,",
                "           {$value['branch_item_msq']}          = :min_qty,",
                "           {$value['branch_item_maq']}          = :max_qty,",
                "           {$value['branch_item_moq']}          = :order_qty",
                "     WHERE stock_id = :stock_id;",
                "}",
            ]),
            p("The column names are **not in the code**. They come out of the branch row and are "
              "concatenated into the statement. Add a branch to `mst_branch` with its column names filled "
              "in, and this loop writes its prices too - no code change. That is the payoff for the design. "
              "The price for it is everything in the warnings below."),
        ],
        notes=[
            warn("**Every branch gets the same opening price.** The loop writes the one set of figures you "
                 "typed into all three branches' columns. There is no per-branch price on the add form. To "
                 "give a branch a different price you must afterwards use **Item Price Update** "
                 "(section 4.6), which works on one branch at a time."),
            warn("**An item created anywhere but Head Office is born invisible.** The code reads your "
                 "branch and sets `branch_id = 1, branch_status = 1` if you are at Head Office, and "
                 "`branch_id = 0, branch_status = 0` otherwise. So a Kerala user can create an item and "
                 "then not find it in any dropdown - including their own. Someone must open **Branch Wise "
                 "Item Update** and tick the branches. If new items 'do not appear', check `branch_status` "
                 "before anything else."),
            warn("**Editing an item never touches `tbl_item_stock`.** The whole of the branch loop is "
                 "inside the SAVE path; the UPDATE path writes only `tbl_item_details`. So correcting a "
                 "price by editing the item does nothing at all - the figures on screen change, the ones "
                 "the system uses do not. **Prices are changed only through Item Price Update.**"),
            note("The branch loop reads `SELECT * FROM mst_branch` with **no status filter**, so a "
                 "soft-deleted branch still gets its columns written. Harmless - nothing reads them - but "
                 "worth knowing if a column you expected to be untouched has a value in it."),
            note("An item with no `tbl_item_stock` row - imported directly into the database, say - has no "
                 "price and no stock anywhere, and the stock screens will show it as blank rather than "
                 "zero. Create items through this screen so the stock row is made for you."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 4.5 grouping
    b += form2(
        "4.5", "Item Grouping",
        [("Menu", "Item Masters > Item Grouping"),
         ("Files", "`lst_item_grouping.php` (list) - `mst_item_grouping.php` (add / edit) - "
                   "`inc/datatable/ajaxItemGroupingList.php` - `inc/cis_ajax/modal_item_group_det.php` (view)"),
         ("Type", "Master with child lines - a kit or bill of materials")],
        ["A group is a named set of items with a quantity each - a kit. Sell or quote the group and all its "
         "parts come with it, in the right proportions.",
         "The lines are staged in a `_temp` table while you build the group, exactly as described in Rule 4 "
         "of Part 0, and copied under the new group id when you save."],
        [["`tbl_item_group`", "The group header - name, code, serial, UOM, remarks"],
         ["`tbl_item_group_details`", "One row per item in the group, with its quantity and position"],
         ["`tbl_item_group_details_temp`", "Session-tagged staging while you are still adding lines"],
         ["`tbl_item_details`", "Read - the items you can add"],
         ["`mst_uom`", "Read - the group's own unit of measure"]],
        ["Open **Item Masters > Item Grouping**. Live groups listed with how many items each holds.",
         "**Add New** opens `mst_item_grouping.php`. The **group serial** is worked out as "
         "`max(item_group_slno) + 1`.",
         "Name the group, give it a code, pick its **UOM**, and add remarks.",
         "Add items one at a time: pick the item, enter the **quantity**. Each line goes into "
         "`tbl_item_group_details_temp` against your session.",
         "Save. The header is written, the staged lines are copied across under the new `item_group_id`, "
         "and the staging rows are cleared.",
         "On edit the existing detail rows are **deleted and rebuilt** from what is on the form."],
        [["INSERT", "`tbl_item_group_details_temp`", "One row per line as you add it, tagged with your "
          "session"],
         ["INSERT", "`tbl_item_group`", "The header, with `item_group_slno = max + 1`"],
         ["DELETE", "`tbl_item_group_details`", "Any existing lines for this group"],
         ["INSERT", "`tbl_item_group_details`", "The staged lines, now under the real `item_group_id`"],
         ["DELETE", "`tbl_item_group_details_temp`", "Your session's staging rows"],
         ["UPDATE", "`tbl_item_group`", "The header on edit"]],
        ["**Item Details** - an item whose type is *Group Products* points at a group",
         "**Quotation** and **Sales Order** - picking a group brings its parts in",
         "Stock reports - the parts move, not the group"],
        fields=[
            ["`item_group_slno`", "A running number, `max + 1`. Same race as every other `max + 1` code in "
             "BIE - two simultaneous saves get the same number"],
            ["`item_group_code`", "The short code for the group"],
            ["`uom_id`", "The group's own unit. Each line keeps the item's own UOM"],
            ["`item_qty`", "How many of that item make up one group"],
            ["`item_position`", "The order the lines print in"],
            ["`company_id`", "Another leftover from the Benzear fork. Nothing in BIE reads it"],
        ],
        notes=[
            note("**A group is a recipe, not stock.** There is no stock figure for a group and nothing "
                 "reserves the parts. Selling a group moves each part's own quantity; if one part is short, "
                 "nothing warns you when the group is chosen."),
            note("Abandoning a half-built group leaves rows in `tbl_item_group_details_temp`. They are "
                 "filtered by session and never read again, but they do accumulate - clearing old ones is "
                 "safe."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 4.6 price history
    b += form2(
        "4.6", "Item Price History",
        [("Menu", "Item Masters > Item Price History"),
         ("Files", "`mst_itemprice_history_list.php` (list) - `mst_itemprice_history.php` (the price change "
                   "itself, 1,000 lines) - `inc/datatable/ajaxItemPriceHistoryList.php` - "
                   "`inc/cis_ajax/modal_update_items.php` (the last ten changes, in a modal)"),
         ("Type", "Log - and **the only screen that can change a price**")],
        ["Two things wear one name here. The menu item is a **log**: every price change ever made, with the "
         "old and the new figures side by side. The screen behind it, reached from the Item Details list, is "
         "**where a price is actually changed**.",
         "This matters because, as section 4.4 explains, editing an item does not change its price. This is "
         "the only route.",
         "A change works on **one branch at a time** - your branch. The screen reads your branch's column "
         "names out of `mst_branch`, shows you the current figures, takes the new ones, writes a history "
         "row holding both, and then overwrites the current columns."],
        [["`tbl_itemprice_history`", "**One row per change**, per branch. Holds `..._new_...` and "
          "`..._old_...` columns for every figure - itself organised by branch, exactly like "
          "`tbl_item_stock`"],
         ["`tbl_item_stock`", "**Overwritten** with the new figures, in your branch's columns"],
         ["`tbl_item_details`", "Updated - but only `item_uom` and `item_hsn`"],
         ["`tbl_multiuom_itemprice_history`", "The same, for items priced in more than one unit"],
         ["`mst_branch`", "Read - your branch's column names"],
         ["`mst_uom`, `mst_hsn`", "Read - a price change can also change the unit and the tax code"]],
        ["Open the **Item Details** list and click the **Item Price Update** icon on the item's row.",
         "The screen loads **your branch's** current figures - price, discount, minimum and maximum "
         "discount, cost price, selling price, margin, MSQ, MAQ, MOQ - by reading the column names from "
         "`mst_branch` and selecting them under generic aliases.",
         "Enter the **new** figures. You may also change the **UOM** and the **HSN** here.",
         "Save. One row goes into `tbl_itemprice_history` carrying **both** sets of figures: the new ones "
         "in that branch's `..._new_...` columns and the ones being replaced in its `..._old_...` columns.",
         "`tbl_item_details.item_uom` and `item_hsn` are updated.",
         "Then `tbl_item_stock` is updated - your branch's current columns are overwritten with the new "
         "figures.",
         "To see the history, open **Item Masters > Item Price History** for the full log, or the price "
         "modal on the item list for that item's last ten changes."],
        [["INSERT", "`tbl_itemprice_history`", "One row: `item_id`, `branch_id`, the new figures in "
          "`<branch>_new_*`, the superseded ones in `<branch>_old_*`, plus the UOM and HSN before and after"],
         ["UPDATE", "`tbl_item_details`", "`item_uom` and `item_hsn` only"],
         ["UPDATE", "`tbl_item_stock`", "Your branch's `<branch>_item_price`, `_discount`, `_min_discount`, "
          "`_max_discount`, `_cost_price`, `_selling_price`, `_margin`, `_msq`, `_maq`, `_moq` - "
          "**and `<branch>_stock`.** See the warning"],
         ["INSERT", "`tbl_multiuom_itemprice_history`", "For multi-UOM items, the same record per unit"]],
        ["**Quotation** and **Invoice** - the new price is offered from the next line you add",
         "**Item Details** list - the current price column",
         "**Item Price History** - the change appears in the log",
         "The price modal on the item list - the last ten changes for that item",
         "**Cost Worksheet** and the margin reports"],
        fields=[
            ["`branch_id`", "Which branch the change applies to. **Your branch** - the screen does not offer "
             "a choice"],
            ["`<branch>_new_price` etc.", "13 columns per branch holding what the figures became"],
            ["`<branch>_old_price` etc.", "13 more holding what they were. The pair is the audit trail"],
            ["`created_dtm`, `created_by`", "When and by whom - **taken from the posted form**, not from "
             "the session. See the note"],
        ],
        notes=[
            warn("**This screen also writes the stock quantity.** The same `UPDATE tbl_item_stock` sets "
                 "`<branch>_stock` from a form field named `branch_stock_field` - an ordinary, editable text "
                 "box that the page fills with the current stock when it loads (its placeholder still reads "
                 "*Enter MAQ*, left over from a copy-paste). Two consequences. Anyone changing a price can "
                 "type over the stock figure. And even if they do not touch it, the value saved is whatever "
                 "the stock was **when the page opened** - so a GRN or an invoice posted while the price "
                 "screen was sitting open is silently wiped out. **Do not leave this screen open**, and "
                 "check the item's stock after a price change."),
            warn("**A price change is per branch and does not propagate.** Changing the Head Office price "
                 "leaves Kerala and Rajapalayam on the old one. That is the design - but combined with the "
                 "item form writing the same price to all three at creation, it means the three branches "
                 "start identical and then drift apart silently. There is no screen that shows all three "
                 "prices side by side except **Branch Wise Stock** (Part 10)."),
            note("`created_by` and `created_dtm` on the history row come from `$_REQUEST`, not from the "
                 "session and the clock. They are hidden fields on the form, so in normal use they are "
                 "right - but they are not trustworthy as an audit trail the way `$_SESSION['_user_id']` "
                 "would be."),
            tip("**Reading the log.** Each row is one change, for one branch. `<branch>_old_price` is what "
                "it was, `<branch>_new_price` is what it became; the next row for the same item and branch "
                "should have that new price as its old one. Where the chain breaks, something changed the "
                "stock row without going through this screen."),
        ])

    return {"heading": "Part 4", "blocks": b}
