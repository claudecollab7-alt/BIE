# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 11  Service")]
    b += [lead("Two screens for after-sales work: which spare parts fit which product, and repairs booked "
               "in against an invoice the customer already has.")]

    # ------------------------------------------------------------------ 11.1
    b += form2(
        "11.1", "Spare Mapping",
        [("Menu", "Service > Spare Mapping"),
         ("Files", "`spare_mapping_list.php` (list) - `spare_mapping.php` (map, 331 lines) - "
                   "`inc/datatable/ajaxMappingList.php`"),
         ("Type", "Master - a link table between items")],
        ["Which spare parts belong to which product. One product, many spares - and both sides are ordinary "
         "rows in `tbl_item_details`, so a spare is just another item that happens to be mapped to a "
         "parent.",
         "It exists so that when a repair comes in for a product, whoever is booking it can be offered the "
         "right parts instead of searching the whole item list."],
        [["`tbl_spare_mapping`", "The link - `item_id` (the product) and `spare_item_id` (the part), one "
          "row per pair"],
         ["`tbl_item_details`", "Read - **both sides**, the product and the spares"]],
        ["Open **Service > Spare Mapping**. Products that have spares mapped, with how many each has.",
         "Choose a product, or add a new mapping. `spare_mapping.php` opens.",
         "Pick the spares that belong to it. All of them are selected at once - this is a set, not a "
         "sequence.",
         "Save. **Every existing mapping for that product is deleted first**, then the chosen spares are "
         "inserted.",
         "The dustbin on the list removes a mapping through the shared delete endpoint."],
        [["DELETE", "`tbl_spare_mapping`", "**Every row for this product**, before writing"],
         ["INSERT", "`tbl_spare_mapping`", "One row per chosen spare - `item_id`, `spare_item_id`, "
          "`created_by`, `created_dtm`"]],
        ["**Repair Indent** - the parts offered for a product being repaired",
         "Nothing else. It is a lookup for the service desk"],
        fields=[
            ["`item_id`", "The product"],
            ["`spare_item_id`", "The spare part. Also an ordinary `tbl_item_details` row"],
            ["`created_by`, `created_dtm`", "Who mapped it and when"],
        ],
        notes=[
            warn("**Saving rebuilds the whole mapping.** Every row for the product is deleted before the "
                 "chosen spares are inserted, so opening a product with forty spares mapped, ticking one "
                 "more and saving a form that did not post the other forty leaves you with one. Check the "
                 "count after saving."),
            note("**There is no hard delete of the row itself and no status column** - "
                 "`tbl_spare_mapping` has only the two ids and the audit pair. The shared delete endpoint "
                 "is pointed at it from the list, so unlike everywhere else in BIE, removing a mapping "
                 "here really removes it."),
            note("Nothing stops a mapping being circular, or an item being mapped as a spare of itself. "
                 "Nothing reads the mapping except the repair screen, so the consequences are limited to a "
                 "confusing parts list."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 11.2
    b += form2(
        "11.2", "Repair Indent",
        [("Menu", "Service > Repair Indent"),
         ("Files", "`repair_indent_list.php` (list) - `repair_indent_add.php` (book a repair, 961 lines) - "
                   "`inc/datatable/ajaxIndentList.php` - `sales_repair_print.php` - "
                   "`inc/cis_ajax/jquery_sales_repair_status.php` (verify and approve)"),
         ("Type", "Transaction with a two-step approval - **and it moves no stock**")],
        ["A repair booked in for something the customer already bought. The indent is raised **against the "
         "invoice** - `inv_id` - so the repair is tied to the actual sale, and through it to the sales "
         "order and the quotation.",
         "Lines are the parts and labour the repair needs, priced with tax. The whole thing then goes "
         "through the same **verify then approve** pattern as a quotation.",
         "Despite consuming parts, **it does not move stock** and it posts nothing to accounts."],
        [["`tbl_sales_repair`", "The header - reference, date, the invoice, the customer, value, and the "
          "verify and approve flags"],
         ["`tbl_sales_repair_details`", "One row per part or labour line, with quantity, rate and tax"],
         ["`tbl_invoice`", "Read - the invoice being repaired against. **The update to stamp it is "
          "commented out**"],
         ["`tbl_spare_mapping`", "Read - the parts that fit this product"],
         ["`tbl_item_details`", "Read - the parts themselves"],
         ["`mst_supplier_new`", "Read - the customer"],
         ["`mst_finyear`", "Read - numbers the indent"],
         ["`tbl_accounts`", "**Nothing.** The posting code exists and is commented out - see below"]],
        ["Open **Service > Repair Indent**. Repairs booked, with their value and approval state.",
         "**Add New** opens `repair_indent_add.php`. The serial is `max(sal_repair_slno) + 1` for your "
         "branch, and the financial year comes from `mst_finyear`.",
         "Choose the **invoice** being repaired against. That brings the customer, the sales order and the "
         "quotation with it.",
         "Add the lines - the spare parts and the labour - with quantities, rates and tax.",
         "Save. Header, then lines; the detail rows are **deleted and rebuilt** each time.",
         "**Verify.** `jquery_sales_repair_status.php` with `mode = verify` sets `sal_repair_verify_status`, "
         "your user id and the timestamp.",
         "**Approve.** The same endpoint with `mode = approve` sets the approve flag, id and timestamp.",
         "Print with `sales_repair_print.php`. The quotation action on the list opens `quotation.php`, so a "
         "repair can be quoted for."],
        [["INSERT", "`tbl_sales_repair`", "`sal_repair_finyr`, `sal_repair_slno`, `sal_repair_date`, "
          "`so_id`, `inv_id`, `supp_id`, `company_id`, `modify_by`"],
         ["DELETE + INSERT", "`tbl_sales_repair_details`", "The lines, rebuilt on every save"],
         ["UPDATE", "`tbl_sales_repair`", "On verify - `sal_repair_verify_status`, `_verify_by`, "
          "`_verify_date_time`"],
         ["UPDATE", "`tbl_sales_repair`", "On approve - `sal_repair_approve_status`, `_approve_by`, "
          "`_approve_date_time`"],
         ["**(nothing)**", "`tbl_item_stock`", "**No stock movement**, even though parts are consumed"],
         ["**(nothing)**", "`tbl_accounts`", "**No journal entry** - the code is there, commented out"],
         ["**(nothing)**", "`tbl_invoice`", "The `repair_submit_status` update is also commented out"]],
        ["**Repair Indent List** - the indent with its approval state",
         "`sales_repair_print.php` - the job sheet",
         "**Quotation** - a repair can be quoted for from the list",
         "**Not** the stock reports. **Not** the invoice, which is not stamped"],
        fields=[
            ["`inv_id`", "**The invoice being repaired against.** The anchor of the whole record"],
            ["`so_id`, `quo_id`", "Carried across from the invoice, so the chain back to the original sale "
             "is intact"],
            ["`sal_repair_verify_status` / `_approve_status`", "`0` / `1`. The same two-step pattern as a "
             "quotation"],
            ["`sal_repair_value`", "The repair total"],
            ["`company_id`", "Holds the **branch** id, despite the name - the serial is computed "
             "`WHERE company_id = <your branch>`. Another Benzear-fork leftover, here quietly repurposed"],
        ],
        notes=[
            warn("**Parts used in a repair never leave stock.** `repair_indent_add.php` touches no stock "
                 "table and writes no `tbl_stock_flow` row. The parts are listed, priced and printed on the "
                 "job sheet, and the system still counts them on the shelf. Together with purchase returns "
                 "(section 8.4) this is the second standing reason stock reads high."),
            warn("**Three things in this screen are written and then commented out**: the "
                 "`tbl_accounts` posting in `jquery_sales_repair_status.php`, the "
                 "`tbl_invoice.repair_submit_status` stamp in `repair_indent_add.php`, and with it any way "
                 "of telling from the invoice that a repair exists against it. The code reads as though the "
                 "feature was built and then switched off. If you are asked to finish it, the shapes are "
                 "all there - including a lookup of a sales ledger from `mst_accounts_setting`, a table BIE "
                 "does not otherwise use."),
            note("**A repair can be booked against an invoice more than once** and nothing warns you, "
                 "because the invoice is never stamped. To see whether a customer has already had a repair "
                 "on an invoice, search the Repair Indent list by invoice."),
            note("`company_id` on `tbl_sales_repair` really holds a branch id. If you write a query against "
                 "this table, do not join it to anything expecting a company."),
        ])

    return {"heading": "Part 11", "blocks": b}
