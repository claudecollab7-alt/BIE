# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 6  Sales")]
    b += [lead("Two menu items, but a longer chain than that suggests: quotation, then either a sales order "
               "or a direct invoice, then a delivery challan, then the invoice. The menu shows the two "
               "lists; the rest is reached from them.")]

    b += [h2("6.1  The sales chain")]
    b += [table([26, 74], ["Step", "What happens, and where"], [
        ["Quotation", "`quotation.php` - priced offer to a customer. **Versioned** - a revision is a new row"],
        ["Verify, then approve", "Two separate people, two separate flags on the quotation"],
        ["Sales Order", "`gen_so.php` - the customer accepts. Generated **from** the quotation"],
        ["Delivery Challan", "`dc_add.php` - goods go out, packed into boxes. **No stock movement**"],
        ["Invoice", "`dc_invoice.php` from a challan, or `quo_invoice.php` straight from a quotation. "
         "**This is where stock moves**"],
    ], bold_first=True)]
    b += [warn("**Stock does not move until the invoice.** Not on the quotation, not on the sales order, "
               "and not on the delivery challan - the goods can be packed, loaded and gone while the system "
               "still counts them as in stock. Only `quo_invoice.php`, `dc_invoice.php` and "
               "`mng_invoice.php` reduce `tbl_item_stock`. If physical stock and system stock disagree, "
               "look for challans not yet invoiced.")]
    b += [pb()]

    # ------------------------------------------------------------------ 6.2
    b += form2(
        "6.2", "Quotation List",
        [("Menu", "Sales > Quotation List"),
         ("Files", "`lst_quotation.php` (list) - `quotation.php` (add / edit, 2,200 lines) - "
                   "`inc/datatable/ajaxQuotationlist.php` - `print_quotation.php` - "
                   "`quo_proforma_invoice_print.php` - `lst_quo_approval.php` and "
                   "`inc/cis_ajax/jquery_quo_approval.php` (approval)"),
         ("Type", "Transaction with versioning and a two-step approval")],
        ["A priced offer: the customer, the items, the rates and discounts, the tax, and up to eight "
         "standard terms picked from a list.",
         "The distinctive thing is **versioning**. Revising a quotation does not overwrite it. A new row is "
         "written with the **same `quo_slno`** and `quo_version` one higher, and `prev_quo_id` pointing at "
         "the row it replaces. So the whole negotiation survives - version 1 at one price, version 3 at "
         "another, and the trail between them.",
         "Before it can be used, a quotation is **verified** by one person and **approved** by another. "
         "Only then can a sales order or an invoice be generated from it."],
        [["`tbl_quotation`", "The header - customer, date, value, terms, and the verify and approve flags"],
         ["`tbl_quotation_details`", "One row per item line"],
         ["`tbl_quo_pack_details`", "Packing details for the quotation"],
         ["`mst_supplier_new`", "Read - the customer (`supp_type = 'C'`) and, through `discount_apply`, "
          "whether the discount fills in by itself"],
         ["`mst_customer_branch`", "Read - which of the customer's branches this is for"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - the item, and **this branch's** price and stock"],
         ["`mst_hsn`", "Read - the tax rates"],
         ["`mst_finyear`", "Read - numbers the quotation"],
         ["`tbl_task_user`", "Read - who the approval is routed to"]],
        ["Open **Sales > Quotation List**. Quotations for your branch, with their version, value and "
         "approval state.",
         "**Add New** opens `quotation.php`. The serial is `max(quo_slno) + 1` within the financial year, "
         "padded to three digits; the version starts at 1.",
         "Choose the **customer** and their **branch**, the date, and the contact phone and email to print.",
         "Add item lines. Picking an item calls the item endpoint, which returns **your branch's** price, "
         "discount and stock - plus the customer's supplier `discount_apply` flag, which decides whether "
         "the discount fills in automatically (see section 1.3).",
         "Pick up to **eight terms and conditions** from the standard list, and add free-text terms.",
         "Save. The header is written, then the lines, then the packing rows.",
         "**Verify.** Someone opens it and sets `quo_verify_status`, stamped with their id and the time.",
         "**Approve.** The approver - found from `tbl_task_user` - approves or rejects with a remark, "
         "through `lst_quo_approval.php`.",
         "**Revise.** Editing an approved quotation writes a **new row**: same `quo_slno`, "
         "`quo_version + 1`, `prev_quo_id` set, and **both status flags reset to 0** - it must be verified "
         "and approved again.",
         "**Convert.** From the list, generate a sales order (`gen_so.php`) or go straight to an invoice "
         "(`quo_invoice.php`). Either stamps the quotation so it cannot be converted twice."],
        [["INSERT", "`tbl_quotation`", "The header, with `quo_slno`, `quo_version`, `quo_refno`, "
          "`prev_quo_id`, `supp_id`, `branch_id`, `quo_value`, the eight `terms_con_id*` and `bie_branch_id`"],
         ["DELETE + INSERT", "`tbl_quotation_details`", "The lines are rebuilt on every save"],
         ["DELETE + INSERT", "`tbl_quo_pack_details`", "The packing rows likewise"],
         ["UPDATE", "`tbl_quotation`", "On verify - `quo_verify_status`, `quo_verify_by`, "
          "`quo_verify_date_time`, **and `quo_approve_status` back to 0**"],
         ["UPDATE", "`tbl_quotation`", "On approve - `quo_approve_status`, `quo_approve_by`, "
          "`quo_approve_remarks`, `quo_approve_date_time`"],
         ["UPDATE", "`tbl_quotation`", "On conversion - `so_gen_status` and `quo_so_id`, or "
          "`inv_gen_status` and `quo_inv_id`"],
         ["Soft delete", "`tbl_quotation`", "Sets `quo_status = 0`"]],
        ["**Sales Order** - generated from an approved quotation, which keeps `quo_id`",
         "**Invoice** - either through the sales order and challan, or straight from here",
         "**Quotation Branch Report** and **Sales List** (Part 10)",
         "`print_quotation.php` - the customer-facing print, with the branch letterhead from `mst_branch`",
         "`quo_proforma_invoice_print.php` - the proforma"],
        fields=[
            ["`quo_slno` + `quo_version`", "**Together they identify a quotation.** The serial stays, the "
             "version climbs. `quo_id` identifies one *row*, which is one version"],
            ["`prev_quo_id`", "The row this version replaced. Follow it back for the full history"],
            ["`quo_refno`", "The printed reference"],
            ["`quo_verify_status` / `quo_approve_status`", "`0` / `1`. **Both** must be `1` before the "
             "quotation is usable"],
            ["`so_gen_status`, `quo_so_id`", "Set when a sales order is generated - stops a second one"],
            ["`inv_gen_status`, `quo_inv_id`", "The same for a direct invoice"],
            ["`branch_id`", "The **customer's** branch. `bie_branch_id` is **your** branch - do not confuse "
             "them"],
            ["`terms_con_id1` … `id8`", "Eight slots, each pointing at a standard term"],
        ],
        notes=[
            warn("**`branch_id` and `bie_branch_id` mean opposite things.** On `tbl_quotation`, `branch_id` "
                 "is the *customer's* delivery branch (`mst_customer_branch`) and `bie_branch_id` is the BIE "
                 "branch raising it (`mst_branch`). The same word, two different tables. Getting them the "
                 "wrong way round in a report is an easy mistake and produces plausible-looking nonsense."),
            warn("**Quotation approval is routed to the wrong task.** `tbl_task_user` holds three rows - "
                 "`1` PO Approval, `2` Supplier Approval, `3` Quotation Approval - but `quotation.php` "
                 "looks up **`task_id = 1`**. Row 3 is never read. In the shipped data both rows name user "
                 "`1`, so nothing looks wrong; point quotation approval at a different person and it will "
                 "silently keep going to the PO approver."),
            note("**Verifying resets the approval.** The verify step writes `quo_approve_status = 0` at the "
                 "same time, so a quotation that is verified after being approved goes back to the "
                 "approver. That is deliberate, but it surprises people - approve last."),
            tip("**Reading a versioned quotation.** The list shows the latest version. To see the "
                "negotiation, select every row with the same `quo_slno` and `quo_finyr` and order by "
                "`quo_version`. Only the latest version is convertible."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 6.3
    b += form2(
        "6.3", "Sales Order List",
        [("Menu", "Sales > Sales Order List"),
         ("Files", "`lst_sales_order.php` (list) - `gen_so.php` (generate / edit) - "
                   "`inc/datatable/ajaxSaleslist.php` - `print_sales_order.php` - "
                   "`inc/cis_ajax/jquery_modal_sales_det.php` (view) - "
                   "`inc/cis_ajax/jquery_modal_so_reject_reason.php` (reject) - `dc_add.php`, "
                   "`invoice_print.php`"),
         ("Type", "Transaction - generated from a quotation, never from nothing")],
        ["The customer has accepted. A sales order records the commitment: what was agreed, at what price, "
         "for delivery by when.",
         "**It is always generated from an approved quotation.** There is no blank sales order form - "
         "`gen_so.php` is reached from the quotation list, and the quotation's lines are carried across.",
         "From here the order goes out as a **delivery challan**, and the challan becomes an **invoice**."],
        [["`tbl_sales_order`", "The header - quotation, customer, dates, value, balance value"],
         ["`tbl_sales_order_details`", "One row per line"],
         ["`tbl_sales_order_pack_dts`", "The packing details"],
         ["`tbl_quotation`", "Read for the lines; **updated** to record that an order was generated"],
         ["`mst_supplier_new`, `mst_customer_branch`", "Read - customer and delivery branch"],
         ["`tbl_item_details`, `tbl_item_stock`", "Read - item, price and **this branch's** stock"],
         ["`mst_finyear`, `mst_branch`", "Read - both go into the order reference"]],
        ["From the **Quotation List**, open an approved quotation and choose **Generate SO**. `gen_so.php` "
         "opens with the quotation's customer and lines already in place.",
         "Enter the **sales order number**, the order date, the customer's own reference, and the "
         "**despatch date**.",
         "Adjust lines if the accepted order differs from the quotation.",
         "Save. The reference is built as `SO/0042/BIE/<branch>/<financial year>`, the header and lines are "
         "written, and the quotation is stamped `so_gen_status = 1` with `quo_so_id` pointing at the new "
         "order.",
         "Back on the **Sales Order List**, the actions are: view in a modal, print, **reject** with a "
         "reason, raise a **delivery challan** (`dc_add.php`), or print the invoice once one exists."],
        [["INSERT", "`tbl_sales_order`", "`so_finyr`, `so_slno`, `so_refno`, `so_ref`, `so_date`, "
          "`despatch_date`, `quo_id`, `supp_id`, `branch_id`, `bie_branch_id`, `item_net_val`, `bal_value`"],
         ["DELETE + INSERT", "`tbl_sales_order_details`", "Lines rebuilt on every save"],
         ["DELETE + INSERT", "`tbl_sales_order_pack_dts`", "Packing rows likewise"],
         ["UPDATE", "`tbl_quotation`", "`so_gen_status = 1`, `quo_so_id` = the new order"],
         ["UPDATE", "`tbl_sales_order`", "`dc_status = 1` when a delivery challan is raised against it"],
         ["UPDATE", "`tbl_sales_order`", "On invoicing, by `dc_invoice.php` or `quo_invoice.php`"]],
        ["**Delivery Challan** - raised from this order",
         "**Invoice** - through the challan",
         "**Sales Order Report** and **SO Stock List Branchwise** (Part 10)",
         "`print_sales_order.php` - the order confirmation",
         "The quotation it came from, which now shows as converted"],
        fields=[
            ["`quo_id`", "The quotation this came from. **Never empty** - there is no standalone order"],
            ["`so_slno`, `so_refno`", "`SO/0042/BIE/HO/2025-26` - serial, company, **your** branch, "
             "financial year"],
            ["`item_net_val`", "The order value"],
            ["`bal_value`", "What is still to be invoiced. Starts at the full value"],
            ["`despatch_date`", "The promised date. What the pending-order reports measure against"],
            ["`dc_status`", "`1` once a delivery challan exists"],
            ["`branch_id` / `bie_branch_id`", "Customer's branch / BIE's branch - same trap as 6.2"],
        ],
        notes=[
            warn("**A sales order commits nothing.** No stock is reserved and no quantity is set aside. Two "
                 "orders can promise the same single unit, and both will look fine until one of them "
                 "reaches the invoice and finds the stock gone. There is no allocation anywhere in BIE - "
                 "`item_allotted_qty` exists on `tbl_item_details` but nothing writes it."),
            warn("**`dc_status` is a flag, not a count.** One challan against a ten-line order sets it to "
                 "`1` and it never comes back. It tells you *a* challan exists, not that the order is "
                 "fully delivered. For that, compare the order's lines with the challan lines."),
            note("The order reference carries the branch that raised it, so orders from the three branches "
                 "never collide even though the serial is per branch."),
        ])

    return {"heading": "Part 6", "blocks": b}
