# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 10  Report")]
    b += [lead("Fourteen reports, and **every one of them is read-only** - not one writes a single row. "
               "Six are about stock, five about sales, one about purchase cost, one about discounts and "
               "one about quotations.")]

    # ------------------------------------------------------------------ 10.1
    b += [h2("10.1  How to read this menu")]
    b += [table([34, 66], ["Report", "Answers"], [
        ["Store Stock List", "What is in stock **at my branch**, with bin locations"],
        ["Stock List (item-wise)", "The same, laid out by item"],
        ["Stock List (all branches)", "**All three branches side by side** - the one screen that does this"],
        ["Branch Wise Stock", "Stock for one chosen branch"],
        ["Item Stock History", "Every movement of one item - the audit trail"],
        ["Item Stock History Branch Wise", "The same, per branch"],
        ["Cost Worksheet", "What items cost to buy"],
        ["Credit List", "**Invoices not yet paid** - BIE's receivables"],
        ["Quotation Branch Report", "Quotations raised, per branch"],
        ["Sales List", "What was sold, by line"],
        ["Invoice Report", "Invoices raised, with their values"],
        ["Sales Order Report", "Orders raised and their state"],
        ["SO Stock List Branchwise", "**Ordered but not yet despatched**, against stock"],
        ["Item Discount", "The discount bands for every item, all branches at once"],
    ], bold_first=True)]
    b += [tip("**Every one of these reports prints with the heading *Day Book*.** It is a hardcoded string "
              "in the print helper, copied from page to page. Ignore it - there is no day book in BIE "
              "(section 9.1).")]
    b += [warn("**The stock reports can only be as right as the stock is.** Before trusting a figure here, "
               "read section 8.1: purchase returns never reduce stock, and delivery challans do not reduce "
               "it until the invoice. Both make these reports read high.")]
    b += [pb()]

    # ------------------------------------------------------------------ 10.2 stock reports
    b += [h2("10.2  The six stock reports")]
    b += [p("Six screens over the same data, differing in **whose stock** and **how it is laid out**. All "
            "six read `tbl_item_stock`, and all six have to resolve a branch's column name before they can "
            "read anything at all.")]

    b += mini("10.2.1", "Store Stock List", "`store_stock_list.php` + `inc/datatable/ajaxStoreStockList.php` "
              "+ `inc/cis_ajax/jquery_modal_stock_det.php`",
              "Stock at **your** branch, as a searchable list. Reads `branch_stock_field`, "
              "`branch_stock_location_rack_field` and `branch_stock_location_row_field` from your "
              "`mst_branch` row, so it shows the quantity **and the bin location**. The heading carries "
              "your user name. The eye icon opens the item's stock detail in a modal.",
              "`tbl_item_details`, `tbl_item_stock`, `mst_branch`",
              "Quantity, rack, row, plus the item's code, description, category and UOM",
              "Day-to-day stock enquiry. This is the one most people use")
    b += [sp()]
    b += mini("10.2.2", "Stock List (item-wise)", "`rpt_store_stock_list.php`",
              "The same stock, laid out as a printable report rather than a searchable list. Filters on "
              "**item** and **item type**.",
              "`tbl_item_details`, `tbl_item_stock`, `mst_branch`",
              "Item, description, UOM, quantity",
              "Printing a stock position")
    b += [sp()]
    b += mini("10.2.3", "Stock List (all branches)", "`rpt_all_store_stock_list.php`",
              "**The only screen that shows all three branches at once.** It queries `mst_branch` for "
              "active branches and builds a column per branch, so Head Office, Kerala and Rajapalayam sit "
              "side by side for each item.",
              "`tbl_item_details`, `tbl_item_stock`, `mst_branch`",
              "One row per item, one quantity column per branch",
              "Seeing where stock actually is before moving it or promising it. **Use this one when a "
              "branch says it has none**")
    b += [sp()]
    b += mini("10.2.4", "Branch Wise Stock", "`rpt_branch_wise_stock.php`",
              "Stock for **one chosen branch** - you pick the branch rather than being given your own. "
              "Filters on branch, item and item type.",
              "`tbl_item_details`, `tbl_item_stock`, `mst_branch`",
              "Item, description, UOM, quantity for the chosen branch",
              "Checking another branch's position")
    b += [sp()]
    b += mini("10.2.5", "Item Stock History", "`rpt-item-stock-history.php`",
              "**The audit trail for one item.** Every row of `tbl_stock_flow` for it, in order, showing "
              "the transaction type, the quantity moved, and the before and after figures. Only two types "
              "are recognised: **`GRN`** (goods in) and **`INV`** (invoiced out).",
              "`tbl_stock_flow`, `tbl_item_details`",
              "Date, `trans_type`, `before_qty`, `trans_qty`, `after_qty`, price",
              "Explaining how a stock figure got where it is. **Read the warnings in 10.3 before trusting "
              "the before and after columns**")
    b += [sp()]
    b += mini("10.2.6", "Item Stock History Branch Wise", "`rpt_item_stock_history_branch_wise.php`",
              "The same movements, split by branch - `tbl_stock_flow` carries `branch_id` on every row, so "
              "the history can be read one branch at a time.",
              "`tbl_stock_flow`, `tbl_item_details`, `mst_branch`",
              "The same columns, filtered to one branch",
              "Tracing a movement at a particular branch")
    b += [sp()]

    b += [h3("10.3  Two things the stock history will not tell you")]
    b += [warn("**Only two movement types are ever written, and only two are recognised.** "
               "`tbl_stock_flow` gets rows from the GRN (`GRN`) and the three invoice screens (`INV`), and "
               "`rpt-item-stock-history.php` has branches for exactly those two. Nothing else writes a "
               "flow row - not the purchase return, not the delivery challan, not the price-update screen "
               "which also writes the stock column (section 4.6). So **a movement can change the stock "
               "without appearing in the history at all**, and the running balance will not reconcile.")]
    b += [warn("**The before and after figures on a GRN row are taken from the browser.** As section 8.3 "
               "explains, `before_qty` is what the GRN form was showing when it was opened, while the real "
               "stock update re-reads the database. The two agree only if nothing moved in between. Treat "
               "`trans_qty` - what actually moved - as reliable, and the before and after columns as "
               "indicative.")]
    b += [pb()]

    # ------------------------------------------------------------------ 10.4
    b += form2(
        "10.4", "Cost Worksheet",
        [("Menu", "Report > Cost Worksheet"),
         ("Files", "`rpt_cost_worksheet.php` + `inc/datatable/ajaxCostWorkSheet.php`"),
         ("Type", "Read only")],
        ["What items cost to buy. The endpoint reads `tbl_item_details` and pulls the cost price out of "
         "**your branch's** `branch_item_cost_price` column on `tbl_item_stock`.",
         "It is a buying-side view: the cost the item currently carries, not what any particular purchase "
         "order paid."],
        [["`tbl_item_details`", "Read - item code, description, classification"],
         ["`tbl_item_stock`", "Read - your branch's cost price column"],
         ["`mst_branch`", "Read - to resolve that column's name"]],
        ["Open **Report > Cost Worksheet**.",
         "The list loads through `ajaxCostWorkSheet.php`, resolving your branch's cost column.",
         "Search and filter, then print."],
        [["(none)", "-", "**Read only**"]],
        ["Nothing - it is a view"],
        notes=[
            note("**The cost shown is the item's current cost price, not a purchase history.** It changes "
                 "the moment someone runs an Item Price Update (section 4.6), and it changes for your "
                 "branch only. Two branches will show two different worksheets for the same item, which is "
                 "correct but surprising."),
            note("For what a specific purchase actually cost, use the purchase order and the GRN rather "
                 "than this screen."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 10.5
    b += [h2("10.5  The five sales reports")]

    b += mini("10.5.1", "Credit List", "`rpt_credit_list.php`",
              "**BIE's receivables report.** Invoices with money still outstanding. For each invoice it "
              "sums `tbl_invoice_credit_details.paid_amount` and shows it against the invoice value, so "
              "you see invoiced, paid and outstanding. Filters on customer.",
              "`tbl_invoice`, `tbl_invoice_details`, `tbl_invoice_credit_details`, `mst_supplier_new`",
              "Invoice reference and date, customer, value, paid, outstanding",
              "Chasing money. **This and GRN Payment Details are the whole of BIE's money tracking** - "
              "see 9.1")
    b += [warn("**This report does not know about Sales Receipts.** It reads only the credit payments "
               "entered through `mng_credit.php` against the invoice. Money taken through **Sales Receipt** "
               "(section 9.3) is recorded against the *sales order* and leaves the invoice showing as "
               "unpaid here. If customers appear to owe money they have already paid, this is why.")]
    b += [sp()]
    b += mini("10.5.2", "Sales List", "`rpt_sales_list.php`",
              "What was sold, line by line, out of `tbl_invoice_details`. Filters on customer.",
              "`tbl_invoice`, `tbl_invoice_details`, `tbl_item_details`, `mst_supplier_new`",
              "Invoice, date, customer, item, quantity, rate, value",
              "Sales analysis by item or customer")
    b += [sp()]
    b += mini("10.5.3", "Invoice Report", "`invoice_report.php`",
              "Invoices at header level with their values. For each it sums the line net values and the "
              "packing charges from `tbl_invoice_pack_details` - and separately sums the packing rows whose "
              "`inv_pack_decp` is one of 2, 5, 11 or 15, which are the insurance-type charges. Filters on "
              "**branch** and **customer**.",
              "`tbl_invoice`, `tbl_invoice_details`, `tbl_invoice_pack_details`, `mst_supplier_new`, "
              "`mst_branch`",
              "Invoice reference, date, customer, net value, packing, insurance, total",
              "Monthly sales totals; reconciling invoice values")
    b += [sp()]
    b += mini("10.5.4", "Sales Order Report", "`rpt_sales_order.php` (823 lines - the largest report)",
              "Orders raised and where each one has got to. Filters on branch.",
              "`tbl_sales_order`, `tbl_sales_order_details`, `tbl_quotation`, `tbl_dc`, `tbl_invoice`, "
              "`mst_supplier_new`",
              "Order reference and date, customer, despatch date, value, whether a challan and an invoice "
              "exist",
              "Order book and delivery chasing")
    b += [sp()]
    b += mini("10.5.5", "SO Stock List Branchwise", "`rpt_so_stock_list_branchwise.php`",
              "**Ordered but not yet despatched, set against stock.** For each item it sums `so_qty` over "
              "orders where `dc_status = 0` and `so_cancel_status = 0` for the chosen branch and financial "
              "year, and shows it beside the stock held. The one report that answers *can we actually "
              "deliver what we have promised?*",
              "`tbl_sales_order`, `tbl_sales_order_details`, `tbl_item_details`, `tbl_item_stock`, "
              "`mst_branch`",
              "Item, pending order quantity, stock at that branch",
              "Deciding what to buy or make next. The closest BIE gets to a shortage report")
    b += [note("Pending is defined as **no delivery challan yet** (`dc_status = 0`). Because `dc_status` is "
               "a flag rather than a count (section 6.3), an order that has been *partly* despatched counts "
               "as fully despatched here and drops out. The pending figure is therefore a lower bound.")]
    b += [sp()]
    b += mini("10.5.6", "Quotation Branch Report", "`quotation_branch_rept.php`",
              "Quotations raised, per branch, with their value and state. Filters on branch.",
              "`tbl_quotation`, `mst_supplier_new`, `mst_branch`",
              "Quotation reference, version, date, customer, value, verify and approve state",
              "Sales-pipeline review; seeing what is stuck waiting for approval")
    b += [note("Because quotations are versioned (section 6.2), a quotation revised three times appears as "
               "three rows. Read `quo_version` before totalling anything, or you will count the same "
               "opportunity several times.")]
    b += [pb()]

    # ------------------------------------------------------------------ 10.6
    b += form2(
        "10.6", "Item Discount",
        [("Menu", "Report > Item Discount"),
         ("Files", "`rpt_item_discount.php`"),
         ("Type", "Read only - **all branches at once**")],
        ["The discount bands for every item: the minimum and maximum discount allowed, for **every branch, "
         "side by side**. Filters on item.",
         "It is the discount equivalent of the all-branches stock report - the one place you can see "
         "whether the three branches are selling on the same terms."],
        [["`tbl_item_details`", "Read - item code, purchase code, description"],
         ["`tbl_item_stock`", "Read - the min and max discount columns for each branch"],
         ["`mst_branch`", "Read - the list of branches"]],
        ["Open **Report > Item Discount**, optionally choose an item.",
         "The page reads the active branches, builds a pair of columns for each, and runs one query joining "
         "`tbl_item_details` to `tbl_item_stock`.",
         "Every item is listed with its minimum and maximum discount per branch."],
        [["(none)", "-", "**Read only**"]],
        ["Nothing - it is a view"],
        extra=[
            h4("It builds the column names differently from everything else"),
            p("This is the newest report in the system and it is the only one that does **not** read the "
              "column names out of `mst_branch`. It derives them from the branch code instead:"),
            code([
                "foreach ($branches as $branch) {",
                "    $field = strtolower($branch->branch_code);        // 'ho', 'kl', 'rjpm'",
                "    $select_cols .= \", s.{$field}_item_min_discount AS {$field}_min_disc\";",
                "    $select_cols .= \", s.{$field}_item_max_discount AS {$field}_max_disc\";",
                "}",
            ]),
            p("Compare that with the item form (section 4.4), which uses "
              "`$value['branch_item_min_discount']` - the column name **stored on the branch row**. Both "
              "produce `ho_item_min_discount` today, because the stored names happen to follow the "
              "pattern."),
        ],
        notes=[
            warn("**This report guesses the column names instead of looking them up.** It assumes every "
                 "branch's discount columns are called `<lowercased branch code>_item_min_discount` and "
                 "`..._max_discount`. That is true of the three branches as shipped, and it is not "
                 "guaranteed - `mst_branch` stores those names precisely so they do not have to be "
                 "guessed. Add a branch whose stored column names do not match its code and this report "
                 "shows blank columns for it while every other screen works. If you fix one thing in this "
                 "part, fix this: read `branch_item_min_discount` and `branch_item_max_discount` from the "
                 "branch row, as the rest of BIE does."),
            note("The discount columns come from `tbl_item_stock`, so they are whatever the last **Item "
                 "Price Update** for that branch set (section 4.6). An item never price-updated at a branch "
                 "shows the figures the item form wrote at creation, which were the same for all three."),
        ])

    return {"heading": "Part 10", "blocks": b}
