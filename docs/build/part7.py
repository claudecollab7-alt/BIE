# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 7  Purchase")]
    b += [lead("Two menu items and two routes to the same place. Either you raise a purchase order straight "
               "away, or a store indent is worked up into one. Both end at a purchase order waiting for "
               "goods.")]

    b += [h2("7.1  The two routes")]
    b += [table([30, 70], ["Route", "The path"], [
        ["Direct", "**Direct PO List** > `direct_purchase_order.php`. You know what you want and from whom. "
         "One screen, one order"],
        ["Through an indent", "**Store Indent** (Part 8) > **PO Prepare List** > `po_prepare.php` > "
         "purchase order. The store asks, purchasing prices it and chooses suppliers, then it becomes an "
         "order"],
    ], bold_first=True)]
    b += [p("Both end with a row in `tbl_purchase_order` and, once the goods arrive, a **GRN** (Part 8) "
            "which is where stock actually increases.")]
    b += [tip("The indent route exists because the person who knows what is needed is not the person who "
              "knows what it costs. The indent is a request with no prices; PO Prepare is where prices and "
              "suppliers are put against each line - and one indent can become several orders, because "
              "different lines can go to different suppliers.")]
    b += [pb()]

    # ------------------------------------------------------------------ 7.2
    b += form2(
        "7.2", "Direct PO List",
        [("Menu", "Purchase > Direct PO List"),
         ("Files", "`lst_direct_po.php` (list) - `direct_purchase_order.php` (add / edit, 1,300 lines) - "
                   "`inc/datatable/ajaxPoList.php` - `po_print.php` - `grn_add.php`, `grn_view.php`, "
                   "`supp_invoice_add.php`"),
         ("Type", "Transaction with approval")],
        ["A purchase order: the supplier, the items, the agreed rates, the tax, and the delivery terms. "
         "Raised directly, without an indent.",
         "Only **approved** suppliers appear in the dropdown - the gate described in section 2.2. And the "
         "order itself needs approving before goods can be received against it.",
         "The printed order draws its letterhead from **your branch's** `mst_branch` row, so the same "
         "screen produces a Head Office order or a Kerala order depending on who is signed in."],
        [["`tbl_purchase_order`", "The header - supplier, dates, value, approval state"],
         ["`tbl_purchase_order_details`", "One row per item line, with rate, discount, tax and net"],
         ["`tbl_po_print_details`", "The terms and other text that print on the order"],
         ["`mst_supplier_new`", "Read - **approved suppliers only** (`supp_approve_status = 1`)"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - the item, and this branch's cost price and stock"],
         ["`mst_hsn`", "Read - the tax rates"],
         ["`mst_finyear`, `mst_branch`", "Read - both go into the order reference"],
         ["`tbl_task_user`", "Read - who approves (`task_id = 1`)"]],
        ["Open **Purchase > Direct PO List**. Orders for your branch with their approval state.",
         "**Add New** opens `direct_purchase_order.php`. The serial is `max(po_slno) + 1` **within your "
         "branch**, and the reference is built as `PO/0042/BIE/<branch>/<financial year>`.",
         "Choose the **supplier**. Unapproved suppliers are not offered.",
         "Add item lines: item, quantity, rate, discount. The tax comes from the item's HSN.",
         "Enter delivery terms, payment terms and any printed notes - these go to `tbl_po_print_details`.",
         "Save. Header, then lines, then the print rows. The approver is looked up from `tbl_task_user` and "
         "stored on the order.",
         "**Approve.** The order is approved through the approval screen; until then it cannot be received "
         "against.",
         "From the list: **print** the order, raise a **GRN** when goods arrive (`grn_add.php`), view a GRN "
         "already made (`grn_view.php`), or record the supplier's bill (`supp_invoice_add.php`).",
         "The dustbin sets the order's status column to `0`."],
        [["INSERT", "`tbl_purchase_order`", "`po_finyr`, `po_slno`, `po_refno`, `po_date`, `supp_id`, "
          "`branch_id`, value, `po_approve_id` from `tbl_task_user`"],
         ["DELETE + INSERT", "`tbl_purchase_order_details`", "Lines rebuilt on every save"],
         ["DELETE + INSERT", "`tbl_po_print_details`", "The printed terms likewise"],
         ["UPDATE", "`tbl_purchase_order`", "On approve - the approval flag, approver and timestamp"],
         ["UPDATE", "`tbl_purchase_order`", "By **GRN** - to record that goods were received against it"],
         ["Soft delete", "`tbl_purchase_order`", "Status to `0`"]],
        ["**GRN** - raised against this order; this is where stock increases",
         "**GRN Payment Details** (Part 9) - what is owed to the supplier",
         "**Cost Worksheet** (Part 10)",
         "`po_print.php` - the order sent to the supplier, on your branch's letterhead",
         "The supplier's ledger, once the bill is recorded"],
        fields=[
            ["`po_slno`", "**Per branch**, not global - `max(po_slno) + 1 WHERE branch_id = <yours>`"],
            ["`po_refno`", "`PO/0042/BIE/HO/2025-26`"],
            ["`supp_id`", "The supplier. Must be approved to have been offered"],
            ["`po_approve_id`", "Who must approve, copied from `tbl_task_user` at creation"],
            ["`branch_id`", "Your branch - which also decides the letterhead on the print"],
        ],
        notes=[
            warn("**The order number is the usual `max + 1`, computed before the insert.** Two buyers at "
                 "the same branch saving at the same moment get the same `po_slno` and the same printed "
                 "reference. Nothing in the database prevents it. At Head Office, where several people "
                 "raise orders, this is worth watching."),
            warn("**The approver is frozen onto the order when it is created.** `po_approve_id` is read "
                 "from `tbl_task_user` at save time and stored. Change who approves purchase orders and "
                 "every order already raised still waits for the old approver. Approve or clear the backlog "
                 "before reassigning."),
            note("**Approving a supplier is not re-checked at order time.** The dropdown filters on "
                 "`supp_approve_status = 1` when the page is drawn; nothing re-verifies it on save. A "
                 "supplier un-approved between opening the form and saving it still gets the order."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 7.3
    b += form2(
        "7.3", "PO Prepare List",
        [("Menu", "Purchase > PO Prepare List"),
         ("Files", "`po_prepare_list.php` (list) - `po_prepare.php` (prepare, 1,100 lines) - "
                   "`inc/datatable/ajaxPoPrepareList.php` - `po_prepare_print.php` - "
                   "`store_indent_add.php`, `store_indent_print.php`"),
         ("Type", "The pricing step between an indent and an order")],
        ["A store indent says *we need these things*. It carries no prices and names no supplier. This "
         "screen is where purchasing turns it into something orderable: **a supplier and a price against "
         "every line**.",
         "Lines are handled individually. Each carries its own `supp_id`, its own rate, discount and tax, "
         "and its own `item_prepare_status` - so half an indent can be priced today and the rest next week, "
         "and different lines can go to different suppliers.",
         "When it is ready it is **sent to admin** for the order to be raised."],
        [["`tbl_po_prepare`", "The header - one per indent being worked up"],
         ["`tbl_po_prepare_dets`", "**One row per line**, each with its own supplier, rate, discount, tax "
          "and status"],
         ["`tbl_store_indent`, `tbl_store_indent_details`", "Read - the indent being priced"],
         ["`mst_supplier_new`", "Read - approved suppliers, per line"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - item, cost price, current stock"],
         ["`mst_hsn`", "Read - tax"],
         ["`mst_finyear`", "Read"]],
        ["Open **Purchase > PO Prepare List**. Indents waiting to be priced, and ones already in progress.",
         "Open one. `po_prepare.php` shows the indent's lines with quantity and the current stock, ready to "
         "be priced.",
         "For **each line**: choose the supplier, enter the unit price and discount, confirm the UOM and "
         "the tax, and the net amount is worked out.",
         "Lines you have not yet dealt with keep their `item_prepare_status` - so you can come back.",
         "Set **send to admin** when the sheet is ready.",
         "Save. The header is written or updated; the detail rows are **deleted and rebuilt** from the form.",
         "Print the prepared sheet with `po_prepare_print.php`, or open the source indent with "
         "`store_indent_print.php`.",
         "Administration then raises the purchase order or orders from it."],
        [["INSERT", "`tbl_po_prepare`", "`po_prepare_date`, `po_prepare_finyr`, **`si_id`** (the indent), "
          "remarks, `send_to_admin_status`"],
         ["DELETE", "`tbl_po_prepare_dets`", "Every line for this sheet"],
         ["INSERT", "`tbl_po_prepare_dets`", "One row per line: `si_id`, `supp_id`, `item_id`, `si_qty`, "
          "`uom`, `item_discount`, `unit_price`, `price`, `gst`, `gst_id`, `net_amt`, "
          "`item_prepare_status`"],
         ["UPDATE", "`tbl_po_prepare`", "The header on re-save, including `send_to_admin_status`"],
         ["Soft delete", "`tbl_po_prepare`", "Through the shared delete endpoint"]],
        ["**Direct PO / Purchase Order** - the priced lines become one or more orders",
         "**Store Indent List** (Part 8) - the indent shows as being worked on",
         "`po_prepare_print.php` - the comparison sheet",
         "**Cost Worksheet** (Part 10)"],
        fields=[
            ["`si_id`", "The store indent this prepares. Held on **both** the header and every line"],
            ["`supp_id`", "**Per line.** This is the point of the screen - one indent, several suppliers"],
            ["`unit_price`, `item_discount`, `net_amt`", "The line's commercial terms"],
            ["`item_prepare_status`", "Whether this line has been dealt with"],
            ["`send_to_admin_status`", "The sheet is ready for an order to be raised from it"],
            ["`company_id`", "Another leftover from the Benzear fork. Nothing reads it"],
        ],
        notes=[
            warn("**Every save rebuilds all the lines.** The detail rows are deleted and re-inserted from "
                 "what the form posts. If a line is not on the form when you save - collapsed, filtered "
                 "out, or lost to a browser problem - it is gone, and with it the supplier and price "
                 "already chosen for it. Check the line count after saving a long sheet."),
            note("**Nothing links the resulting purchase order back to this sheet.** "
                 "`tbl_purchase_order` has no `po_prepare_id`. The chain is traceable forwards - indent, "
                 "prepare sheet, and an order typed from it - but not backwards from the order. To find out "
                 "which indent an order came from you have to match on supplier, item and date."),
            note("An unpriced line is not blocked. A line with no supplier and no price can be saved and "
                 "sent to admin; it simply cannot be ordered. Use `item_prepare_status` to see what is "
                 "still outstanding."),
        ])

    return {"heading": "Part 7", "blocks": b}
