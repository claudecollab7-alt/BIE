# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 2  Masters")]
    b += [lead("The people you trade with, and the small lists that fill dropdowns everywhere else. Get these "
               "right first - almost every screen later in the book reads from them.")]
    b += [table([26, 74], ["Screen", "In one line"], [
        ["Customers", "Who you sell to - and their delivery branches"],
        ["Supplier", "Who you buy from - needs approving before purchase screens will use them"],
        ["Box Type", "The kinds of packing box a delivery challan can use"],
        ["State / District / City", "The three-level address list. Each one feeds the next"],
        ["Department / Designation", "The HR structure - designation hangs off department"],
        ["Month Master", "**Working days per month** - the divisor payroll uses"],
    ], bold_first=True)]
    b += [tip("Remember Rule 2 from Part 0: **customers and suppliers are the same table**, "
              "`mst_supplier_new`, told apart by `supp_type` - `C` for customer, `S` for supplier. The two "
              "menu items are two filtered views of one list.")]

    # ------------------------------------------------------------------ 2.1
    b += form2(
        "2.1", "Customers",
        [("Menu", "Masters > Customers"),
         ("Files", "`lst_customers.php` (list) - `mst_customer_new.php` (add / edit) - "
                   "`inc/datatable/ajaxCustomerList.php` (the rows) - `inc/cis_ajax/modal_supplier_dets.php` (view)"),
         ("Type", "Master with child rows")],
        ["A customer here is a company you sell to. The form captures three addresses - the main address, the "
         "delivery address and the bank details - plus GST and PAN.",
         "Underneath, a customer can have **any number of branches**: separate delivery points, each with "
         "its own contact person and address. You add them in the lower half of the form while you type, and "
         "they are saved with the customer.",
         "Saving a customer also **creates its accounts ledger**. You never make one by hand."],
        [["`mst_supplier_new`", "The customer row itself, with `supp_type = 'C'`"],
         ["`mst_ledger`", "**Inserted first.** The customer's accounts ledger; its new id is stored on the "
          "customer row as `ledger_id`"],
         ["`mst_accounts_group`", "Read, to find the `group_type` for the group you chose - it goes onto the "
          "ledger"],
         ["`mst_customer_branch`", "One row per delivery branch of this customer"],
         ["`mst_customer_branch_temp`", "Staging for those branch rows while you are still typing, tagged "
          "with your session id"],
         ["`mst_state`, `mst_district`, `mst_city`", "Read, to fill the three cascading address dropdowns"]],
        ["Open **Masters > Customers**. The list shows active customers with their branches listed beside them.",
         "**Add New** opens `mst_customer_new.php`.",
         "Fill in the company details, then the main address - **State, then District, then City**, each "
         "dropdown reloading from the one above it.",
         "Fill the delivery address and the bank details. If the customer is unregistered, tick **URP** and "
         "the screen puts the literal text `URP` into both GST and PAN fields.",
         "Choose the **accounts group** the ledger should sit under, and the opening balance with its "
         "Debit / Credit side.",
         "In the branches section, type a branch and press add. Each one goes straight into "
         "`mst_customer_branch_temp` against your session - it is not attached to the customer yet.",
         "Save. In one go the screen inserts the ledger, works out the next customer code, inserts the "
         "customer, then copies your staged branches across under the new customer id and clears the "
         "staging rows."],
        [["INSERT", "`mst_ledger`", "**First.** `group_id`, `ledger_name`, `ledger_type`, `open_bal`, "
          "`open_bal_type`. The new `ledger_id` is kept"],
         ["INSERT", "`mst_supplier_new`", "The customer, with `supp_type = 'C'`, `supp_code = 'C' + four "
          "digits`, and the `ledger_id` from above"],
         ["DELETE", "`mst_customer_branch`", "Any existing branch rows for this customer - so the copy below "
          "cannot double up"],
         ["INSERT", "`mst_customer_branch`", "One row per staged branch, now carrying the real `supp_id`"],
         ["DELETE", "`mst_customer_branch_temp`", "Your session's staged rows, once they are safely copied"],
         ["UPDATE", "`mst_ledger`, `mst_supplier_new`", "On edit, both rows are updated together - the "
          "ledger name follows the customer name"],
         ["Soft delete", "`mst_supplier_new`", "Sets `supp_status = 0`. **The ledger is left alone**, so its "
          "balance still appears in the accounts"]],
        ["**Quotation**, **Sales Order**, **Invoice** - the customer dropdown, and the branch dropdown under it",
         "**Delivery Challan** - the delivery address comes from the chosen customer branch",
         "**Sales Receipt** - the ledger dropdown, which is the only place a customer's `ledger_id` is used",
         "**Credit List** and **Sales reports** - grouped by this customer",
         "**Common Settings** - no, that screen lists suppliers only"],
        fields=[
            ["`supp_type`", "Always `C` here. The same table holds suppliers as `S`"],
            ["`supp_code`", "`C0001`, `C0002`... built as `'C'` plus `max(supp_id) + 1` padded to four digits"],
            ["`ledger_id`", "Points at the `mst_ledger` row created with the customer. Everything financial "
             "about this customer hangs off it"],
            ["`group_id`, `open_bal`, `open_bal_type`", "Not stored on the customer - they are the ledger's "
             "fields, filled from this form"],
            ["`supp_gst`, `supp_pan`", "Upper-cased on save, or set to `URP` if the URP box is ticked"],
            ["`supp_status`", "`1` active, `0` deleted"],
        ],
        notes=[
            warn("**The customer code is not safe for two people at once.** It is worked out as "
                 "`max(supp_id) + 1` *before* the insert, so if two users save a customer within the same "
                 "moment they both read the same maximum and both get the same code. The codes are not "
                 "unique in the database, so nothing stops it. Check for duplicates if two people do data "
                 "entry together."),
            note("**Customer and supplier codes share one counter.** The maximum is taken over the whole "
                 "table, not over `supp_type = 'C'`, so customer numbers jump whenever a supplier is added "
                 "in between. The numbers are sequence positions, not counts."),
            note("Because the code is computed rather than read back, `supp_code` and `supp_id` can drift "
                 "apart - a gap in the auto-increment, or a deleted row, is enough. Trust `supp_id` when "
                 "you need identity; `supp_code` is for humans."),
            tip("Unlike the supplier list, **the customer list is not filtered by branch.** Every branch "
                "sees every customer. That is deliberate - a customer can be sold to from anywhere."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 2.2
    b += form2(
        "2.2", "Supplier",
        [("Menu", "Masters > Supplier"),
         ("Files", "`lst_supplier.php` (list) - `mst_supplier_new.php` (add / edit) - "
                   "`mst_supplier_approve.php` (approve) - `inc/datatable/ajaxSupplierList.php`"),
         ("Type", "Master with an approval step")],
        ["A supplier is a company you buy from. The form is the customer form's twin - same addresses, same "
         "bank block - plus two purchase-specific fields: **credit days** and **payment mode**.",
         "The difference is the **approval step**. A newly added supplier is unapproved, and purchase screens "
         "will not offer them until someone opens the approve screen and confirms. That is the control: "
         "whoever adds a supplier cannot also make them usable.",
         "Like the customer, saving a supplier creates its ledger."],
        [["`mst_supplier_new`", "The supplier row, with `supp_type = 'S'`"],
         ["`mst_ledger`", "**Inserted first**, then pointed at by `ledger_id`"],
         ["`mst_accounts_group`", "Read, for the ledger's `group_type`"],
         ["`mst_finyear`", "Read - the active financial year goes into the supplier code"],
         ["`mst_branch`", "Read - the branch code goes into the supplier code too"],
         ["`mst_state`, `mst_district`, `mst_city`", "Read, for the address dropdowns"]],
        ["Open **Masters > Supplier**. The list shows a status column - approved or not.",
         "**Add New** opens `mst_supplier_new.php`. Fill in company, address, bank, GST and PAN, plus "
         "**credit days** and **payment mode**.",
         "Choose the accounts group and opening balance for the ledger.",
         "Save. The ledger goes in first; then the code is built; then the supplier row, stamped with "
         "**your branch** as `company_branch_id`.",
         "Back on the list, open the **approve** action. `mst_supplier_approve.php` shows the supplier "
         "read-only with an Approve button.",
         "Press Approve. `supp_approve_status` becomes `1` and your user id and the timestamp are recorded.",
         "The supplier now appears in the Purchase Order and GRN dropdowns."],
        [["INSERT", "`mst_ledger`", "**First**, exactly as for a customer. The new `ledger_id` is kept"],
         ["INSERT", "`mst_supplier_new`", "`supp_type = 'S'`, `company_branch_id = your branch`, "
          "`supp_credit_days`, `supp_pay_mode`, `ledger_id`, and the long `supp_code` below. "
          "`supp_approve_status` is left at `0`"],
         ["UPDATE", "`mst_ledger`", "On edit - group, name, type, opening balance"],
         ["UPDATE", "`mst_supplier_new`", "On edit - everything except the code, which never changes"],
         ["UPDATE", "`mst_supplier_new`", "On approve - `supp_approve_status = 1`, `supp_approve_by`, "
          "`supp_approve_dt`"],
         ["UPDATE", "`mst_supplier_new`", "From **Common Settings** - `discount_apply`"],
         ["Soft delete", "`mst_supplier_new`", "Sets `supp_status = 0`. The ledger stays"]],
        ["**Purchase Order** and **Direct PO** - the supplier dropdown, **approved suppliers only**",
         "**GRN** - the supplier on the goods receipt, and the credit days that set the due date",
         "**GRN Payment Details** - what is owed to this supplier, through the ledger",
         "**Item Details** - a supplier can be linked to an item as its source",
         "**Common Settings** - this supplier appears in the discount tick list",
         "**Cost Worksheet** and purchase reports"],
        fields=[
            ["`supp_code`", "Long and structured: `S/0042/BIE/HO/2025-26` - the letter, the running number, "
             "the company, **your branch code** and **the active financial year**"],
            ["`supp_approve_status`", "`0` = added but not usable. `1` = approved. Purchase screens filter on "
             "this"],
            ["`company_branch_id`", "The branch that created the supplier. Decides who can see them - see "
             "the note below"],
            ["`supp_credit_days`", "Feeds the payment due date on a GRN"],
            ["`supp_pay_mode`", "Default payment method, offered on the payment screens"],
            ["`discount_apply`", "Set from **Common Settings**, not from this form. `1` means the item "
             "discount fills in automatically on quotations and invoices"],
        ],
        notes=[
            note("**Who sees which suppliers.** The list shows everything to user id `1` **or to anyone "
                 "whose branch is Head Office**; every other branch sees only suppliers with their own "
                 "`company_branch_id`. So an ordinary Head Office user sees all three branches' suppliers, "
                 "while a Kerala user sees only Kerala's. If a Kerala buyer cannot find a supplier that "
                 "exists, this is why - the supplier was added at another branch."),
            warn("**Approval is a single click with no second pair of eyes.** Nothing stops the person who "
                 "added the supplier from approving them, and nothing un-approves a supplier whose details "
                 "are later edited. If bank details change on an approved supplier, the change goes live "
                 "with no re-approval. Watch this one."),
            warn("**On edit, the ledger is updated using the `ledger_id` posted by the form**, not the one "
                 "read back from the supplier row. The screen does fetch the correct id and then does not "
                 "use it. In normal use the hidden field holds the right value, so this is harmless - but "
                 "it means a bad or missing hidden field would rewrite the wrong ledger. Worth tidying."),
            note("The supplier code carries the branch and the financial year at the time of creation. That "
                 "makes it a useful audit label, but it also means the code is no guide to where the "
                 "supplier is used now."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 2.3 the small lookups
    b += [h2("2.3  The small lists")]
    b += [p("Six screens with the same shape: a list, an add form, a pencil to edit and a dustbin that sets "
            "the status column to `0`. There is nothing to learn about them individually - what matters is "
            "**where each one is used**, because deleting a row here can empty a dropdown somewhere else.")]

    b += mini("2.3.1", "Box Type", "`mst_boxtype.php`",
              "The kinds of packing box a delivery challan can be packed into - Corrugated Box, Wooden Box "
              "and so on.",
              "`tbl_dc_package_box`",
              "`box_id`, `box_name`, `box_status`",
              "**Delivery Challan** - the packing section, where each box on the challan picks a type here")
    b += [sp()]

    b += mini("2.3.2", "State", "`mst_state.php`",
              "The states. The top level of the three-level address list, and the source of the state code "
              "that decides CGST/SGST versus IGST.",
              "`mst_state`",
              "`state_id`, `country_id`, `state_name`, `state_shname`, `state_code`, `state_status`",
              "Every address block in the system - customer, supplier, employee, delivery. Also the tax "
              "calculation on quotations and invoices")
    b += [sp()]

    b += mini("2.3.3", "District", "`mst_district.php`",
              "Districts, each belonging to one state. The middle level.",
              "`mst_district`",
              "`district_id`, `district_name`, `country_id`, `state_id`, `district_status`",
              "The District dropdown on every address block, reloaded whenever the State above it changes")
    b += [sp()]

    b += mini("2.3.4", "City", "`mst_city.php`",
              "Cities, each belonging to one district. The bottom level.",
              "`mst_city`",
              "`city_id`, `country_id`, `state_id`, `district_id`, `city_name`, `city_status`",
              "The City dropdown, reloaded from the District above it by "
              "`inc/cis_ajax/jquery_select_city.php`")
    b += [sp()]

    b += mini("2.3.5", "Department", "`mst_department.php`",
              "The departments employees belong to.",
              "`mst_department`",
              "`department_id`, `department_name`, plus the usual `created_by` / `modify_by` audit pair. "
              "Status column is `rec_del_status`",
              "**Employee** - the Department dropdown. **Designation** - designations hang off a department. "
              "Salary and attendance reports group by it")
    b += [sp()]

    b += mini("2.3.6", "Designation", "`mst_designation.php`",
              "Job titles. **Each one belongs to a department**, so the Designation dropdown on the employee "
              "form only shows titles for the department already chosen.",
              "`mst_designation`",
              "`designation_id`, `department_id`, `designation_name`, audit pair, `rec_del_status`",
              "**Employee** - the Designation dropdown. Printed on salary documents")
    b += [sp()]
    b += [note("The three address tables use `state_status`, `district_status` and `city_status`. Department "
               "and Designation use `rec_del_status` instead. Box Type uses `box_status`. Same idea, "
               "different column name on almost every table - the delete endpoint is told which column to "
               "set, so each screen carries its own name.")]
    b += [warn("**A soft-deleted lookup row disappears from new dropdowns but stays on old documents.** The "
               "document stored the id, and the print re-reads the row by id without checking the status, so "
               "old prints still show the right name. But if you delete a district that customers are "
               "pointing at, editing one of those customers will show the district box empty, and saving "
               "will blank it. Prefer renaming a lookup row to deleting it.")]
    b += [pb()]

    # ------------------------------------------------------------------ 2.4
    b += form2(
        "2.4", "Month Master",
        [("Menu", "Masters > Month Master"),
         ("Files", "`mst_month_master.php` (list) - `mst_month_master_add.php` (add / edit)"),
         ("Type", "A calendar the payroll depends on")],
        ["One row per month: how many days it has, how many are **working days**, how many are holidays, and "
         "how many are function holidays.",
         "It looks like a trivial list. It is not - **the working-days figure is the divisor payroll uses.** "
         "A month with no row, or with the wrong working-days count, gives every salary for that month the "
         "wrong value. Fill this in before you run a salary for a new month."],
        [["`tbl_month_master`", "One row per month and year, with the day counts"]],
        ["Open **Masters > Month Master**. The list shows month name and year, newest first.",
         "**New Month** opens `mst_month_master_add.php`.",
         "Pick the **month** and the **year**, then enter total days, working days, holidays and function "
         "holidays.",
         "Save. The screen first checks that no active row already exists for that month and year, so you "
         "cannot enter September 2025 twice.",
         "Edit works the same way, excluding the row being edited from the duplicate check.",
         "The dustbin sets `month_master_status = 0` - the month drops out of the list and out of the "
         "attendance reports."],
        [["INSERT", "`tbl_month_master`", "`month`, `year`, `total_days`, `working_days`, `holidays`, "
          "`function_holidays`, `created_by`, `created_dtm`"],
         ["UPDATE", "`tbl_month_master`", "The same figures plus `modify_by`, `modify_dtm`"],
         ["Soft delete", "`tbl_month_master`", "Sets `month_master_status = 0`"]],
        ["**Attendance Report (PF)**, **Attendance Report (Without PF)**, **OT Report** and **EPF Report** - "
         "all four read this row for the month you pick",
         "**Employee Salary** - the per-day rate comes from the monthly salary divided by the working days "
         "here",
         "The month dropdown on the attendance screens is built from these rows, so **a month with no row "
         "cannot be selected at all**"],
        fields=[
            ["`month`", "Stored as a **number**, 1 to 12. The list turns it into a name for display"],
            ["`year`", "Four digits. Together with `month` it must be unique among active rows"],
            ["`total_days`", "Calendar days in the month - 28 to 31"],
            ["`working_days`", "**The payroll divisor.** Days actually worked in a full month"],
            ["`holidays`", "Weekly offs and public holidays"],
            ["`function_holidays`", "Extra company holidays declared for that month"],
        ],
        notes=[
            warn("**No row, no salary.** The attendance and salary screens list only the months that exist "
                 "here, so forgetting to add next month silently blocks payroll rather than warning anyone. "
                 "Add the row at the start of every month."),
            warn("**Changing `working_days` after salaries are calculated changes past figures.** The "
                 "reports recalculate from this row every time they are opened - nothing is frozen. If you "
                 "correct a working-days count for a closed month, the printed and the on-screen figures "
                 "for that month will no longer agree."),
            note("`total_days`, `holidays` and `function_holidays` are stored but are not what drives the "
                 "pay calculation - `working_days` is. They do not have to add up, and nothing checks that "
                 "they do."),
        ])

    return {"heading": "Part 2", "blocks": b}
