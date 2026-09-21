# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 5  Attendance and Salary")]
    b += [lead("Five screens that turn biometric punches into pay. One imports, two calculate and pay, two "
               "report. This is the only part of BIE where money is created rather than recorded.")]

    b += [h2("5.1  How payroll fits together")]
    b += [p("Six things have to be in place before anyone can be paid. Four of them are in Parts 2 and 3.")]
    b += [table([30, 70], ["What", "Where it comes from"], [
        ["The person", "**Employee** (3.1) - and critically their `bio_id`, which is how a punch finds them"],
        ["Their CTC", "**Employee Salary Setting** (3.4) - the current `tbl_emp_salary` row"],
        ["How it splits", "**Salary Package** (3.3) - the percentages"],
        ["The month's shape", "**Month Master** (2.9) - `working_days`, the divisor"],
        ["The punches", "**Import Attendance** (5.2) - this part"],
        ["The payment", "**Attendance Report PF / Without PF** (5.3) - this part"],
    ], bold_first=True)]
    b += [p("And the calculation, in full. It is worth having in front of you when a figure looks wrong:")]
    b += [code([
        "earned    = CTC / working_days x paid_days      (sal_period = 2, monthly)",
        "earned    = CTC x paid_days                     (otherwise, daily rate)",
        "",
        "basic     = earned x sal_basic%                 -- 40%",
        "da        = earned x sal_da%                    -- 25%",
        "hra       = earned x sal_hra%                   -- 25%",
        "conveyance= earned x sal_convey%                -- 10%",
        "",
        "EPF       = (basic + da) x sal_pf%              -- 12% of basic+DA, not of earned",
        "",
        "net       = earned - EPF - advance recovered - debit note recovered",
    ])]
    b += [warn("**Nothing is frozen until you press Issue Salary.** Every figure above is recalculated from "
               "the live master data each time a report is opened. Change a CTC, a package percentage or a "
               "working-days count, and *last month's* report changes too - until that month has been "
               "issued, at which point the figures are copied into `tbl_emp_monthly_salary` and stop "
               "moving. Issue each month before you touch the masters for the next.")]
    b += [pb()]

    # ------------------------------------------------------------------ 5.2
    b += form2(
        "5.2", "Attendance Update (Import Attendance)",
        [("Menu", "Attendance and Salary > Attendance Update"),
         ("Files", "`import_attendance.php` - also `attendance_new.php` and `attendance_report.php`"),
         ("Type", "Import - a two-stage batch")],
        ["You upload the biometric machine's Excel export. The screen reads it, matches each punch to an "
         "employee, pairs the punches into a check-in and a check-out for each day, and works out how long "
         "each person was there.",
         "It is **two stages, not one**. Stage one copies the raw punches in, one row per punch, untouched. "
         "Stage two walks those rows and builds the day records. Keeping the raw punches means an import can "
         "be re-run and a disputed day can be traced back to the actual machine readings."],
        [["`tbl_attendance_import_new`", "**Stage one.** One row per raw punch: `bio_id`, "
          "`biometric_time`, and later `check_in_out` (IN or OUT) and `sync_status` once processed"],
         ["`tbl_attendance`", "**Stage two.** One row per employee per work date: `check_in`, `check_out`, "
          "`work_time`, `break_time`, `late_in`, `late_out`, `early_in`, `early_out`"],
         ["`mst_employee`", "Read - to turn a `bio_id` into an `emp_id`"],
         ["`mst_shifts`", "Read - only `check_in` and `check_out`, to measure lateness"]],
        ["Open **Attendance and Salary > Attendance Update** and choose the machine's Excel file.",
         "**Stage one.** Each row is read; column 4 is the `bio_id`. The employee is looked up by "
         "`bio_id`. Any existing punch for the same `bio_id` at the same timestamp is deleted first, and the "
         "day's `tbl_attendance` row is cleared - **so re-importing a day replaces it rather than doubling "
         "it**. The punch is inserted with `sync_status = 0`.",
         "**Stage two.** The unprocessed punches are walked in order. Each is marked `IN` or `OUT` by "
         "looking at what the previous one was, and `sync_status` is set to `1`.",
         "The first `IN` of a date creates the `tbl_attendance` row with its `check_in`; a later `OUT` "
         "fills in the `check_out`.",
         "**Stage three, the arithmetic.** For every day that has both a check-in and a check-out but no "
         "`work_time` yet, the punches between them are summed into working minutes and break minutes.",
         "Finally the shift row is read and four figures are worked out in minutes: `late_in` and `early_in` "
         "against the shift's `check_in`, `late_out` and `early_out` against its `check_out`.",
         "Open **Attendance Report** to see the result, and correct a day by hand if the machine missed a "
         "punch."],
        [["DELETE", "`tbl_attendance_import_new`", "Any punch already held for this `bio_id` at this exact "
          "timestamp"],
         ["DELETE", "`tbl_attendance`", "The day record for this `bio_id` and date, so it can be rebuilt"],
         ["INSERT", "`tbl_attendance_import_new`", "One row per punch in the file"],
         ["UPDATE", "`tbl_attendance_import_new`", "`sync_status = 1`, `check_in_out` (IN / OUT) and "
          "`shift_id` as each punch is processed"],
         ["INSERT", "`tbl_attendance`", "One row per employee per work date, with `check_in`"],
         ["UPDATE", "`tbl_attendance`", "`check_out` when the closing punch is found; then `work_time`, "
          "`break_time`, `late_in`, `late_out`, `early_in`, `early_out`"]],
        ["**Attendance Report (PF)** and **(Without PF)** - days present, and therefore pay",
         "**OT Report** - `work_time` beyond the normal day",
         "**Attendance Report** - the day-by-day view, where a missed punch is corrected"],
        fields=[
            ["`bio_id`", "**The only link between a punch and a person.** Read from column 4 of the sheet "
             "and matched against `mst_employee.bio_id`"],
            ["`biometric_time`", "The punch timestamp, exactly as the machine recorded it"],
            ["`check_in_out`", "`IN` or `OUT`, worked out from what the previous punch was - **not** from "
             "the machine"],
            ["`sync_status`", "`0` raw, `1` processed into `tbl_attendance`"],
            ["`work_time`, `break_time`", "**In minutes.** Work time is the sum of the in-to-out stretches; "
             "break time is the gaps between them"],
            ["`late_in`, `early_out`", "Minutes after the shift start, minutes before the shift end"],
            ["`auto_check_out`", "`1` marks a check-out the system supplied because none was punched"],
            ["`manual_update_by`", "Set when a human corrects the day on the attendance screen"],
        ],
        notes=[
            warn("**Everyone is imported as DAY shift.** The code sets `shift_id = 1` for every punch it "
                 "processes - the shift is never chosen. A night worker punching in at 19:00 is measured "
                 "against DAY's 09:00 start and comes out roughly ten hours late every day. The NIGHT row "
                 "in `mst_shifts` exists and is editable but is never used. If night-shift lateness figures "
                 "look absurd, this is why, and no amount of editing Shift Wise will fix it."),
            warn("**An employee with no `bio_id` is invisible to payroll.** The lookup is by `bio_id` alone, "
                 "and the reports select `WHERE bio_id > 0`. Someone with the field blank has no "
                 "attendance, is never listed, and is silently not paid. Two employees sharing a `bio_id` is "
                 "worse - they share punches, and nothing warns you. Check `bio_id` on every new starter."),
            warn("**IN and OUT are inferred, not read.** A punch is marked the opposite of the one before "
                 "it. So a single missed or duplicated punch flips every punch after it for that person - "
                 "an out becomes an in and the day's hours are nonsense. Correct the day on the attendance "
                 "screen; re-importing the same file will not fix it, because the inference will make the "
                 "same mistake again."),
            tip("**Re-importing is safe and is the right first move.** Both the raw punch and the day record "
                "are deleted before being written again, so importing the same file twice leaves the same "
                "result. If a day looks wrong, re-import before editing anything by hand."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 5.3
    b += form2(
        "5.3", "Employee Salary Report - PF, and Without PF",
        [("Menu", "Attendance and Salary > Employee Salary Report - PF  ·  ...- WPF"),
         ("Files", "`attendance_report_pf.php` - `attendance_report_withoutpf.php` - "
                   "`issue_salary.php` (the Issue Salary action) - `checkattendance.php` - "
                   "`emp_advance_return_payment.php` - `emp_debit_return_payment.php`"),
         ("Type", "**The payroll run.** Report on screen, payment on a button")],
        ["Pick a month and a year and you get the payroll: every employee, days worked, earned pay broken "
         "into its components, EPF, what is recovered against advances and debit notes, and the net.",
         "Two screens, one calculation. **PF** lists employees with `emp_pf = 2`; **Without PF** lists the "
         "rest. The PF version deducts EPF; the other does not.",
         "Each row carries three actions: **Advance Deduction (+)** and **Debit Deduction (+)**, which "
         "recover an instalment out of this month's net, and **Issue Salary**, which is the payment itself."],
        [["`mst_employee`", "Read - everyone with `bio_id > 0` and `rec_del_status = 1`, split by `emp_pf`"],
         ["`tbl_attendance`", "Read - the days present for the month"],
         ["`tbl_month_master`", "Read - `working_days`, `total_days`, `function_holidays`"],
         ["`tbl_emp_salary`", "Read - the current revision (`em_current = 1`): CTC and package"],
         ["`mst_salary_setting`", "Read - the percentages that split earned pay"],
         ["`tbl_emp_advance`, `tbl_emp_advance_return_payment`", "Read - outstanding, and recovered this "
          "month. **Written** by the (+) action"],
         ["`tbl_emp_debit_note`, `tbl_emp_debit_note_return_payment`", "The same for debit notes"],
         ["`tbl_emp_monthly_salary`", "**Written on Issue Salary** - the frozen payslip"],
         ["`tbl_accounts`", "**Written on Issue Salary** - the journal entry"]],
        ["Open the report and choose the **month** and **year**. The month list comes from Month Master, so "
         "a month with no row cannot be selected.",
         "The screen builds every row: days present from `tbl_attendance`, working days from Month Master, "
         "CTC and package from the employee's current salary revision, then the arithmetic in 5.1.",
         "**Paid days are capped at working days** - extra days present do not raise the pay.",
         "Recover an advance: click the **(+)** in the advance column. `emp_advance_return_payment.php` "
         "opens knowing the employee, the month, the year and the net pay. Enter the instalment.",
         "Recover a debit note the same way through its own **(+)**.",
         "Check the arithmetic on screen. The net column is earned pay less EPF, less both recoveries.",
         "Press **Issue Salary**. Every figure on that row is posted to `issue_salary.php`, written into "
         "`tbl_emp_monthly_salary`, and posted to the journal.",
         "The Issue button disappears for that employee and month - the screen looks for an existing issued "
         "row before drawing it."],
        [["INSERT", "`tbl_emp_advance_return_payment`", "From the advance (+): the instalment, tagged with "
          "`atten_month` and `atten_year`"],
         ["UPDATE", "`tbl_emp_advance`", "`return_amount` up, `balance_amount` down, `advance_status = 3`"],
         ["INSERT", "`tbl_emp_debit_note_return_payment`", "The same for a debit note"],
         ["INSERT", "`tbl_emp_monthly_salary`", "**The payslip.** `salary_for` (year-month), working days, "
          "paid days, CTC, earned, basic, DA, basic+DA, HRA, conveyance, EPF, both recoveries, net, "
          "`salary_type` (`WPF` / `WOPF`), `salary_status = 1`, `issued_by`, `issued_dtm`"],
         ["INSERT", "`tbl_accounts`", "`voucher_type = 'Employee Salary'`, `acc_tran_value = net`, "
          "`dr_ledger_id = 1`, `cr_ledger_id` = the employee's ledger"]],
        ["**EPF Report** - reads the issued `WPF` rows; nothing appears there until salary is issued",
         "`tbl_accounts` - a credit against the employee's ledger. **Written and never read** (see "
         "section 9.1), so the payslip row is the record that matters",
         "**Employee Advance** and **Employee Debit Note** - balances fall as instalments are recovered"],
        fields=[
            ["`emp_pf`", "`2` puts the employee on the PF report; anything else on the Without PF report"],
            ["`paid_days`", "Days present, **capped at `working_days`**"],
            ["`salary_for`", "`YYYY-M`. This plus `salary_type` plus `emp_id` is what marks a month paid"],
            ["`salary_type`", "`WPF` with PF, `WOPF` without, `OT` for the overtime run"],
            ["`salary_status`", "`1` issued"],
            ["`basic_da`", "Basic + DA, stored because EPF is a percentage of it"],
        ],
        notes=[
            warn("**`issue_salary.php` trusts everything the browser sends it.** Every figure - earned pay, "
                 "EPF, net - is calculated in PHP while the page renders, written into hidden fields, and "
                 "posted straight into `tbl_emp_monthly_salary` and the journal. Nothing is recalculated on "
                 "the server. The endpoint also has **no duplicate check**: the Issue button is hidden once "
                 "a month is issued, but a repeated request would write a second payslip and a second "
                 "journal entry. Treat the button as the only guard, and check "
                 "`tbl_emp_monthly_salary` for duplicates if anything is re-submitted."),
            warn("**The salary expense ledger is hardcoded as ledger id `1`.** Every salary posts "
                 "`dr_ledger_id = 1`. If ledger `1` in your database is not the salary account, every "
                 "payroll entry names the wrong account. Nothing reads the journal, so nothing will ever "
                 "tell you - but it matters the day someone builds a ledger report on top of it. Check "
                 "what ledger `1` actually is."),
            warn("**A part-recovery of an advance marks it settled.** Same behaviour as section 3.5: the "
                 "(+) action sets `advance_status = 3` whatever the amount. The next month's report reads "
                 "the outstanding balance with `advance_status != 3`, so **an advance recovered in "
                 "instalments drops out of the report after the first instalment** and the rest is never "
                 "chased. If advances stop appearing, check `balance_amount` directly."),
            note("**Extra days worked are worth nothing here.** Paid days are capped at the month's "
                 "`working_days`, so someone who worked every day including offs is paid the same as "
                 "someone who worked the standard month. Extra time is meant to be claimed through the OT "
                 "report instead."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 5.4
    b += form2(
        "5.4", "Employee Salary Report - OT",
        [("Menu", "Attendance and Salary > Employee Salary Report - OT"),
         ("Files", "`attendance_report_ot.php` - `issue_salary.php`"),
         ("Type", "The overtime run - a second, separate payment")],
        ["Overtime is paid separately from salary, on its own run, against its own rate. This screen works "
         "out how many overtime minutes each person accumulated in the month and what they are owed for "
         "them.",
         "Not everybody is eligible. The list is **Labour employees (`emp_type = 2`), plus anyone whose "
         "current salary revision has an OT rate above zero** - and of those, only people with attendance "
         "in the month."],
        [["`mst_employee`", "Read - the eligibility filter above"],
         ["`tbl_attendance`", "Read - every day in the month, with `work_time` and the `is_ot` flag"],
         ["`tbl_emp_salary`", "Read - `emp_ot_sal`, the rate per hour, from the current revision"],
         ["`tbl_emp_monthly_salary`", "**Written on Issue** - with `salary_type = 'OT'`"],
         ["`tbl_accounts`", "**Written on Issue**"]],
        ["Choose the **month** and **year**.",
         "For each eligible employee the screen walks their attendance days for the month and accumulates "
         "overtime minutes from `work_time`, taking the `is_ot` flag into account.",
         "The total is shown as hours and minutes - the running total is kept in **minutes** and divided "
         "out at the end.",
         "The rate comes from `emp_ot_sal` on the employee's current salary revision. Tiffin allowance days "
         "and amount can be added.",
         "Press **Issue Salary** on the row. The same endpoint writes a `tbl_emp_monthly_salary` row, but "
         "with `salary_type = 'OT'` and only the OT columns filled, plus its own journal entry."],
        [["INSERT", "`tbl_emp_monthly_salary`", "`salary_for`, `total_ot_hours`, `amount_per_hour`, "
          "`ot_amount`, `tiffen_allow_days`, `tiffen_amount`, `total_ot_amount`, `salary_type = 'OT'`, "
          "`salary_status = 1`, `issued_by`, `issued_dtm`"],
         ["INSERT", "`tbl_accounts`", "A second journal entry for the same employee and month, separate "
          "from their salary"]],
        ["`tbl_accounts` - a second, separate credit against the employee's ledger for the month",
         "`tbl_emp_monthly_salary` - two rows for the month, one `WPF` or `WOPF` and one `OT`"],
        fields=[
            ["`emp_ot_sal`", "The rate **per hour**, held on `tbl_emp_salary` per revision"],
            ["`is_ot`", "A per-day flag on `tbl_attendance` marking the day as overtime"],
            ["`total_ot_hours`", "Despite the name, accumulated in **minutes** and converted for display"],
            ["`tiffen_allow_days`, `tiffen_amount`", "A meal allowance added on top of the OT amount"],
        ],
        notes=[
            note("**An employee can be paid twice for the same month** - once on the salary run and once "
                 "here - and that is intended. Two rows in `tbl_emp_monthly_salary` with the same "
                 "`salary_for` but different `salary_type`, and two journal entries. When reconciling a "
                 "month's payroll, sum both."),
            warn("**Eligibility is easy to get wrong.** A Staff employee is only listed if their current "
                 "salary revision has `emp_ot_sal > 0`. Set the rate to zero, or forget to set it on a new "
                 "revision, and they silently vanish from this report even though their attendance shows "
                 "the extra hours. Check the OT rate after every salary revision."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 5.5
    b += form2(
        "5.5", "Employee Salary Report - EPF",
        [("Menu", "Attendance and Salary > Employee Salary Report - EPF"),
         ("Files", "`attendance_report_epf.php` - `attendance_report_epf_txt.php` (the upload file)"),
         ("Type", "Statutory report - **read only**")],
        ["The provident fund return for a month, in the shape the EPFO wants: each member with their EPF "
         "number and UAN, their wages, and the contribution split into **EPS** and **EDLI**.",
         "It writes nothing. It reads the payslips already issued and reshapes them.",
         "A second button produces the same data as a **text file** ready to upload to the EPFO portal."],
        [["`tbl_emp_monthly_salary`", "Read - issued rows with `salary_type = 'WPF'` for the chosen month"],
         ["`mst_employee`", "Read - `emp_name`, `emp_epf_no`, `emp_uan_no`"]],
        ["Choose the **month** and **year**.",
         "The screen selects the issued PF payslips for that month - `salary_for = '<year>-<month>'`, "
         "`salary_type = 'WPF'`, `salary_status = 1`.",
         "For each one it takes `basic_da` and `epf_amount` from the payslip and splits the contribution: "
         "**EPS is 8.33 per cent of basic + DA**, and **EDLI is the rest of the EPF amount**.",
         "The employee's EPF number and UAN are pulled from their record and shown alongside.",
         "Print the report, or use the text-file button to generate `attendance_report_epf_txt.php` for "
         "upload."],
        [["(none)", "-", "**This screen writes nothing.** It is a reading of `tbl_emp_monthly_salary`"]],
        ["Nothing inside BIE - the output goes to the EPFO portal"],
        fields=[
            ["`basic_da`", "Basic + DA from the payslip. The base for both the EPF deduction and the split"],
            ["`epf_amount`", "The total contribution, `basic_da × sal_pf%`"],
            ["EPS", "`basic_da × 8.33%` - the pension share. The 8.33 is hardcoded"],
            ["EDLI", "`epf_amount - EPS` - the remainder"],
            ["`emp_epf_no`, `emp_uan_no`", "From the employee record. Blank here means blank on the return"],
        ],
        notes=[
            warn("**Nothing appears here until salary is issued.** The report reads issued payslips, not "
                 "attendance. An employee whose salary for the month has not been issued is simply absent "
                 "from the PF return, with no warning. Issue every PF employee's salary before generating "
                 "the return."),
            warn("**The 8.33 per cent EPS rate is hardcoded in the page.** So is the assumption that "
                 "everything above it is EDLI, and there is no wage ceiling applied. If the statutory rates "
                 "or the ceiling change, this is a code change, not a settings change. Check the figures "
                 "against the current rules before filing."),
            note("**A missing EPF number or UAN is not flagged.** The employee still appears, with empty "
                 "columns, and the text file is generated with the gaps in it. Check "
                 "`emp_epf_no` and `emp_uan_no` are filled for every PF employee before the first return."),
        ])

    return {"heading": "Part 5", "blocks": b}
