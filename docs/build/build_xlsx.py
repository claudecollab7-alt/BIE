# -*- coding: utf-8 -*-
"""BIE ERP documentation workbook - all 11 menus, all 63 screens."""
import json, os
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter
from xlsx_data_p1 import SETTINGS, MASTERS, HR, TABLES_P1
from xlsx_data_p2 import ITEMS, ATTEN, SALES, PURCHASE, STORES, TABLES_P2
from xlsx_data_p3 import ACCOUNTS, REPORTS, SERVICE, TABLES_P3

OUT = "/home/user/bie/docs/BIE_ERP_Documentation.xlsx"
F = "Arial"

INK, ACCENT, ACCENT2, MUTED = "1A1A1A", "18506B", "0F6E52", "5B6470"
HEADBG, ZEBRA, RULE = "18506B", "F4F7F9", "C9D2DA"

thin = Side(style="thin", color=RULE)
BOX = Border(left=thin, right=thin, top=thin, bottom=thin)

def style_header(ws, row, ncols):
    for c in range(1, ncols + 1):
        cell = ws.cell(row=row, column=c)
        cell.font = Font(name=F, size=10, bold=True, color="FFFFFF")
        cell.fill = PatternFill("solid", fgColor=HEADBG)
        cell.alignment = Alignment(vertical="center", horizontal="left", wrap_text=True)
        cell.border = BOX
    ws.row_dimensions[row].height = 30

def write_row(ws, row, values, zebra=False, wrap=True, size=9, bold_cols=()):
    for i, v in enumerate(values, start=1):
        cell = ws.cell(row=row, column=i, value=v)
        cell.font = Font(name=F, size=size, bold=(i in bold_cols), color=INK)
        cell.alignment = Alignment(vertical="top", horizontal="left", wrap_text=wrap)
        cell.border = BOX
        if zebra:
            cell.fill = PatternFill("solid", fgColor=ZEBRA)

def set_widths(ws, widths):
    for i, w in enumerate(widths, start=1):
        ws.column_dimensions[get_column_letter(i)].width = w

def title_block(ws, title, subtitle):
    ws["A1"] = title
    ws["A1"].font = Font(name=F, size=15, bold=True, color=ACCENT)
    ws["A2"] = subtitle
    ws["A2"].font = Font(name=F, size=9.5, italic=True, color=MUTED)
    ws.row_dimensions[1].height = 22
    ws.row_dimensions[2].height = 16
    ws.row_dimensions[3].height = 6
    ws.freeze_panes = "A5"

MENU_COLS = ["#", "Sub-menu (form)", "PHP file(s)", "Type", "What it does (short)",
             "Tables used", "What changes in the tables", "Full flow", "Where it reflects",
             "Key notes / traps"]
MENU_W = [6, 26, 30, 15, 46, 34, 54, 62, 48, 46]

wb = Workbook()

# ---------- README ----------
ws = wb.active
ws.title = "README"
set_widths(ws, [34, 104])
title_block(ws, "BIE ERP - Documentation Workbook",
            "Complete  ·  all 11 menus, all 63 screens  ·  companion to the Word document")
r = 5
readme = [
 ("What this workbook is", "One row per screen, one sheet per menu, covering all 63 live screens. The same material as the Word document, laid out for quick lookup rather than for reading straight through."),
 ("How to read a menu sheet", "Columns run left to right in the order you need them: what the screen IS, then which TABLES it uses, then what it WRITES, then the FLOW, then where it REFLECTS, then the TRAPS."),
 ("", ""),
 ("SHEETS", ""),
 ("Menu Index", "All 11 menus and 63 live sub-menus, with the file each opens and which part documents it."),
 ("1. Settings", "3 screens - who may log in, which branches exist, and which suppliers get an automatic discount."),
 ("2. Masters", "9 screens - customers, suppliers, the address and org lookups, and the payroll calendar."),
 ("3. HR Management", "9 screens - the employee record, salary packages, advances, debit notes and shifts."),
 ("4. Item Masters", "10 screens - the classification lists, the item record, kits, and the only screen that can change a price."),
 ("5. Attendance+Salary", "5 screens - the biometric import, the payroll run, overtime and the PF return."),
 ("6. Sales", "2 screens - the versioned quotation and the sales order generated from it."),
 ("7. Purchase", "2 screens - the direct purchase order, and the pricing step between an indent and an order."),
 ("8. Stores", "4 screens - the indent, the GRN (the only screen that increases stock), the return and the challan."),
 ("9. Accounts", "3 screens - the invoice, money in from customers, money out to suppliers. Read 9.1 first."),
 ("10. Report", "14 reports, every one read-only. Six on stock, five on sales, one each on cost, discounts and quotations."),
 ("11. Service", "2 screens - which spares fit which product, and repairs booked against an invoice."),
 ("Tables Reference", "Every table the live menu touches - 89 of them - what it holds, and which screens use it."),
 ("Coverage", "All 11 menus, complete, with live formulas."),
 ("", ""),
 ("THE ONE THING TO UNDERSTAND FIRST", ""),
 ("BIE is multi-BRANCH by COLUMN", "Most systems add a branch_id column and store one row per branch. BIE does not. It stores ONE ROW PER ITEM and gives each branch its own SET OF COLUMNS - ho_stock, kl_stock, rjpm_stock, and the same again for every price, discount, margin and bin location."),
 ("mst_branch stores the names", "The branch row does not hold the stock; it holds the NAME OF THE COLUMN that holds the stock. A screen reads branch_stock_field from mst_branch, gets back the text 'ho_stock', and builds its SQL around it. That substitution is every branch-aware query in BIE."),
 ("Why it matters", "When a figure looks right for one branch and wrong for another, this is why - the two branches are literally different columns, and a screen that hardcodes ho_stock is right at Head Office and wrong everywhere else."),
 ("", ""),
 ("LEGEND", ""),
 ("R / W / RW", "Table is Read / Written / both by that screen."),
 ("Soft delete", "The dustbin icon sets a status column to 0 via inc/cis_ajax/jquery_delete_records.php (or jquery_delete_records_hr.php for HR). Nothing is physically removed, and lists filter on status = 1."),
 ("(W) on mst_ledger", "The screen auto-creates an accounts ledger BEFORE writing the master row, and stores its ledger_id on the master."),
 ("tbl_accounts", "Looks like a central journal, and is WRITE-ONLY. Four payroll files insert into it and nothing anywhere reads it. BIE has no Day Book, no Ledger Book and no Trial Balance - see sheet 9, section 9.1."),
 ("_temp tables", "Session-tagged staging for document lines while a form is open. Copied to the real table on save, then cleared."),
 ("", ""),
 ("FIVE RULES THAT HOLD NEARLY EVERYWHERE", ""),
 ("1. Nothing is really deleted", "Every master carries a status column; deleting sets it to 0. The one exception is tbl_user_rights, which is hard-deleted and rebuilt."),
 ("2. Customers and suppliers share a table", "Both are rows in mst_supplier_new - note the _new. supp_type: C = customer, S = supplier. There is no customer table."),
 ("3. A party or an employee is also a ledger", "Customer, supplier and employee masters INSERT their mst_ledger row FIRST, then store the new ledger_id on the master row."),
 ("4. Documents with lines use a _temp staging table", "Lines are staged against your session id while you type, copied under the new header id on save, then cleared. Abandoned documents leave harmless orphan rows."),
 ("5. Most forms write themselves", "Few screens contain a hand-written INSERT. CIS_InsertRecord and CIS_UpdateRecord DESCRIBE the table and keep only posted values whose NAME MATCHES A REAL COLUMN. So the name of the input box IS the name of the column - and a typo is silently dropped."),
 ("", ""),
 ("SCOPE", ""),
 ("Schema source", "db/bie.sql - the current dump in the repository. 105 tables."),
 ("Included", "Only files the live menu actually opens - 63 screens across 11 menus."),
 ("Excluded", "Eight mst_sub_menu rows with no URL. Seven are hidden; one, Bank Account Details under Settings, is set to show but goes nowhere."),
 ("Field detail", "The fields that DRIVE behaviour, plus every table write in full. Not an exhaustive inventory of every input on every form."),
 ("", ""),
 ("WHAT ACTUALLY MOVES STOCK", ""),
 ("Only four screens change it", "GRN (grn_add.php) puts stock UP. The three invoice screens - quo_invoice.php, dc_invoice.php, mng_invoice.php - put it DOWN. Nothing else in BIE touches tbl_item_stock's quantity columns, with one exception: Item Price Update writes the stock column too (see 4.10)."),
 ("A third and fourth gap", "REPAIR INDENT consumes parts and moves no stock either (11.2). And the price-update screen writes the stock column from a form field, so a price change can silently reset a quantity (4.10)."),
 ("Two gaps, not design", "A DELIVERY CHALLAN takes goods out of the building and only the later invoice reduces stock, so anything despatched but not invoiced is counted twice. A PURCHASE RETURN sends goods back and NOTHING reduces stock at all, so the quantity stays up permanently."),
 ("When a figure is wrong", "Check in this order: (1) challans raised but not invoiced, (2) purchase returns, (3) repair indents, (4) a price change made through Item Price Update, (5) whether you are reading tbl_item_details.item_curr_stock (stale) instead of the branch column on tbl_item_stock (live)."),
 ("", ""),
 ("THERE IS NO GENERAL LEDGER", ""),
 ("What you might expect", "A Day Book, a Ledger Book, a Trial Balance, customer and supplier balances. BIE has none of them - no such screen exists, in the menu or as an unlinked file."),
 ("What is actually there", "Money is tracked PER DOCUMENT. What a customer owes is tbl_invoice.inv_bal_value, reduced by credit payments and summed by the Credit List. What you owe a supplier is the GRN value less its payments, on GRN Payment Details. Nothing totals either by party."),
 ("The trap", "SALES RECEIPT records money against the SALES ORDER and never touches the invoice, while the Credit List reads only invoice credit payments. Use both screens and you double-count. Pick one route per customer."),
 ("", ""),
 ("Related system", "Benzear is the sister codebase this one was forked from. Benzear is multi-COMPANY and keys on company_id; BIE is multi-BRANCH and keys on branch_id. A few unused company_id columns survive in BIE from that fork."),
]
for label, text in readme:
    c1 = ws.cell(row=r, column=1, value=label)
    c2 = ws.cell(row=r, column=2, value=text)
    is_head = label.isupper() and text == ""
    c1.font = Font(name=F, size=10, bold=True, color=ACCENT if is_head else INK)
    c2.font = Font(name=F, size=9.5, color=INK)
    c1.alignment = Alignment(vertical="top", wrap_text=True)
    c2.alignment = Alignment(vertical="top", wrap_text=True)
    if is_head:
        c1.fill = PatternFill("solid", fgColor="E8EEF2")
        c2.fill = PatternFill("solid", fgColor="E8EEF2")
    ws.row_dimensions[r].height = 42 if len(text) > 220 else (30 if len(text) > 110 else (18 if text else 12))
    r += 1

# ---------- MENU INDEX ----------
menu = json.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "menu.json")))
PHASE = {1: "Part 1", 2: "Part 2", 23: "Part 3", 3: "Part 4", 24: "Part 5",
         4: "Part 6", 19: "Part 7", 20: "Part 8", 21: "Part 9", 22: "Part 10",
         25: "Part 11"}

def clean(v, sep):
    """Strip the literal and real CR/LF that some sm_url and sm_name values carry."""
    t = (v or "")
    for ch in ("\\r\\n", "\\r", "\\n", "\r", "\n", "\t"):
        t = t.replace(ch, sep)
    return t.strip()
ws = wb.create_sheet("Menu Index")
set_widths(ws, [8, 26, 8, 40, 42, 20])
title_block(ws, "Menu Index - the complete live menu tree",
            "From mst_main_menu and mst_sub_menu. 11 menus, 63 live sub-menus. This is what the sidebar draws, filtered by tbl_user_rights - except for administrators, who see everything.")
for i, h in enumerate(["Order", "Main menu", "sm_id", "Sub-menu", "Opens (PHP file)", "Documented in"], start=1):
    ws.cell(row=5, column=i, value=h)
style_header(ws, 5, 6)
r = 6
for g in menu:
    st = PHASE.get(g["mm_id"], "")
    subs = [s for s in g["subs"] if s.get("url")]
    if not subs:
        write_row(ws, r, [g["mm_index"], g["name"], "-", "(no live sub-menus)", "-", st])
        r += 1
        continue
    for j, s in enumerate(subs):
        nm = clean(s["name"], " ")
        url = clean(s["url"], "")
        write_row(ws, r, [g["mm_index"] if j == 0 else "", g["name"] if j == 0 else "",
                          s["sm_id"], nm, url, st if j == 0 else ""], zebra=(j % 2 == 1))
        if j == 0:
            ws.cell(row=r, column=2).font = Font(name=F, size=9, bold=True, color=ACCENT)
        r += 1
    r += 1
ws.auto_filter.ref = "A5:F%d" % (r - 1)

# ---------- MENU SHEETS ----------
def menu_sheet(name, title, subtitle, rows):
    ws = wb.create_sheet(name)
    set_widths(ws, MENU_W)
    title_block(ws, title, subtitle)
    for i, h in enumerate(MENU_COLS, start=1):
        ws.cell(row=5, column=i, value=h)
    style_header(ws, 5, len(MENU_COLS))
    r = 6
    for k, row in enumerate(rows):
        write_row(ws, r, row, zebra=(k % 2 == 1), bold_cols=(2,))
        longest = max(len(str(v)) for v in row)
        lines = max(str(v).count("\n") + 1 for v in row)
        ws.row_dimensions[r].height = min(430, max(64, lines * 12.5, longest / 5.2))
        r += 1
    ws.auto_filter.ref = "A5:%s%d" % (get_column_letter(len(MENU_COLS)), r - 1)
    return ws

menu_sheet("1. Settings", "Menu 1  ·  Settings  (mm_id = 1)",
           "3 screens, and they decide everything else: who may log in, which branches exist, and which suppliers get an automatic discount. Administrators only. A fourth item, Bank Account Details, appears in the menu but has no file behind it.",
           SETTINGS)

menu_sheet("2. Masters", "Menu 2  ·  Masters  (mm_id = 2)",
           "9 screens. Fill in order: State > District > City, then Department > Designation, then Customers and Suppliers. Month Master is not a lookup - it is the payroll calendar, and payroll stops without it.",
           MASTERS)

menu_sheet("3. HR Management", "Menu 3  ·  HR Management  (mm_id = 23)",
           "9 screens. Setup: Labour Master, Salary Package, Shift Wise. Per person: the Employee form, which alone writes five tables. Ongoing: advances and debit notes, both of which post to the central journal.",
           HR)

menu_sheet("4. Item Masters", "Menu 4  \u00b7  Item Masters  (mm_id = 3)",
           "10 screens. Six one-field lists, HSN (which sets the tax), the item record - which creates the branch stock row - kits, and the price-change screen. An item lives in TWO tables: tbl_item_details says what it is, tbl_item_stock says what it costs and how many there are, per branch. The price and stock columns on tbl_item_details are STALE.",
           ITEMS)

menu_sheet("5. Attendance+Salary", "Menu 5  \u00b7  Attendance and Salary  (mm_id = 24)",
           "5 screens. One imports punches, two calculate and pay, two report. Six things must be in place first: the employee and their bio_id, their CTC, their salary package, the month's working days, the punches, and the payment. Nothing is frozen until Issue Salary is pressed.",
           ATTEN)

menu_sheet("6. Sales", "Menu 6  \u00b7  Sales  (mm_id = 4)",
           "2 menu items, a longer chain: quotation (versioned, verified then approved), sales order generated from it, delivery challan, invoice. STOCK DOES NOT MOVE UNTIL THE INVOICE - not on the quotation, not on the order, and not on the challan.",
           SALES)

menu_sheet("7. Purchase", "Menu 7  \u00b7  Purchase  (mm_id = 19)",
           "2 screens, two routes to the same place. Either raise a purchase order directly, or let a store indent be priced up through PO Prepare. Both end at a purchase order waiting for goods, and both move no stock - the GRN does that.",
           PURCHASE)

menu_sheet("8. Stores", "Menu 8  \u00b7  Stores  (mm_id = 20)",
           "4 screens, and the only place in BIE where stock goes UP. The GRN increases it; the store indent, the purchase return and the delivery challan all move nothing - and for the last two, that is a gap rather than a design.",
           STORES)

menu_sheet("9. Accounts", "Menu 9  \u00b7  Accounts  (mm_id = 21)",
           "3 screens: the invoice - the step that reduces stock - money in from customers, and money out to suppliers. Read the README's 'There is no general ledger' section first: tbl_accounts is write-only, and money is tracked per document rather than per party.",
           ACCOUNTS)

menu_sheet("10. Report", "Menu 10  \u00b7  Report  (mm_id = 22)",
           "14 reports and EVERY ONE IS READ-ONLY - not one writes a row. Six on stock, five on sales, one each on purchase cost, discounts and quotations. All of them print with the heading 'Day Book', which is a hardcoded string and means nothing.",
           REPORTS)

menu_sheet("11. Service", "Menu 11  \u00b7  Service  (mm_id = 25)",
           "2 screens for after-sales work: which spare parts fit which product, and repairs booked in against an invoice the customer already has. The repair consumes parts and moves no stock.",
           SERVICE)

# ---------- TABLES REFERENCE ----------
ws = wb.create_sheet("Tables Reference")
set_widths(ws, [32, 14, 8, 74, 40])
title_block(ws, "Tables Reference - complete",
            "Every table the live menu touches, across all 11 menus. The database holds 105 in total; the remainder belong to legacy screens the menu does not open.")
for i, h in enumerate(["Table", "Kind", "Cols", "What it holds", "Used by"], start=1):
    ws.cell(row=5, column=i, value=h)
style_header(ws, 5, 5)
r = 6
for k, row in enumerate(TABLES_P1 + TABLES_P2 + TABLES_P3):
    write_row(ws, r, row, zebra=(k % 2 == 1), bold_cols=(1,))
    ws.row_dimensions[r].height = 30 if len(row[3]) > 78 else 16
    r += 1
ws.auto_filter.ref = "A5:E%d" % (r - 1)

# ---------- COVERAGE ----------
COV = [
 [1,  "Settings",              1,  3,  "Part 1",  "Phase 1", "Done"],
 [2,  "Masters",               2,  9,  "Part 2",  "Phase 1", "Done"],
 [3,  "HR Management",         23, 9,  "Part 3",  "Phase 1", "Done"],
 [4,  "Item Masters",          3,  10, "Part 4",  "Phase 2", "Done"],
 [5,  "Attendance and Salary", 24, 5,  "Part 5",  "Phase 2", "Done"],
 [6,  "Sales",                 4,  2,  "Part 6",  "Phase 2", "Done"],
 [7,  "Purchase",              19, 2,  "Part 7",  "Phase 2", "Done"],
 [8,  "Stores",                20, 4,  "Part 8",  "Phase 2", "Done"],
 [9,  "Accounts",              21, 3,  "Part 9",  "Phase 3", "Done"],
 [10, "Report",                22, 14, "Part 10", "Phase 3", "Done"],
 [11, "Service",               25, 2,  "Part 11", "Phase 3", "Done"],
]
NOTES = {
 "Settings": "Administrators only. One menu item has no file behind it",
 "Item Masters": "The item record creates the branch stock row",
 "Attendance and Salary": "Nothing is frozen until Issue Salary",
 "Sales": "Stock does not move until the invoice",
 "Masters":  "Month Master is the payroll calendar, not a lookup",
 "Report":   "Largest menu - 14 reports, all read-only",
 "Accounts": "No general ledger - money is tracked per document",
 "Service":  "The repair consumes parts and moves no stock",
 "Stores":   "Where stock actually moves",
}
ws = wb.create_sheet("Coverage")
set_widths(ws, [12, 30, 10, 12, 12, 12, 12, 46])
title_block(ws, "Coverage tracker",
            "All 11 menus and all 63 live screens are documented. Menu order follows the sidebar; the totals below are live formulas.")
for i, h in enumerate(["Order", "Main menu", "mm_id", "Screens", "Word part", "Phase", "Status", "Notes"], start=1):
    ws.cell(row=5, column=i, value=h)
style_header(ws, 5, 8)
r = 6
for k, row in enumerate(COV):
    write_row(ws, r, row + [NOTES.get(row[1], "")], zebra=(k % 2 == 1), bold_cols=(2,))
    ws.cell(row=r, column=7).font = Font(name=F, size=9, bold=True,
                                         color=ACCENT2 if row[6] == "Done" else MUTED)
    r += 1
first, last = 6, r - 1
r += 1
tot = [
 ("Menus in the system", "=COUNTA(B%d:B%d)" % (first, last)),
 ("Menus documented", '=COUNTIF(G%d:G%d,"Done")' % (first, last)),
 ("Screens in the system", "=SUM(D%d:D%d)" % (first, last)),
 ("Screens documented", '=SUMIF(G%d:G%d,"Done",D%d:D%d)' % (first, last, first, last)),
 ("Screens remaining", '=SUMIF(G%d:G%d,"Pending",D%d:D%d)' % (first, last, first, last)),
 ("Percent complete", '=IFERROR(SUMIF(G%d:G%d,"Done",D%d:D%d)/SUM(D%d:D%d),0)' % (first, last, first, last, first, last)),
]
for label, formula in tot:
    c1 = ws.cell(row=r, column=1, value=label)
    ws.merge_cells(start_row=r, start_column=1, end_row=r, end_column=2)
    c2 = ws.cell(row=r, column=3, value=formula)
    ws.merge_cells(start_row=r, start_column=3, end_row=r, end_column=4)
    c1.font = Font(name=F, size=10, bold=True, color=ACCENT)
    c2.font = Font(name=F, size=10, bold=True, color=INK)
    c2.alignment = Alignment(horizontal="center")
    for c in (c1, c2):
        c.fill = PatternFill("solid", fgColor="E8EEF2")
        c.border = BOX
    if label == "Percent complete":
        c2.number_format = "0.0%"
    r += 1

for s in wb.worksheets:
    s.sheet_view.showGridLines = False
    s.page_setup.orientation = "landscape"
    s.page_setup.fitToWidth = 1
    s.page_setup.fitToHeight = 0
    s.sheet_properties.pageSetUpPr.fitToPage = True
    s.page_margins.left = s.page_margins.right = 0.3
    s.page_margins.top = s.page_margins.bottom = 0.4
    if s.title != "README":
        s.print_title_rows = "5:5"

wb.save(OUT)
print("saved", OUT, os.path.getsize(OUT), "bytes")
