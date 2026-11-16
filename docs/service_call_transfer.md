# Service call: Transfer to BIE (handover note for the Benzear side)

BIE now has the same service call flow as Benzear (register, complaints, complete, send for approval / approve, prints,
report). A Benzear service call can be handed to BIE with a **Transfer to BIE** button; BIE completes it, and Benzear shows
BIE's status. Both databases are on the same MariaDB server, so Benzear writes and reads the BIE database directly
(the same way it already reads the attendance database).

**Rule: Benzear writes only the two INSERTs below into BIE, and after that only reads BIE.** BIE never writes into Benzear.

## 1. Benzear config

Add the BIE database name next to the others in `inc/common/config.php`:

```php
define('DB_NAME_BIE', 'bie_db');   // the BIE database on this server - the DB_NAME in BIE's own config.php (bie_db on live)
```

The same database user (root on the live server) can read and write both databases.

## 2. BIE tables used (already created by `db/post_upload_161126_service_call.sql` in BIE)

`bie_db.tbl_service_call_register` - same columns as Benzear's call table, plus:

| Column | Benzear fills | Meaning |
|---|---|---|
| `src_system` | `'BENZEAR'` | made in Benzear |
| `src_sc_id` | Benzear `sc_id` | link back |
| `src_sc_no` | Benzear `sc_no` | e.g. `KL-2627-019`, shown in BIE |
| `src_state_name`, `src_district_name`, `src_city_name`, `src_principal_name` | the **names** | the two databases number these masters differently - BIE finds its own ids by name |
| `src_transfer_by`, `src_transfer_dtm` | user name, now | who sent it |
| `bie_received` | `0` | BIE has not taken it in yet |
| `sc_status` | `1` | pending |

Leave `sc_no`, `state_id`, `district_id`, `city_id`, `principal_id`, `supp_id`, `branch_id`, `invoice_id` as they default
(blank / 0): BIE fills them when it takes the call in (BIE number `BIE-<state>-<finyr>-NNN`, BIE branch of that state,
the BIE customer found by GST / mobile / name or made new). Benzear invoices are not in BIE, so `invoice_id` stays 0.

`bie_db.tbl_sc_complaints` - one row per **open** Benzear complaint, with `src_complaint_id` = Benzear `complaint_id`.

## 3. Transfer to BIE (Benzear side)

Show the button on a Benzear call that is pending, has at least one open complaint and is not transferred yet.
On click, in **one transaction on the Benzear connection**:

```php
$bie = '`' . DB_NAME_BIE . '`';
$conn->beginTransaction();
$sc = $conn->query("SELECT sc_id, sc_status FROM tbl_service_call_register WHERE sc_id = " . (int)$sc_id . " FOR UPDATE")->fetch(PDO::FETCH_OBJ);
// refuse when not found, not pending (sc_status <> 1) or already transferred

$conn->prepare("INSERT INTO $bie.tbl_service_call_register
  (sc_date, customer_name, sc_ref_no, enq_add1, enq_add2, customer_address, enq_pincode, prefix, contact_person, designation,
   mobile_no, tel_no, fax_no, email, enq_website, enq_gst, enq_pan, service_charge_type, received_amount, nature_of_complaint,
   sc_status, src_system, src_sc_id, src_sc_no, src_state_name, src_district_name, src_city_name, src_principal_name,
   src_transfer_by, src_transfer_dtm, bie_received)
SELECT sc.sc_date, sc.customer_name, sc.sc_ref_no, sc.enq_add1, sc.enq_add2, sc.customer_address, sc.enq_pincode, sc.prefix,
   sc.contact_person, sc.designation, sc.mobile_no, sc.tel_no, sc.fax_no, sc.email, sc.enq_website, sc.enq_gst, sc.enq_pan,
   sc.service_charge_type, sc.received_amount, sc.nature_of_complaint,
   1, 'BENZEAR', sc.sc_id, sc.sc_no, IFNULL(TRIM(st.state_name), ''), IFNULL(TRIM(d.district_name), ''),
   IFNULL(TRIM(ci.city_name), ''), IFNULL(TRIM(p.principal_name), ''), :by, NOW(), 0
FROM tbl_service_call_register sc
LEFT JOIN mst_state st ON st.state_id = sc.state_id
LEFT JOIN mst_district d ON d.district_id = sc.district_id
LEFT JOIN mst_city ci ON ci.city_id = sc.city_id
LEFT JOIN mst_principal p ON p.principal_id = sc.principal_id
WHERE sc.sc_id = :id")->execute(array(':by' => $_SESSION['_username'], ':id' => $sc_id));
$bie_sc_id = (int)$conn->lastInsertId();

$conn->prepare("INSERT INTO $bie.tbl_sc_complaints (sc_id, complaint_desc, complaint_status, src_complaint_id)
SELECT :bie_sc_id, complaint_desc, 0, complaint_id FROM tbl_sc_complaints WHERE sc_id = :id AND complaint_status = 0")
  ->execute(array(':bie_sc_id' => $bie_sc_id, ':id' => $sc_id));

// Benzear's own call: mark it transferred and keep the BIE id (new Benzear columns, see 4)
$conn->prepare("UPDATE tbl_service_call_register SET sc_status = 4, bie_sc_id = :b, bie_transfer_by = :u, bie_transfer_dtm = NOW()
WHERE sc_id = :id")->execute(array(':b' => $bie_sc_id, ':u' => $_SESSION['_userid'], ':id' => $sc_id));
$conn->commit();
```


## 4. Benzear changes for a transferred call

- New columns on Benzear `tbl_service_call_register`: `bie_sc_id INT NOT NULL DEFAULT 0`, `bie_transfer_by INT NOT NULL DEFAULT 0`,
  `bie_transfer_dtm DATETIME NULL`.
- New status `sc_status = 4` "Transferred to BIE": no edit, no complete, no delete in Benzear (refuse on the server too).
  The approval steps stay in BIE for these calls.

## 5. Status back from BIE (Benzear reads, never writes)

```sql
SELECT b.sc_no AS bie_sc_no, b.bie_received, b.sc_status, b.sc_verify_status, b.sc_approve_status,
       (SELECT MAX(c.solving_date) FROM `bie_db`.tbl_sc_complaints c WHERE c.sc_id = b.sc_id AND c.complaint_status = 1) AS solving_date
FROM `bie_db`.tbl_service_call_register b
WHERE b.src_system = 'BENZEAR' AND b.src_sc_id = :benzear_sc_id
```

| BIE values | Show in Benzear |
|---|---|
| `bie_received = 0` | Sent to BIE - not opened yet |
| `sc_status = 1` | At BIE - Pending |
| `sc_status = 2`, `sc_verify_status = 0` | At BIE - Completed |
| `sc_status = 2`, `sc_verify_status = 1` | At BIE - Completed, in approval |
| `sc_status = 3` (or `sc_approve_status = 1`) | At BIE - Approved |

Per complaint: `` `bie_db`.tbl_sc_complaints `` by `src_complaint_id` gives `complaint_status` (0 open / 1 completed),
`action_taken` (1 phone, 2 on site, 3 in house), `solving_date`, `action_remarks`.

## 6. What BIE does

- The BIE service call list takes in every call with `bie_received = 0` when it opens: BIE number, branch (the BIE branch
  of the state, else Head Office), state / district / city / principal ids by name, the customer (same GST, else same
  mobile, else same name, else a new customer with its own ledger), then `bie_received = 1`.
- BIE completes the complaints and sends for approval / approves as for its own calls. The print shows
  "Transferred from Benzear: <Benzear no> (<who>, <when>)".

## 7. Test (done on BIE's side with copies of both live databases)

Benzear calls KL-2627-019 (Kerala, 1 open complaint) and AP-2627-003 (Andhra Pradesh, 2 open complaints) were written
with the two INSERTs above. On opening the BIE list: BIE-KL-2627-001 (Kerala branch, district / city / principal found,
matched to the existing customer by GST / mobile) and BIE-AP-2627-001 (Head Office, district not in BIE so left 0, new
customer C1406 made). KL completed in BIE; the status query above then returned `sc_status = 2` with the solving date.
