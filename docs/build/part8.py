# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 8  Stores")]
    b += [lead("Four screens, and the only place in BIE where stock goes **up**. Read section 8.1 before "
               "the rest - what moves stock, and what only looks as though it does, is the single most "
               "useful thing to know about this system.")]

    # ------------------------------------------------------------------ 8.1
    b += [h2("8.1  What actually moves stock")]
    b += [p("Stock is a number in one column of one row: `tbl_item_stock.<your branch>_stock`. Four screens "
            "in the whole of BIE change it.")]
    b += [table([30, 22, 48], ["Screen", "Direction", "What it does"], [
        ["**GRN** (`grn_add.php`)", "**Up**", "Accepted quantity is added to this branch's stock column"],
        ["**Invoice from quotation** (`quo_invoice.php`)", "**Down**", "Invoiced quantity is subtracted"],
        ["**Invoice from challan** (`dc_invoice.php`)", "**Down**", "The same"],
        ["**Manage Invoice** (`mng_invoice.php`)", "**Down**", "The same"],
    ])]
    b += [p("Everything else leaves it alone. In particular:")]
    b += [bul([
        "**Purchase Order** - an intention, no movement",
        "**Store Indent** - a request, no movement",
        "**PO Prepare** - pricing, no movement",
        "**Sales Order** - a commitment, no movement, **and no reservation**",
        "**Delivery Challan** - the goods physically leave, and **stock does not change**",
        "**Purchase Return** - goods physically go back, and **stock does not change**. See 8.4",
    ])]
    b += [warn("**Two of those are gaps, not design.** A delivery challan takes goods out of the building "
               "and only the later invoice reduces stock, so anything despatched but not yet invoiced is "
               "counted twice - once on the shelf, once on the van. A purchase return sends goods back to "
               "the supplier and **nothing reduces stock at all**, so the quantity stays up permanently. "
               "Section 8.4 has the detail.")]
    b += [tip("When a stock figure is wrong, work through it in this order: (1) challans raised but not "
              "invoiced, (2) purchase returns - every one of them leaves stock overstated, (3) a price "
              "change made through Item Price Update, which also writes the stock column (section 4.6), "
              "(4) whether you are looking at `tbl_item_details.item_curr_stock`, which is stale, instead "
              "of the branch column (section 4.1).")]
    b += [pb()]

    # ------------------------------------------------------------------ 8.2
    b += form2(
        "8.2", "Store Indent List",
        [("Menu", "Stores > Store Indent List"),
         ("Files", "`store_indent_list.php` (list) - `store_indent_add.php` (raise / edit) - "
                   "`inc/datatable/ajax_SiList.php` - `store_indent_print.php`"),
         ("Type", "Request - **no prices, no stock movement**")],
        ["The store asking for something. A list of items and quantities, a suggested supplier, and a "
         "remark. No rates - that is deliberate, and it is what **PO Prepare** (section 7.3) is for.",
         "Each line shows the **current stock** and the item's minimum order quantity beside the quantity "
         "asked for, so whoever prices it can see why the request was made."],
        [["`tbl_store_indent`", "The header - reference, date, suggested supplier, remarks, status"],
         ["`tbl_store_indent_details`", "One row per line: item, quantity, MOQ, UOM, **and the stock at the "
          "time the indent was raised**"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - item and this branch's current stock"],
         ["`mst_supplier_new`", "Read - the suggested supplier"],
         ["`mst_finyear`, `mst_branch`", "Read - both go into the reference"],
         ["`tbl_task_user`", "Read - who approves"]],
        ["Open **Stores > Store Indent List**. Indents for your branch.",
         "**Add New** opens `store_indent_add.php`. The reference is built as "
         "`SI/0042/BIE/<branch>/<financial year>`.",
         "Choose a **suggested supplier** if you have one in mind, set the date and a remark.",
         "Add lines: pick the item, enter the **quantity needed**. The screen shows the item's MOQ and the "
         "**current stock at this branch** alongside, and stores the stock figure on the line.",
         "Save. Header, then lines. The approver is looked up from `tbl_task_user` and stored on the header.",
         "Print with `store_indent_print.php`.",
         "The indent then appears on the **PO Prepare List** (section 7.3) to be priced.",
         "The dustbin sets `si_status` through the shared delete endpoint."],
        [["INSERT", "`tbl_store_indent`", "`si_finyr`, `si_slno`, `si_refno`, `si_date`, `supp_id`, "
          "`si_remarks`, `branch_id`, `si_approve_id`, `si_status`"],
         ["DELETE + INSERT", "`tbl_store_indent_details`", "Lines rebuilt on every save: `si_id`, "
          "`item_id`, `si_qty`, `item_moq`, `item_uom`, `si_unit`, **`curr_stock`**"],
         ["UPDATE", "`tbl_store_indent`", "The header on edit"],
         ["Soft delete", "`tbl_store_indent`", "Status to `0`"]],
        ["**PO Prepare List** - the indent appears there to be priced",
         "**Purchase Order** - eventually, through the prepare sheet",
         "`store_indent_print.php` - the paper request",
         "**Store Stock reports** (Part 10) - as a pending requirement"],
        fields=[
            ["`si_refno`", "`SI/0042/BIE/HO/2025-26`"],
            ["`si_qty`", "How many are being asked for"],
            ["`curr_stock`", "**The stock when the indent was raised**, stored on the line. A snapshot for "
             "context - it is not updated afterwards and is not the live figure"],
            ["`item_moq`", "The item's minimum order quantity, copied for reference"],
            ["`si_approve_id`", "Who approves, from `tbl_task_user`"],
        ],
        notes=[
            note("**`curr_stock` on the line is a snapshot, not a link.** It records what the stock was the "
                 "day the indent was written, which is useful when reviewing an old request - and "
                 "misleading if you read it as current. The live figure is always "
                 "`tbl_item_stock.<branch>_stock`."),
            note("The block that would have written purchase-order lines directly from the indent is still "
                 "in `store_indent_add.php`, **commented out**. Indents reach an order only through PO "
                 "Prepare."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 8.3
    b += form2(
        "8.3", "GRN List",
        [("Menu", "Stores > GRN List"),
         ("Files", "`grn_list.php` (list) - `grn_add.php` (receive, 1,200 lines) - "
                   "`inc/datatable/ajaxGrnList.php` - `grn_view.php` - `grn_print.php` - "
                   "`inc/cis_ajax/jquery_modal_grn_cancel_reason.php` - `purchase_return_add.php`"),
         ("Type", "**Stock movement - in.** The only screen that increases stock")],
        ["Goods have arrived against a purchase order. For each line you record what was **received**, what "
         "was **accepted**, what was **rejected** and what is still **pending**.",
         "The accepted quantity is added to **your branch's** stock column - the screen reads the column "
         "name out of `mst_branch` and builds the SQL from it, exactly as described in Part 0.",
         "Every movement also writes a row to `tbl_stock_flow`, the running audit of stock changes."],
        [["`tbl_grn`", "The header - reference, date, supplier, purchase order, status"],
         ["`tbl_grn_details`", "One row per line: received, accepted, rejected, pending"],
         ["`tbl_item_stock`", "**Written.** `<branch>_stock` increased by the accepted quantity"],
         ["`tbl_stock_flow`", "**Written.** One audit row per line, with before and after quantities"],
         ["`tbl_purchase_order`", "Updated - the order is marked as received against"],
         ["`tbl_item_details`", "Read for the cost price. **The stock update on this table is commented "
          "out** - see section 4.1"],
         ["`mst_branch`", "**Read for the column name** - `branch_stock_field`"],
         ["`mst_supplier_new`", "Read - the supplier and their credit days"],
         ["`mst_finyear`", "Read - numbers the GRN"]],
        ["Open the **Direct PO List** or the **GRN List** and choose **Add GRN** against an approved "
         "purchase order.",
         "The order's lines are loaded with their ordered quantities. The reference is built as "
         "`GRN/0042/BIE/<branch>/<financial year>` - the serial is `max + 1` **within your branch and "
         "financial year**.",
         "For every line enter **received**, **accepted**, **rejected** and **pending** quantities. "
         "Accepted is what enters stock; rejected and pending are recorded but move nothing.",
         "Save. The header goes in, then the lines.",
         "**Then, per line:** the branch's stock column name is read from `mst_branch`; a `tbl_stock_flow` "
         "row is written; the **current stock is re-read from the database** and the accepted quantity "
         "added to it; and the stock column is updated.",
         "From the list: **print** the GRN, **view** it, raise a **purchase return** against it, or cancel "
         "it with a reason."],
        [["INSERT", "`tbl_grn`", "`grn_slno`, `grn_finyr`, `grn_refno`, `grn_ref_code`, `grn_date`, "
          "`supp_id`, `branch_id`, `po_id`"],
         ["DELETE + INSERT", "`tbl_grn_details`", "Lines rebuilt: received, accepted, rejected, pending"],
         ["INSERT", "`tbl_stock_flow`", "One row per line - `trans_type = 'GRN'`, `trans_id`, `branch_id`, "
          "`item_id`, `item_price`, `before_qty`, `rcvd_qty`, `trans_qty`, `reje_qty`, `pend_qty`, "
          "`after_qty`"],
         ["UPDATE", "`tbl_item_stock`", "**`SET <branch_stock_field> = current + accepted`** - the "
          "current value is re-read from the database first"],
         ["UPDATE", "`tbl_purchase_order`", "Marked as received against"],
         ["UPDATE", "`tbl_grn`", "`rtn_submit_status = 1` when a purchase return is raised against it"]],
        ["**Every stock figure in the system** - the item picker on quotations, orders and invoices",
         "**Store Stock List**, **Branch Wise Stock**, **Item Stock History** (Part 10)",
         "**Item Stock History** - through the `tbl_stock_flow` rows written here",
         "**Purchase Return** - raised against this GRN",
         "**GRN Payment Details** (Part 9) - what is owed, and when",
         "`grn_print.php` - the receipt note"],
        fields=[
            ["`grn_accepted_qty`", "**The only quantity that moves stock.** Received, rejected and pending "
             "are recorded and nothing more"],
            ["`grn_slno`", "`max + 1` within **branch and financial year**"],
            ["`rtn_submit_status`", "`1` once a purchase return exists against this GRN"],
            ["`before_qty` / `after_qty`", "On the audit row. **Not always right** - see the warning"],
            ["`item_price` on the flow row", "Taken from `tbl_item_details.item_cost_price` - the **stale** "
             "non-branch column (section 4.1)"],
        ],
        notes=[
            tip("**The stock update itself is sound.** The code re-reads the current quantity from the "
                "database immediately before adding to it, rather than trusting the figure the browser "
                "posted. So two GRNs saved at the same moment both land. This is worth noting because "
                "several other screens in BIE do not do this."),
            warn("**The audit row can disagree with the stock.** `tbl_stock_flow.before_qty` is taken from "
                 "`item_curr_stock` **as the browser posted it** - the figure read when the GRN form was "
                 "opened - and `after_qty` is that plus the accepted quantity. The real update re-reads the "
                 "database. So if anything moved the stock while the GRN form was open, the stock is right "
                 "and **the audit trail is wrong**. Treat `tbl_stock_flow` as a record of *what was "
                 "received*, not as a reliable running balance."),
            warn("**The value on the audit row uses a stale price.** `item_price` comes from "
                 "`tbl_item_details.item_cost_price`, which was written when the item was created and never "
                 "updated - price changes go to the branch columns on `tbl_item_stock` instead "
                 "(section 4.1). Any valuation built on `tbl_stock_flow.item_price` is valuing at the "
                 "original cost, not the current one."),
            note("**Rejected and pending quantities move nothing and are not chased.** They are recorded on "
                 "the line so the paperwork is complete, but there is no screen that lists outstanding "
                 "pending quantities across GRNs and no reminder that a supplier still owes you goods."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 8.4
    b += form2(
        "8.4", "Purchase Return List",
        [("Menu", "Stores > Purchase Return List"),
         ("Files", "`purchase_return_list.php` (list) - `purchase_return_add.php` (raise) - "
                   "`inc/datatable/ajaxPurchaseReturnList.php` - `purchase_return_view.php` - "
                   "`inc/cis_ajax/jquery_modal_grn_cancel_reason.php`"),
         ("Type", "Transaction - **and it does not move stock**")],
        ["Goods received on a GRN are being sent back to the supplier - wrong item, damaged, short.",
         "The return is raised **against a GRN**, so the lines and quantities are already known. You choose "
         "what goes back and why, and the GRN is flagged as having a return against it.",
         "**Read the warning below before using this screen.** It records the return correctly and does not "
         "reduce stock."],
        [["`tbl_purchase_return`", "The header - reference, date, the GRN, the supplier, remarks"],
         ["`tbl_purchase_return_details`", "One row per returned line, with quantity and reason"],
         ["`tbl_grn`", "**Updated** - `rtn_submit_status = 1`"],
         ["`tbl_grn_details_temp`", "Staging while the return is being built"],
         ["`tbl_item_details`", "Read - the item names and codes"],
         ["`mst_supplier_new`", "Read - the supplier"],
         ["`mst_finyear`", "Read - numbers the return"]],
        ["From the **GRN List**, open a GRN and choose **Purchase Return**. `purchase_return_add.php` opens "
         "with that GRN's lines.",
         "Tick the lines going back and enter the **quantity** and the **reason** for each. The lines are "
         "staged in `tbl_grn_details_temp` while you work.",
         "Save. The header and the return lines are written, the staging is cleared, and the GRN is "
         "stamped `rtn_submit_status = 1`.",
         "View a saved return with `purchase_return_view.php`. The list shows returns for your branch."],
        [["INSERT", "`tbl_purchase_return`", "The header - reference, date, `grn_id`, `supp_id`, remarks"],
         ["DELETE + INSERT", "`tbl_purchase_return_details`", "The returned lines"],
         ["DELETE", "`tbl_grn_details_temp`", "The staging rows"],
         ["UPDATE", "`tbl_grn`", "`rtn_submit_status = 1`"],
         ["UPDATE", "`tbl_purchase_return`", "The header on edit"],
         ["**(nothing)**", "`tbl_item_stock`", "**No stock update. This is the defect - see below**"],
         ["**(nothing)**", "`tbl_stock_flow`", "**No audit row either**"]],
        ["**GRN List** - the GRN now shows a return against it",
         "**Purchase Return List** - the return itself",
         "`purchase_return_view.php` - the printed return",
         "**Not** the stock reports. **Not** Item Stock History. **Not** the item picker"],
        fields=[
            ["`grn_id`", "The GRN being returned against. A return always has one"],
            ["`rtn_submit_status`", "On the GRN - `1` means *a* return exists, not that everything went back"],
        ],
        notes=[
            warn("**Purchase Return never reduces stock.** `purchase_return_add.php` contains no reference "
                 "to `tbl_item_stock`, to `branch_stock_field`, to `item_curr_stock` or to `tbl_stock_flow` "
                 "- the whole stock block is simply absent. The goods physically leave, the paperwork is "
                 "correct, and the system still counts them. **Every purchase return leaves that item's "
                 "stock overstated by the returned quantity, permanently.**\n"
                 "Until it is fixed: keep a list of returns and reconcile stock by hand, or correct the "
                 "branch column directly after each return. When it is fixed, the pattern to copy is the "
                 "one in `grn_add.php` - read `branch_stock_field` from `mst_branch`, re-read the current "
                 "quantity, subtract, and write a `tbl_stock_flow` row with `trans_type = 'PRTN'`."),
            warn("**Nothing reverses the money either.** No `tbl_accounts` entry is written, so the "
                 "supplier's ledger still shows the full GRN value owing. A credit note against the "
                 "supplier has to be raised separately in Accounts."),
            note("`rtn_submit_status` is a flag, not a quantity. One line returned out of twenty sets it, "
                 "and it never clears. It tells you to go and look at the returns, nothing more."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 8.5
    b += form2(
        "8.5", "DC List",
        [("Menu", "Stores > DC List"),
         ("Files", "`dc_list.php` (list) - `dc_add.php` (raise, 1,200 lines) - "
                   "`inc/datatable/ajaxDclist.php` - `dc_print.php` - `package_list.php` - "
                   "`dc_invoice.php` - `inc/cis_ajax/jquery_modal_sales_det.php`"),
         ("Type", "Despatch document - **no stock movement**")],
        ["The delivery challan: goods going out to a customer against a sales order. It records what is "
         "being sent and, unusually, **how it is packed** - which items go in which box, and what kind of "
         "box each one is.",
         "The packing is a real second layer. Each box on the challan has a type (from **Box Type**, "
         "section 2.3) and its own list of contents, and `package_list.php` prints the packing list.",
         "The challan does **not** reduce stock. The invoice raised from it does."],
        [["`tbl_dc`", "The header - reference, date, sales order, customer, delivery branch, transport"],
         ["`tbl_dc_details`", "One row per item line"],
         ["`tbl_dc_details_temp`", "Session-tagged staging while you build the challan"],
         ["`tbl_package_box_details`", "One row per box - its type and its contents"],
         ["`tbl_package_box_details_temp`", "Staging for the boxes"],
         ["`tbl_sales_order`", "Read for the lines; **updated** - `dc_status = 1`"],
         ["`tbl_dc_package_box`", "Read - the box types"],
         ["`mst_customer_branch`", "Read - the delivery address"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - item and this branch's stock (shown, not changed)"]],
        ["From the **Sales Order List**, choose **DC** against an order. `dc_add.php` opens with the "
         "order's lines.",
         "Set the challan date, the **delivery branch** of the customer, and the transport details.",
         "Confirm the item lines and quantities being despatched. Lines are staged in "
         "`tbl_dc_details_temp` against your session.",
         "**Pack it.** Add boxes, choose each box's **type**, and assign items to boxes. These are staged "
         "in `tbl_package_box_details_temp`.",
         "Save. The header is written, then the staged lines and boxes are copied across under the new "
         "`dc_id`, the staging is cleared, and the sales order is stamped `dc_status = 1`.",
         "Print the challan with `dc_print.php` and the packing list with `package_list.php`.",
         "**Invoice it.** From the list, `dc_invoice.php` turns the challan into an invoice - **and that is "
         "the step that reduces stock**."],
        [["INSERT", "`tbl_dc_details_temp`, `tbl_package_box_details_temp`", "Lines and boxes as you add "
          "them, tagged with your session"],
         ["INSERT", "`tbl_dc`", "The header - reference, date, `so_id`, `supp_id`, delivery branch, "
          "transport"],
         ["DELETE + INSERT", "`tbl_dc_details`", "The staged lines under the real `dc_id`"],
         ["DELETE + INSERT", "`tbl_package_box_details`", "The staged boxes likewise"],
         ["DELETE", "`tbl_dc_details_temp`, `tbl_package_box_details_temp`", "The staging rows"],
         ["UPDATE", "`tbl_sales_order`", "`dc_status = 1`"],
         ["UPDATE", "`tbl_dc`", "On edit, and again when an invoice is raised from it"],
         ["**(nothing)**", "`tbl_item_stock`", "**The challan moves no stock.** `dc_invoice.php` does"]],
        ["**Invoice** - `dc_invoice.php`, which reduces stock and posts to accounts",
         "**Sales Order List** - the order now shows `dc_status = 1`",
         "`dc_print.php` - the challan that travels with the goods",
         "`package_list.php` - the packing list",
         "**Sales reports** (Part 10)"],
        fields=[
            ["`so_id`", "The sales order. A challan always has one"],
            ["`branch_id`", "The **customer's** delivery branch, from `mst_customer_branch`"],
            ["`box_id` on a package row", "The box type, from `tbl_dc_package_box` (section 2.3)"],
            ["`dc_status` on the sales order", "`1` once any challan exists"],
        ],
        notes=[
            warn("**Between the challan and the invoice, stock is counted twice.** The goods are on the "
                 "van; the system still has them on the shelf. On a branch that despatches daily and "
                 "invoices weekly, the overstatement is a week's despatches. When a stock count does not "
                 "match, list the challans with no invoice against them first."),
            note("**The packing layer is optional and nothing checks it.** You can save a challan with "
                 "lines and no boxes, or with boxes that do not account for every line. The packing list "
                 "prints what is there."),
            note("Abandoning a half-built challan leaves rows in both `_temp` tables. They are filtered by "
                 "session and never read again, but they accumulate."),
        ])

    return {"heading": "Part 8", "blocks": b}
