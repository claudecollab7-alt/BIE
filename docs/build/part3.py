# -*- coding: utf-8 -*-
from content_common import *

def chapter():
    b = []
    b += [h1("Part 3  HR Management")]
    b += [lead("Nine screens covering the people side: employees, their salary packages, money advanced to "
               "them, money recovered from them, and the shifts they work.")]
    b += [table([26, 74], ["Screen", "In one line"], [
        ["Employee", "**The big one.** One form, 2,500 lines, and it writes five tables"],
        ["Labour / Staff", "Two filtered views of the same employee list"],
        ["Labour Master", "The contractor or labour-group names an employee can be attached to"],
        ["Salary Package", "Named pay structures - basic, DA, HRA, conveyance, PF, CCA"],
        ["Employee Salary", "**Read only.** Who is on what CTC"],
        ["Employee Advance", "Money paid to an employee ahead of salary - and what is still owed"],
        ["Employee Debit Note", "Amounts charged against an employee - damage, shortfall, penalties"],
        ["Shift Wise", "The two shifts and the exact clock times that define them"],
    ], bold_first=True)]
    b += [tip("Remember Rule 3: an employee gets an **accounts ledger** created with them. Every advance, "
              "debit note and salary payment posts against it, into the one central journal "
              "`tbl_accounts`.")]

    # ------------------------------------------------------------------ 3.1
    b += form2(
        "3.1", "Employee",
        [("Menu", "HR Management > Employee"),
         ("Files", "`mst_employee_add.php` (2,500 lines - list, add and edit) - "
                   "`inc/cis_ajax/jquery_select_emp_code.php` (the code) - "
                   "`inc/cis_ajax/jquery_modal_employee_det.php` (view)"),
         ("Type", "Master - **the largest form in BIE**")],
        ["Everything about one person: name, father's or husband's name, date of birth, blood group, photo, "
         "both addresses, bank account, PAN, Aadhaar, marital and family details, joining date, EPF and UAN "
         "numbers, nominee, and the documents scanned in as proof.",
         "It also does three things beyond storing a person. It **creates their accounts ledger**. It can "
         "**create their login** - the same as adding a user in Settings. And it records **the assets issued "
         "to them** - laptop, SIM card, tools - plus their certificates.",
         "The form reshapes itself around **employee type**: Staff, Labour or Others. The type decides which "
         "sections appear, what the employee code looks like, and which list you land on after saving."],
        [["`mst_employee`", "The employee row - the great majority of the fields"],
         ["`mst_ledger`", "**Inserted first.** The employee's accounts ledger; its id is stored as "
          "`ledger_id`"],
         ["`mst_accounts_group`", "Read, for the ledger's `group_type`"],
         ["`tbl_user`", "**A login row, if you tick login access.** Same table as Settings > User"],
         ["`tbl_asset_details`", "One row per asset issued to this employee"],
         ["`mst_employee_certificate`", "One row per certificate - **Staff only**"],
         ["`mst_department`, `mst_designation`", "Read, for the two linked dropdowns"],
         ["`mst_labour`", "Read - the labour group, when the type is Labour"],
         ["`mst_branch`", "Read - the branch, which goes into the employee code"],
         ["`mst_state`, `mst_district`, `mst_city`", "Read, twice - current address and permanent address"]],
        ["Open **HR Management > Employee**, or reach the form from the Staff or Labour list.",
         "Choose the **employee type** first - Staff, Labour or Others. Sections appear and disappear as you "
         "do.",
         "Choose the **branch** and the employment status (Temporary or Permanent). As soon as these are "
         "set, the screen calls `jquery_select_emp_code.php` and fills in the **employee code**, which is "
         "read-only.",
         "Fill in personal details, the current address, and the permanent address - or tick **copy "
         "address** to repeat the first.",
         "Fill in bank details, PAN, Aadhaar, and upload the proof documents. Each file is renamed using the "
         "employee code and stored under `project_img/`.",
         "Fill in joining date, EPF and UAN numbers, nominee, credit days and pay mode.",
         "Choose the **accounts group** for the ledger and its opening balance.",
         "Add any **assets** issued - asset, issue date, quantity, value, and for a SIM the number and its "
         "limit. Add **certificates** if the person is Staff.",
         "If they need to log in, tick **login access** and give a login name and password.",
         "Save. The screen inserts the ledger, then the employee, then the assets, then the certificates, "
         "then the login - in that order.",
         "You land on the Staff list, the Labour list, or `lst_employee.php`, depending on the type you "
         "chose."],
        [["INSERT", "`mst_ledger`", "**First.** The new `ledger_id` is carried into the employee row"],
         ["INSERT", "`mst_employee`", "One row, ~65 columns, including `branch_id`, `emp_code`, `emp_slno`, "
          "`emp_type`, `ledger_id`, `emp_advance_bal`, `created_by`, `created_dtm`"],
         ["INSERT", "`tbl_asset_details`", "One row per asset line. On edit, **every existing row for this "
          "employee is deleted first**, then re-inserted"],
         ["INSERT", "`mst_employee_certificate`", "One row per certificate - only when `emp_type = 1` "
          "(Staff). Also delete-then-reinsert on edit"],
         ["INSERT", "`tbl_user`", "A login, when login access is ticked - `usr_logname`, hashed "
          "`usr_logpwd`, `branch_id` and `emp_id` pointing back at the employee"],
         ["UPDATE", "`mst_employee`, `mst_ledger`, `tbl_user`", "On edit, all three are updated together"],
         ["Soft delete", "`mst_employee`", "Sets `rec_del_status = 0`, through "
          "`jquery_delete_records_hr.php`. **The ledger and the login are left active**"]],
        ["**Labour list** and **Staff list** - the same rows, split by type",
         "**Employee Salary** - CTC shown per employee",
         "**Employee Advance** and **Employee Debit Note** - one row each per employee",
         "**Import Attendance** - punches are matched to an employee by `bio_id`",
         "**All four attendance and salary reports** - the pay calculation reads this row",
         "**Accounts** - Day Book, Ledger and Trial Balance, through `ledger_id`",
         "**Settings > User** - the login created here appears in that list too"],
        fields=[
            ["`emp_type`", "**`1` Staff, `2` Labour, `3` Others.** Drives the whole form, the code, and "
             "where you land after saving"],
            ["`emp_code`", "Built by AJAX as `BIE / branch code / type + status / serial` - for example "
             "`BIE/HO/SP/42`. `S` Staff, `L` Labour, `O` Others; `T` Temporary, `P` Permanent"],
            ["`emp_slno`", "The running number inside the code - `max(emp_slno) + 1` over active employees"],
            ["`bio_id`", "**The biometric device id.** This, not `emp_id`, is how imported attendance finds "
             "the person. Wrong or missing, and their punches are orphaned"],
            ["`staff_status` / `labour_status`", "`1` Temporary, `2` Permanent. Only one applies, depending "
             "on type"],
            ["`emp_pf`", "Whether PF applies - decides which of the two attendance reports the person "
             "belongs in"],
            ["`emp_ctc`", "Cost to company. Shown on the Employee Salary screen; **not on this form**"],
            ["`ledger_id`", "The employee's accounts ledger"],
            ["`emp_advance_bal`", "An opening advance balance carried in on creation"],
            ["`login_access`, `emp_login_name`, `emp_login_password`", "Whether to create a `tbl_user` row, "
             "and with what credentials"],
            ["`rec_del_status`", "`1` active, `0` deleted"],
        ],
        notes=[
            warn("**Deleting an employee does not disable their login.** The dustbin sets "
                 "`mst_employee.rec_del_status = 0` and stops there - the `tbl_user` row keeps `usr_status = "
                 "1` and the person can still log in. When someone leaves, delete them here **and** delete "
                 "their user in Settings > User."),
            warn("**Assets and certificates are rebuilt on every edit.** Saving deletes all "
                 "`tbl_asset_details` and `mst_employee_certificate` rows for the employee and re-inserts "
                 "what is on the form. Open the form, do not scroll to the asset section, save - and the "
                 "assets are gone if the form did not post them back. Check the asset list after any edit."),
            warn("**Certificates are Staff-only.** The certificate insert is guarded by `emp_type == 1`. "
                 "Attach a certificate to a Labour or Others employee and it is silently dropped."),
            note("**`bio_id` is the link to attendance, and nothing enforces it.** It is a free-text field "
                 "with no uniqueness check. Two employees with the same `bio_id` will share punches; an "
                 "employee with none will never appear in an attendance report, even though they exist and "
                 "are paid."),
            note("Employee type **Others** lands on `lst_employee.php` after saving - a screen that is not "
                 "on any menu. The row is saved correctly, but you can only reach it again by editing from "
                 "another route, or by typing the file name. In practice, use Staff or Labour."),
            note("The same plain-text password problem as Settings > User applies here: when the login is "
                 "created, `pw_hint` keeps the password as typed. See section 1.1."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 3.2
    b += [h2("3.2  Labour and Staff")]
    b += [table([22, 78], None, [
        ["What they do", "Two lists of employees - Labour shows `emp_type = 2`, Staff shows "
         "`emp_type = 1`. Nothing else differs"],
        ["Files", "`lst_labour.php` + `inc/datatable/ajaxLabourList.php` - `lst_staff.php` + "
         "`inc/datatable/ajaxStaffList.php`"],
        ["Tables", "`mst_employee`, joined to `mst_department` and `mst_designation` for the names"],
        ["Writes", "Nothing of its own. Add and edit both open the Employee form; the dustbin calls "
         "`inc/cis_ajax/jquery_delete_records_hr.php` to set `rec_del_status = 0`"],
        ["View", "The eye icon opens `jquery_modal_employee_det.php` in a modal - the full employee record, "
         "read only, without leaving the list"],
    ], bold_first=True)]
    b += [p("Both lists filter on `rec_del_status = 1`, and both can be narrowed by **branch** and by "
            "**department**. Search matches employee name, code, mobile, department and designation.")]
    b += [note("**Branch visibility works the same way as the supplier list.** User id `1`, or anyone whose "
               "branch is Head Office, sees every branch's employees. Everyone else sees only their own "
               "branch. So a Kerala HR user cannot see Rajapalayam staff - that is by design, not a fault.")]
    b += [warn("There is no third list for employee type **Others**. Those rows exist in `mst_employee` but "
               "appear on neither of these two screens, so they are effectively invisible. If the headcount "
               "on these lists is lower than you expect, count the `emp_type = 3` rows.")]
    b += [sp()]

    b += mini("3.2.1", "Labour Master", "`mst_labour.php`",
              "The names of labour groups or contractors an employee can be attached to. A plain list with "
              "one field.",
              "`mst_labour`",
              "`labour_id`, `labour_name`, `company_id`, audit pair, `rec_del_status`",
              "**Employee** - the Labour dropdown, shown when the employee type is Labour. Attendance and "
              "salary reports can be grouped by it")
    b += [note("`mst_labour` still carries a `company_id` column, left over from the Benzear codebase this "
               "one was forked from. BIE is organised by **branch**, not by company, and nothing in BIE "
               "reads it. Ignore it.")]
    b += [pb()]

    # ------------------------------------------------------------------ 3.3
    b += form2(
        "3.3", "Salary Package",
        [("Menu", "HR Management > Salary Package"),
         ("Files", "`lst_salary_package.php` (list) - `mst_salary_package_add.php` (add / edit) - "
                   "`inc/datatable/ajaxSalaryPackageList.php`"),
         ("Type", "Master - named pay structures")],
        ["A salary package is a named breakdown of pay: **basic, DA, HRA, conveyance, PF and CCA**. Instead "
         "of typing six figures for every employee, you define the package once and attach people to it.",
         "A package is either **monthly** or **daily**, which changes what the figures mean - a monthly "
         "amount, or a per-day rate."],
        [["`mst_salary_setting`", "One row per package, with the six components"]],
        ["Open **HR Management > Salary Package**. The list shows package name, type and the components.",
         "**Add New** opens `mst_salary_package_add.php`.",
         "Name the package, choose **Monthly** or **Daily**, set the period, and enter the six amounts.",
         "Save. One row, straight in.",
         "The dustbin sets `rec_del_status = 0`."],
        [["INSERT", "`mst_salary_setting`", "`sal_package_name`, `sal_type`, `sal_period`, `sal_basic`, "
          "`sal_da`, `sal_hra`, `sal_convey`, `sal_pf`, `sal_cca`, plus `created_by` and `created_dtm`"],
         ["UPDATE", "`mst_salary_setting`", "The same fields plus `modify_by`, `modify_dtm`"],
         ["Soft delete", "`mst_salary_setting`", "Sets `rec_del_status = 0`"]],
        ["The salary package dropdown on the employee screens",
         "**Attendance Report (PF)** and **Attendance Report (Without PF)** - the components come from here "
         "and are apportioned by days worked",
         "**EPF Report** - through `sal_pf`"],
        fields=[
            ["`sal_package_name`", "Free text, 25 characters. How you recognise it - keep it descriptive"],
            ["`sal_type`", "**`M` monthly, `D` daily.** Decides whether the figures are a month's pay or a "
             "day's"],
            ["`sal_period`", "The period the package covers"],
            ["`sal_basic`, `sal_da`, `sal_hra`, `sal_convey`, `sal_pf`, `sal_cca`", "The six components. "
             "Each is `decimal(6,2)`"],
        ],
        notes=[
            warn("**Every component is `decimal(6,2)` - the largest value that fits is 9999.99.** A basic "
                 "salary above ten thousand cannot be stored. MySQL will either refuse it or silently clamp "
                 "it, depending on the server's strict-mode setting, and nothing on the screen warns you. "
                 "Widening these columns is the fix; until then, check what was actually saved after "
                 "entering a large figure."),
            note("**Editing a package changes pay for everybody on it, including for past months.** The "
                 "attendance and salary reports read the current package row each time they are opened - "
                 "nothing is copied onto the employee or frozen at month end. To change pay for one person, "
                 "move them to a different package; to change it from a date, make a new package."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 3.4
    b += [h2("3.4  Employee Salary")]
    b += [table([22, 78], None, [
        ["What it does", "Shows every employee with their **CTC**, searchable and filterable. That is all - "
         "**nothing on this screen writes anything**"],
        ["Files", "`employee_salary.php` + `inc/datatable/ajaxEmployeeSalarySet.php` + "
         "`modal_employee_det.php`"],
        ["Tables", "`mst_employee` (for `emp_ctc`), left-joined to `mst_department` and `mst_designation`"],
        ["Writes", "**None.** Read only"],
        ["Filters", "Employee name, code, mobile, department or designation; plus branch and employee type"],
    ], bold_first=True)]
    b += [p("Despite the name, this is not where salary is set or run. It is a quick answer to *what is "
            "everyone on?* - one line per employee showing code, name, department, designation and CTC. The "
            "eye icon opens the full employee record in a modal.")]
    b += [note("**Where salary actually happens.** The figures come from the **Salary Package** attached to "
               "the employee, apportioned by the days in **Month Master** and the punches from **Import "
               "Attendance**. The four **Attendance and Salary** reports do the calculation. Those are all "
               "covered in Part 5.")]
    b += [note("`emp_ctc` is a column on `mst_employee`, but the Employee form does not offer it and this "
               "screen does not edit it. In the shipped code it is set directly in the database. If CTC "
               "shows as zero for everyone, that is why.")]
    b += [pb()]

    # ------------------------------------------------------------------ 3.5
    b += form2(
        "3.5", "Employee Advance",
        [("Menu", "HR Management > Employee Advance"),
         ("Files", "`employee_advance.php` (list) - `emp_advance_update.php` (record an advance) - "
                   "`emp_advance_return_payment.php` (recover it) - `inc/datatable/ajaxEmployeeAdvance.php`"),
         ("Type", "Transaction, posted to accounts")],
        ["Money paid to an employee ahead of their salary. The list shows, for every employee, **advanced**, "
         "**returned** and **balance still owed**.",
         "Recording an advance posts a real accounts entry, so the money shows up in the Day Book and on the "
         "employee's ledger immediately.",
         "Recovery is **not done from this screen**. It happens from the attendance reports, where you "
         "deduct an instalment from that month's net pay."],
        [["`tbl_emp_advance`", "One row per advance - amount, date, payment mode, cheque details, balance"],
         ["`tbl_emp_advance_return_payment`", "One row per recovery instalment, tagged with the attendance "
          "month and year"],
         ["`tbl_accounts`", "**The central journal.** Both the advance and each recovery post a "
          "double-entry row here"],
         ["`mst_employee`", "Read for the list, and for the employee's `ledger_id`"],
         ["`mst_finyear`", "Read - the active financial year, which numbers the advance"]],
        ["Open **HR Management > Employee Advance**. Every employee is listed with their advanced, returned "
         "and balance totals.",
         "Click **Modify Advance** to open `emp_advance_update.php` for that person.",
         "Enter the **date**, the **amount**, the **payment mode** - cash, cheque, card or transfer - with "
         "the cheque number or reference if relevant, and a remark.",
         "Save. A row goes into `tbl_emp_advance` with `is_current = 1` and `advance_status = 1`, numbered "
         "with the next serial for the financial year. If the mode is cheque, `chq_passed` starts as `NO`.",
         "Straight after, one row goes into `tbl_accounts`: **debit the employee's ledger, credit the cash "
         "or bank ledger you chose**, tagged `voucher_type = 'Employee Advance'`.",
         "**To recover it**, go to the attendance report for the month, find the employee, and use the "
         "**Advance Deduction** (+) link. That opens `emp_advance_return_payment.php` already knowing the "
         "month, the year and the net pay.",
         "Enter how much to recover. That writes a `tbl_emp_advance_return_payment` row, adds to "
         "`return_amount` and subtracts from `balance_amount` on the advance, and posts the reverse accounts "
         "entry."],
        [["INSERT", "`tbl_emp_advance`", "`emp_id`, `advance_slno`, `advance_date`, `advance_amount`, "
          "`balance_amount` (= the full amount), `pay_finyr`, `ledger_id`, payment mode and cheque fields, "
          "`is_current = 1`, `advance_status = 1`"],
         ["INSERT", "`tbl_accounts`", "`voucher_type = 'Employee Advance'`, `record_type = 'M'`, "
          "`advance_id` = the new row, `dr_ledger_id` = the employee's ledger, `cr_ledger_id` = the cash or "
          "bank ledger"],
         ["INSERT", "`tbl_emp_advance_return_payment`", "On recovery - the instalment, with `atten_month` "
          "and `atten_year` so the attendance report can find it"],
         ["UPDATE", "`tbl_emp_advance`", "On recovery - `return_amount = return_amount + paid`, "
          "`balance_amount = balance_amount - paid`, and **`advance_status = 3`**"],
         ["INSERT", "`tbl_accounts`", "On recovery - the reverse entry"]],
        ["**Day Book**, **Ledger Book**, **Trial Balance** - through `tbl_accounts`",
         "The employee's own ledger - the advance sits as a debit until recovered",
         "**Attendance Report (PF)** and **(Without PF)** - the advance deduction column, and the (+) link "
         "that records a recovery",
         "This list - the three totals are recalculated from `tbl_emp_advance` every time it loads"],
        fields=[
            ["`advance_slno`", "A running number within the financial year - `max + 1` over `pay_finyr`"],
            ["`advance_amount`", "What was paid out. Never changes"],
            ["`return_amount`", "Running total recovered. Only the recovery screen touches it"],
            ["`balance_amount`", "Still owed. Starts equal to `advance_amount`"],
            ["`advance_status`", "`1` open, **`3` treated as settled**. See the warning below"],
            ["`is_current`", "`1` for the live row. The list's totals filter on it"],
            ["`pay_type`", "`Q` cheque - which is what makes `chq_passed` start at `NO`. Otherwise cash, "
             "card or transfer"],
        ],
        notes=[
            warn("**A part-recovery marks the advance settled.** The recovery screen sets "
                 "`advance_status = 3` whatever the amount, so recovering 1,000 of a 5,000 advance flags it "
                 "as done. `balance_amount` is still 4,000 and the list still shows it, because the list "
                 "does not filter on status - but the balance calculation inside `emp_advance_update.php` "
                 "does, and ignores status `3` rows. So the list and that screen can disagree about what is "
                 "outstanding. **Trust `balance_amount` on the list.**"),
            warn("**There is no way to correct or cancel an advance from the screen.** The only action is "
                 "Save, which adds another row and another accounts entry. A mistyped amount needs a "
                 "database correction and a matching journal fix - it cannot be undone here."),
            note("**`chq_passed` is set to `NO` for a cheque and never updated.** Nothing on any screen marks "
                 "a cheque as cleared, so the field stays `NO` forever. Do not use it to tell what has "
                 "actually gone through the bank."),
            tip("Recovery is driven from the attendance report because that is where the net pay for the "
                "month is known. The (+) link passes the month, the year and the net amount along, so the "
                "recovery can never be more than the pay it comes out of."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 3.6
    b += form2(
        "3.6", "Employee Debit Note",
        [("Menu", "HR Management > Employee Debit Note"),
         ("Files", "`employee_debit_note.php` (list) - `emp_debit_note.php` (raise one) - "
                   "`inc/datatable/ajaxEmployeeDebit.php`"),
         ("Type", "Transaction, posted to accounts")],
        ["A debit note charges an amount **against** an employee: damaged stock, a shortfall, a penalty, the "
         "cost of something lost. The structure is the mirror image of Employee Advance - amount, recovered, "
         "balance - and it posts to accounts the same way.",
         "The difference is intent. An advance is money lent that will come back. A debit note is a charge "
         "that the employee owes."],
        [["`tbl_emp_debit_note`", "One row per debit note - amount, date, reason, recovered, balance"],
         ["`tbl_accounts`", "The double-entry row for the charge"],
         ["`mst_employee`", "Read for the list, and for the `ledger_id`"],
         ["`mst_finyear`", "Read, to number the note"]],
        ["Open **HR Management > Employee Debit Note**. Every employee with their debited, returned and "
         "balance figures.",
         "Click **Modify Debit** to open `emp_debit_note.php` for that person.",
         "Enter the date, the amount, the reason and the ledger the charge goes against.",
         "Save. A row goes into `tbl_emp_debit_note` with `is_current = 1`, numbered within the financial "
         "year, and a matching `tbl_accounts` row is posted.",
         "Recovery, as with advances, is handled from the attendance reports when that month's pay is "
         "calculated."],
        [["INSERT", "`tbl_emp_debit_note`", "`emp_id`, serial, date, amount, `balance_amount`, `pay_finyr`, "
          "`ledger_id`, reason, `is_current = 1`, `debit_status`"],
         ["INSERT", "`tbl_accounts`", "The double-entry row, tagged as an employee debit note"],
         ["UPDATE", "`tbl_emp_debit_note`", "On recovery - `return_amount` up, `balance_amount` down"]],
        ["**Day Book**, **Ledger Book**, **Trial Balance**",
         "The employee's ledger - the charge sits against them",
         "**Attendance Report (PF)** and **(Without PF)** - the deduction column",
         "This list - totals recalculated on every load"],
        fields=[
            ["`debit_amount`", "The amount charged"],
            ["`return_amount`, `balance_amount`", "Recovered so far, and still outstanding"],
            ["`debit_status`", "`3` again means settled - the same convention, and the same caveat"],
            ["`is_current`", "`1` for the live row"],
        ],
        notes=[
            warn("**The same caveats as Employee Advance apply here**, because the two screens are built "
                 "from the same code: a part-recovery can mark the note settled, there is no cancel or "
                 "correct, and the totals on the list and inside the entry screen can disagree. Read "
                 "section 3.5 and treat this screen the same way."),
            note("A debit note is a real accounting charge, not a note to self. It hits the employee's "
                 "ledger and the Trial Balance the moment you save it. If you only want to record that "
                 "something happened, write it in the remarks on the employee record instead."),
        ])
    b += [pb()]

    # ------------------------------------------------------------------ 3.7
    b += form2(
        "3.7", "Shift Wise",
        [("Menu", "HR Management > Shift Wise"),
         ("Files", "`shift_wise.php` (list) - `shift_wise_time_setting.php` (edit the times)"),
         ("Type", "Settings - **the rules attendance is judged against**")],
        ["Two shifts, DAY and NIGHT, and the exact clock times that define each one. This is a small screen "
         "with a large reach: **it is what turns a raw biometric punch into a present, a late or an "
         "overtime**.",
         "Each shift carries six times, not two. The official check-in and check-out, plus a window around "
         "each - the earliest and latest a punch will be accepted as that shift's check-in, and the same for "
         "check-out. A punch outside the window is not counted as that shift at all."],
        [["`mst_shifts`", "One row per shift, with the six times, the duration and the next-day flag"]],
        ["Open **HR Management > Shift Wise**. Two rows - DAY and NIGHT.",
         "Click the pencil to open `shift_wise_time_setting.php` for that shift.",
         "Adjust the times: official check-in and check-out, then the check-in window "
         "(`check_in_start` to `check_in_end`) and the check-out window (`check_out_start` to "
         "`check_out_end`).",
         "Set the **duration** in minutes - the length of a full shift - and whether check-out falls on the "
         "**next day**.",
         "Update. **You cannot add or delete a shift here** - only edit the two that exist."],
        [["UPDATE", "`mst_shifts`", "The six times, `duration`, `work_day` and `is_nxtday_checkout` for the "
          "one shift you edited. **That is the only write this screen makes**"]],
        ["**Import Attendance** - each punch is matched against these windows to decide which shift it "
         "belongs to and whether it counts",
         "**Attendance Report (PF)** and **(Without PF)** - present, absent and half-day come out of this",
         "**OT Report** - overtime is time worked beyond `duration`"],
        fields=[
            ["`shift_name`", "`DAY` or `NIGHT`"],
            ["`check_in`, `check_out`", "The official start and end. In the shipped data, DAY is 09:00 to "
             "17:30 and NIGHT is 19:00 to 07:00"],
            ["`check_in_start`, `check_in_end`", "The window a check-in punch must fall in. DAY accepts "
             "08:00 to 09:05 - so a punch at 09:06 is **not a late check-in, it is not a check-in at all**"],
            ["`check_out_start`, `check_out_end`", "The same for going home. DAY accepts 17:30 to 23:00"],
            ["`duration`", "Full shift length **in minutes** - 510 for DAY (8.5 hours), 540 for NIGHT (9). "
             "Overtime is measured against this"],
            ["`is_nxtday_checkout`", "**`1` means the shift ends the following day.** NIGHT is `1`: a "
             "check-in at 19:00 pairs with a check-out at 07:00 the next morning"],
            ["`work_day`", "How much of a working day one shift counts as"],
            ["`status`", "`1` active, `0` deleted"],
        ],
        notes=[
            warn("**The windows are narrow, and a punch outside them is thrown away.** The DAY check-in "
                 "window closes at 09:05. Someone punching at 09:15 has no check-in for that day, so the "
                 "attendance report shows them absent rather than late. If a whole group shows as absent on "
                 "a day you know they worked, widen `check_in_end` and import again."),
            warn("**Changing these times changes attendance that was already imported and reported.** The "
                 "reports re-derive present, absent and overtime from `mst_shifts` every time they run. "
                 "Adjust a window and last month's figures change with it. Make shift changes at a month "
                 "boundary, and print anything you need to keep first."),
            note("**`is_nxtday_checkout` is what makes the night shift work.** Without it a 19:00 check-in "
                 "and a 07:00 check-out look like twelve hours of absence rather than a completed shift. If "
                 "night-shift attendance is wrong, check this flag before anything else."),
            note("There is no add or delete on this screen - `shift_wise.php` only lists and edits. Adding a "
                 "third shift means inserting a `mst_shifts` row directly, and then checking that the "
                 "import and the reports cope with three."),
        ])

    return {"heading": "Part 3", "blocks": b}
