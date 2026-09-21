# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 9  Accounts")]
    b += [lead("Three screens: the invoice, money coming in from customers, and money going out to "
               "suppliers. Read 9.1 first - what BIE calls Accounts is not what the word usually means.")]

    # ------------------------------------------------------------------ 9.1
    b += [h2("9.1  There is no general ledger")]
    b += [p("BIE has a table called `tbl_accounts` that looks like a double-entry journal - `acc_date`, "
            "`voucher_type`, `acc_tran_value`, `dr_ledger_id`, `cr_ledger_id`. It has a `mst_ledger` table, "
            "and every customer, supplier and employee gets a ledger created with them. It all looks like "
            "bookkeeping.")]
    b += [warn("**`tbl_accounts` is write-only, and only payroll writes to it.** Four files insert into the "
               "table - `emp_advance_update.php`, `emp_advance_return_payment.php`, `emp_debit_note.php` "
               "and `issue_salary.php`. A fifth, `inc/cis_ajax/jquery_sales_repair_status.php`, has the "
               "code to post a repair entry but **it is commented out**. Nothing anywhere selects from the "
               "table. So the journal contains employee advances, debit notes and salaries, and nothing "
               "else - and no screen ever shows them.")]
    b += [p("And there are no ledger reports. **BIE has no Day Book, no Ledger Book and no Trial Balance "
            "screen** - not in the menu, and not as an unlinked file. The words *Day Book* do appear in the "
            "code, but only as a hardcoded print title copied into every report page, including the stock "
            "reports.")]
    b += [table([30, 70], ["What you might expect", "What is actually there"], [
        ["A general ledger", "**Nothing.** The journal is written and never read"],
        ["Customer balances", "Per invoice - `tbl_invoice.inv_bal_value`, reduced by each credit payment"],
        ["Supplier balances", "Per GRN - `tbl_grn_pay_receipt` rows against the GRN value"],
        ["A trial balance", "**Nothing**"],
        ["`mst_ledger` on a customer", "Created and stored, then used only as a dropdown on the receipt "
         "screen"],
        ["`mst_ledger` on an employee", "Actually used - it is the `dr`/`cr` side of the payroll journal "
         "rows nothing reads"],
    ], bold_first=True)]
    b += [tip("**So how do you know what a customer owes?** Per document, not per party. "
              "`tbl_invoice.inv_bal_value` starts at the invoice total and comes down as credit payments "
              "are recorded; the **Credit List** report (section 10.7) sums the outstanding ones. For "
              "suppliers, **GRN Payment Details** (section 9.4) does the same against GRNs. There is no "
              "screen that gives you one number for a party across everything.")]
    b += [note("This is one of the clearest places where BIE diverges from Benzear, the codebase it was "
               "forked from. Benzear has the full chain - chart of accounts, Day Book, Ledger Book, Trial "
               "Balance, all reading one `tbl_accounts`. BIE kept the writes and the ledger creation and "
               "dropped the reporting. If you are ever asked to add accounting to BIE, the data model is "
               "already half there and most of the money movements are **not** posting to it - the invoice, "
               "the receipt, the supplier payment and the credit note all write nothing to `tbl_accounts`.")]
    b += [pb()]

    # ------------------------------------------------------------------ 9.2
    b += form2(
        "9.2", "Invoice List",
        [("Menu", "Accounts > Invoice List"),
         ("Files", "`invoice_list.php` (list) - `mng_invoice.php` (direct invoice, 1,672 lines) - "
                   "`quo_invoice.php` (from a quotation, 1,447) - `dc_invoice.php` (from a challan, 1,457) - "
                   "`mng_credit.php` (record a credit payment, 749) - "
                   "`inc/datatable/ajaxInvoiceList.php` - `invoice_print.php`, `quo_invoice_print.php`"),
         ("Type", "**Transaction - the step that reduces stock**")],
        ["The invoice. One list, but **three screens write into it**, depending on where the invoice came "
         "from - and they set `invoice_type` so you can tell them apart afterwards.",
         "This is the end of the sales chain and the only place stock comes down (see section 8.1). It is "
         "also where a **credit** invoice starts its life: if the customer is not paying now, the balance "
         "sits on the invoice and is paid off later through `mng_credit.php`.",
         "Cash invoices can record a **denomination breakdown** - how many of each note were taken."],
        [["`tbl_invoice`", "The header. `invoice_type` says which screen made it"],
         ["`tbl_invoice_details`", "One row per line"],
         ["`tbl_invoice_pack_details`", "Packing details, on the quotation and challan routes"],
         ["`tbl_invoice_denomination_details`", "The note breakdown for a cash invoice"],
         ["`tbl_invoice_credit_details`", "**One row per part-payment** against a credit invoice"],
         ["`tbl_item_stock`", "**Written** - `<branch>_stock` reduced by the invoiced quantity"],
         ["`tbl_stock_flow`", "**Written** - `trans_type = 'INV'`, one audit row per line"],
         ["`tbl_quotation`, `tbl_sales_order`, `tbl_dc`", "Updated - stamped as invoiced"],
         ["`tbl_item_details`", "Read for the selling price. The stock write on this table is commented out"],
         ["`mst_branch`", "**Read for the column name** and for the print letterhead"],
         ["`mst_supplier_new`, `mst_customer_branch`", "Read - customer and delivery branch"],
         ["`mst_hsn`", "Read - the tax rates"]],
        ["Open **Accounts > Invoice List**. Invoices for your branch with their type, value and balance.",
         "**Three ways in.** *Direct* - **Add New** opens `mng_invoice.php` and you type everything, "
         "`invoice_type = 'I'`. *From a quotation* - the Quotation List's invoice action opens "
         "`quo_invoice.php`, `invoice_type = 'Q'`. *From a challan* - the DC List's invoice action opens "
         "`dc_invoice.php`, `invoice_type = 'D'`.",
         "The reference is built as `INV/0042/BIE/<branch>/<financial year>`; the serial is `max + 1` "
         "**within your branch and financial year**.",
         "Enter or confirm the lines, the transport details and the vehicle number, and any transport "
         "charge.",
         "Choose how it is being paid. **Cash** lets you enter a denomination breakdown. **Credit** sets a "
         "credit amount, a due date and a remark, and leaves `inv_bal_value` outstanding.",
         "Save. Header, lines, packing and denomination rows.",
         "**Then, per line:** the branch's stock column name is read from `mst_branch`; the current stock is "
         "**re-read from the database**; a `tbl_stock_flow` row is written; and the stock column is reduced "
         "by the invoiced quantity.",
         "The source document is stamped so it cannot be invoiced twice.",
         "**Later, for a credit invoice:** `mng_credit.php` records each part-payment. It inserts a "
         "`tbl_invoice_credit_details` row and subtracts the amount from `inv_bal_value`."],
        [["INSERT", "`tbl_invoice`", "`inv_slno`, `inv_finyr`, `inv_refno`, `inv_date`, `quo_id`, `so_id`, "
          "`dc_id`, `supp_id`, `branch_id`, `inv_tot_value`, `inv_bal_value`, `invoice_type`, "
          "`credit_amount`, `credit_due_date`"],
         ["DELETE + INSERT", "`tbl_invoice_details`", "Lines rebuilt on every save"],
         ["DELETE + INSERT", "`tbl_invoice_pack_details`, `tbl_invoice_denomination_details`", "Packing and "
          "note breakdown likewise"],
         ["INSERT", "`tbl_stock_flow`", "`trans_type = 'INV'`, one row per line, with before and after "
          "quantities"],
         ["UPDATE", "`tbl_item_stock`", "**`SET <branch_stock_field> = current - invoiced`** - the current "
          "value is re-read from the database first"],
         ["UPDATE", "`tbl_quotation` / `tbl_sales_order` / `tbl_dc`", "Stamped as invoiced"],
         ["INSERT", "`tbl_invoice_credit_details`", "From `mng_credit.php` - `inv_id`, `paid_amount`, "
          "`paid_date`, payment mode, reference"],
         ["UPDATE", "`tbl_invoice`", "From `mng_credit.php` - `inv_bal_value = inv_bal_value - paid`"],
         ["**(nothing)**", "`tbl_accounts`", "**No journal entry.** See section 9.1"]],
        ["**Every stock figure in the system** - the item picker, the stock reports",
         "**Item Stock History** - through the `tbl_stock_flow` rows",
         "**Credit List** (10.7) - invoices with `inv_bal_value` still outstanding",
         "**Sales List** (10.8) and **Invoice Report** (10.9)",
         "The quotation, order or challan it came from, now marked invoiced",
         "`invoice_print.php` - the customer's invoice, on your branch's letterhead"],
        fields=[
            ["`invoice_type`", "**`I` direct, `Q` from a quotation, `D` from a delivery challan.** A fourth "
             "value `L` exists on 13 old rows and is set by no live screen"],
            ["`inv_tot_value`", "The invoice total. Never changes"],
            ["`inv_bal_value`", "**What is still owed on this invoice.** Starts at the total and is reduced "
             "by each credit payment. This is BIE's only receivables figure"],
            ["`quo_id`, `so_id`, `dc_id`", "Whichever route it came by. The others stay `0`"],
            ["`credit_amount`, `credit_due_date`", "Set when the invoice is on credit"],
            ["`inv_slno`", "`max + 1` within **branch and financial year**"],
            ["`cus_branch_id`", "The customer's delivery branch; `branch_id` is yours"],
        ],
        notes=[
            tip("**The stock deduction is sound.** Like the GRN, the invoice re-reads the current quantity "
                "from the database immediately before subtracting, rather than trusting what the browser "
                "posted. Two invoices saved at the same moment both land correctly."),
            warn("**Nothing stops an invoice going negative.** The deduction is `current - invoiced` with "
                 "no check that `current` is large enough. Invoice more than you have - easy, because the "
                 "sales order reserved nothing (section 6.3) and the challan moved nothing (section 8.5) - "
                 "and the stock column simply goes below zero. The stock reports will show the negative."),
            warn("**Three screens, one table, and they have drifted.** `mng_invoice.php`, "
                 "`quo_invoice.php` and `dc_invoice.php` are three copies of broadly the same 1,500 lines. "
                 "They do not do identical things - only the direct route writes a denomination breakdown "
                 "on every path, and the packing table is written by two of the three. When you change "
                 "invoicing behaviour, change it in all three, and check which one produced the invoice you "
                 "are looking at with `invoice_type`."),
            warn("**The invoice posts nothing to `tbl_accounts`.** No revenue entry, no receivable entry. "
                 "See section 9.1 - this is the whole reason there is no general ledger."),
            note("`tbl_invoice_credit_details` records the part-payments but there is no reversal: a "
                 "payment entered twice reduces `inv_bal_value` twice and can only be undone in the "
                 "database."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 9.3
    b += form2(
        "9.3", "Sales Receipt",
        [("Menu", "Accounts > Sales Receipt"),
         ("Files", "`lst_sales_receipt.php` (list) - `pay_receipt.php` (record a receipt, 693 lines) - "
                   "`inc/datatable/ajaxReceiptlist.php` - `inc/cis_ajax/jquery_modal_recipt_dets.php`, "
                   "`inc/cis_ajax/modal_so_cash_details.php` (view)"),
         ("Type", "Money in - **against a sales order, not an invoice**")],
        ["Money received from a customer. Note what it is recorded against: **the sales order**, not the "
         "invoice. The list is built from `tbl_sales_order`, and each receipt carries an `so_id`.",
         "A receipt captures the amount, the date, how it was paid - cash, cheque, card, transfer - with "
         "the cheque number, card number or reference, and which **ledger** the money went into. That "
         "ledger choice is the one place a customer's `mst_ledger` row is actually used.",
         "Advance payments therefore work naturally: a receipt can be entered against an order before "
         "anything is invoiced."],
        [["`tbl_receipt`", "The receipt header - `so_id`, `supp_id`, serial, financial year, `ledger_id`, "
          "date, mode, amount, cheque details"],
         ["`tbl_receipt_details`", "The breakdown rows"],
         ["`tbl_sales_order`", "Read for the list; **updated** to record the receipt"],
         ["`mst_ledger`", "Read - the ledger dropdown (`ledger_status = 1`)"],
         ["`mst_supplier_new`", "Read - the customer"],
         ["`mst_finyear`", "Read - numbers the receipt"]],
        ["Open **Accounts > Sales Receipt**. Sales orders with what has been received against them.",
         "Choose an order and open `pay_receipt.php`.",
         "Enter the **date**, the **amount**, and the **payment mode**. Cheque, card or transfer details "
         "appear as needed; a cheque starts with `chq_passed` unset.",
         "Choose the **ledger** the money is going into - the bank or cash account.",
         "Save. The receipt header and its detail rows are written, and the sales order is updated.",
         "View what has been received with the receipt modals, or on the sales order print, which shows the "
         "cash details."],
        [["INSERT", "`tbl_receipt`", "`so_id`, `supp_id`, `pay_slno`, `pay_finyr`, `ledger_id`, `pay_date`, "
          "`pay_type`, `pay_amount`, `pay_cardno`, `pay_refno`, `pay_chq_no`, `pay_chq_dt`, `pay_remarks`, "
          "`chq_passed`"],
         ["INSERT", "`tbl_receipt_details`", "The breakdown rows"],
         ["UPDATE", "`tbl_sales_order`", "Recording that a receipt exists"],
         ["**(nothing)**", "`tbl_accounts`", "**No journal entry** - see section 9.1"]],
        ["**Sales Order** - the order shows what has been received",
         "`print_sales_order.php` - the cash details on the order print",
         "The receipt modals on the sales order and receipt lists",
         "**Not** the Credit List, which reads `tbl_invoice.inv_bal_value` instead. See the warning"],
        fields=[
            ["`so_id`", "**The sales order** this is against. Not the invoice"],
            ["`ledger_id`", "Which bank or cash ledger the money went into. The only real use of "
             "`mst_ledger` outside payroll"],
            ["`pay_type`", "Cash, cheque, card or transfer. Decides which reference fields apply"],
            ["`chq_passed`", "Whether a cheque has cleared. **Nothing ever sets it** - the same as on "
             "employee advances (section 3.5)"],
            ["`pay_slno`", "A running number within the financial year"],
        ],
        notes=[
            warn("**Receipts and invoice balances are two separate systems that never meet.** A receipt is "
                 "recorded against the **sales order** and updates nothing on the invoice. An invoice's "
                 "`inv_bal_value` comes down only when a payment is entered through `mng_credit.php` "
                 "(section 9.2). So money taken here does **not** clear an invoice from the Credit List, "
                 "and a credit payment entered there does not appear on the sales order. If you use both "
                 "screens you will double-count. **Pick one route per customer and stay on it.**"),
            warn("**Nothing checks the amount against what is owed.** A receipt can exceed the order value, "
                 "and several receipts can be entered against the same order. There is no reversal - a "
                 "mistaken receipt can only be corrected in the database."),
            note("The ledger dropdown lists **every** active `mst_ledger` row, including the auto-created "
                 "customer, supplier and employee ledgers. Nothing restricts it to bank and cash accounts, "
                 "so it is easy to post a receipt against the wrong kind of ledger - and since nothing "
                 "reads `tbl_receipt.ledger_id` afterwards, nothing will ever tell you."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 9.4
    b += form2(
        "9.4", "GRN Payment Details",
        [("Menu", "Accounts > GRN Payment Details"),
         ("Files", "`grn_payment_details.php` (list) - `grn_pay_receipt.php` (record a payment, 892 lines) "
                   "- `inc/datatable/ajaxGrnPaymentList.php` - `grn_payment_details_pending.php`"),
         ("Type", "Money out - the supplier side, against a GRN")],
        ["The mirror of Sales Receipt, on the buying side. Money paid to a supplier, recorded **against a "
         "GRN** - the goods receipt, not the purchase order and not the supplier's bill.",
         "The list is built from `tbl_grn`, so you see every receipt of goods with what has been paid "
         "against it. The supplier's **credit days** (from their master record, section 2.2) set when the "
         "payment is due.",
         "This, and `inv_bal_value` on the sales side, are the whole of BIE's money tracking."],
        [["`tbl_grn_pay_receipt`", "One row per payment - the GRN, the amount, the date, the mode, the "
          "cheque or reference details"],
         ["`tbl_grn`", "Read for the list; **updated** to record the payment"],
         ["`mst_supplier_new`", "Read - the supplier and their credit days"],
         ["`mst_finyear`", "Read"]],
        ["Open **Accounts > GRN Payment Details**. Every GRN with its value, what has been paid, and what "
         "is outstanding. `grn_payment_details_pending.php` narrows it to the unpaid ones.",
         "Choose a GRN and open `grn_pay_receipt.php`.",
         "Enter the **date**, the **amount** and the **payment mode**, with the cheque number or reference.",
         "Save. A `tbl_grn_pay_receipt` row is written and the GRN is updated.",
         "The list recalculates what is outstanding by summing the payments against the GRN value."],
        [["INSERT", "`tbl_grn_pay_receipt`", "The payment - GRN, amount, date, mode, cheque and reference "
          "details"],
         ["UPDATE", "`tbl_grn`", "Recording that a payment exists against it"],
         ["**(nothing)**", "`tbl_accounts`", "**No journal entry** - see section 9.1"]],
        ["**GRN List** (8.3) - the GRN shows what has been paid",
         "This list, and the pending version of it",
         "**Not** the supplier's ledger, which nothing updates"],
        fields=[
            ["`grn_id`", "The goods receipt this pays for"],
            ["`pay_amount`", "The amount. Summed against the GRN value to get what is outstanding"],
            ["Supplier `supp_credit_days`", "From the supplier master. Sets the due date shown on the list"],
        ],
        notes=[
            warn("**A purchase return does not reduce what you owe.** Section 8.4 explains that "
                 "`purchase_return_add.php` writes no accounting entry of any kind. So goods returned to a "
                 "supplier still show as fully payable on this screen. Check the GRN's returns before "
                 "paying against it."),
            note("**Payment is tracked per GRN, not per supplier and not per bill.** There is no screen "
                 "that totals what is owed to one supplier across every GRN, and the supplier's bill "
                 "(`supp_invoice_add.php`, reached from the purchase order) is a separate record that this "
                 "screen does not reconcile against. To answer *what do we owe this supplier?* you have to "
                 "sum their unpaid GRNs by hand."),
        ])

    return {"heading": "Part 9", "blocks": b}
