<?PHP
ob_start();
session_start();
ini_set('max_execution_time', '0');
require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-09-24 csrf token check + transaction rollback on all post handlers
// 2026-10-01 item rows moved off tbl_dc_details_temp - built in php, posted back as arrays
// 2026-10-01 packing moved off tbl_package_box_details_temp - rides on the item row, box counts done in javascript

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$_REQUEST['dc_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
if (isset($_POST['SAVE']))
{
    if (!csrf_check('dc_add')) {
    	csrf_fail('dc_list.php');
    }
    
    try
    {
    	db_begin($conn);
        $_REQUEST['dc_date'] = date("Y-m-d", strtotime($_REQUEST['dc_date']));

        $_REQUEST['dc_slno'] = $_REQUEST['pur_no'];//$dbconn->GetMaxValue('tbl_dc','dc_slno','company_id',$_SESSION['company_id'])+1;
        $_REQUEST['dc_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
        $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
        $_REQUEST['dc_refno'] = 'DC/'.$_REQUEST['dc_slno'].'/BIE/'.$_REQUEST['branch'].'/'.$_REQUEST['dc_finyr'];
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];

        $stmt = null;               
        $stmt = $conn->prepare("INSERT INTO tbl_dc (dc_finyr, dc_slno, dc_refno, dc_date, so_id, supp_id, cus_branch_id, corrugated_box, wooden_box, gunny_bags, poly_bags, modify_date_time, modify_by, branch_id) VALUES (:dc_finyr, :dc_slno, :dc_refno, :dc_date, :so_id, :supp_id, :cus_branch_id, :corrugated_box, :wooden_box, :gunny_bags, :poly_bags, :modify_date_time, :modify_by, :branch_id)");     
        $data = array(              
            ':dc_finyr' => $_REQUEST['dc_finyr'],
            ':dc_slno' => $_REQUEST['dc_slno'],
            ':dc_refno' => $_REQUEST['dc_refno'],
            ':dc_date' => $_REQUEST['dc_date'],
            ':so_id' => $_REQUEST['so_id'],
            ':supp_id' => $_REQUEST['supp_id'],
            ':cus_branch_id' => $_REQUEST['cus_branch_id'],
            ':corrugated_box' => $_REQUEST['corrugated_box'],
            ':wooden_box' => $_REQUEST['wooden_box'],
            ':gunny_bags' => $_REQUEST['gunny_bags'],
            ':poly_bags' => $_REQUEST['poly_bags'],
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':branch_id' => $_SESSION['_user_branch']
        );
        
        $stmt->execute($data);
        $last_id = $conn->lastInsertId();

            /* ------------ SAVE tbl_po_details  -----------*/
        $delete_details_sql =  "DELETE FROM tbl_dc_details 
                    WHERE dc_id = '".$last_id."'";
            $result_details_delete = $conn->prepare($delete_details_sql);
            $result_details_delete->execute();
        
        // item rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0) {
                    continue;
                }
                $stmt->execute(array(
                    ':dc_id'           => $last_id,
                    ':dc_item_id'      => $_REQUEST['dc_item_id'][$d],
                    ':dc_qty'          => $_REQUEST['dc_qty_h'][$d],
                    ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$d],
                    ':bal_qty'         => $_REQUEST['bal_qty'][$d],
                    ':dc_unit'         => $_REQUEST['dc_unit_h'][$d],
                    ':box_id'          => $_REQUEST['box_id'][$d],
                    ':no_of_box'       => $_REQUEST['no_of_box'][$d],
                    ':dc_remarks'      => $_REQUEST['dc_remarks'][$d]
                ));
            }
        }

        if($_REQUEST['so_id']>0)
        {

            $update_enq = $conn->prepare("UPDATE tbl_sales_order SET dc_status = :dc_status WHERE so_id = :so_id");
            $data1 = array(
                ':so_id' => $_REQUEST['so_id'],
                ':dc_status' => 1
            );
            $update_enq->execute($data1);
        }

        //Packing box details
        $pack_delete =  "DELETE FROM tbl_package_box_details WHERE dc_id = '".$last_id."'";
        $pack_delete_result = $conn->prepare($pack_delete);
        $pack_delete_result->execute();

        // packing rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, total_qty, box_id, dispatch_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :total_qty, :box_id, :dispatch_qty)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0 || trim($_REQUEST['pack_box_no_csv'][$d]) == '') {
                    continue;
                }
                $stmt->execute(array(
                    ':so_id'         => $_REQUEST['so_id'],
                    ':dc_id'         => $last_id,
                    ':item_id'       => $_REQUEST['dc_item_id'][$d],
                    ':pack_box_no'   => $_REQUEST['pack_box_no_csv'][$d],
                    ':pack_item_qty' => $_REQUEST['pack_item_qty_csv'][$d],
                    ':total_qty'     => $_REQUEST['pack_total_qty'][$d],
                    ':box_id'        => $_REQUEST['box_id'][$d],
                    ':dispatch_qty'  => $_REQUEST['dc_dispatch_qty'][$d]
                ));
            }
        }

    db_commit($conn);
    }
    catch (Exception $e)
    {
    	fnLogError($e);       
    	db_rollback($conn);
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
                    
    
    
    $_SESSION['_msg'] = "DC succesfully Saved..!";
    header("location:dc_list.php"); 
    die();
}

if (isset($_POST['UPDATE']))
{
    if (!csrf_check('dc_add')) {
    	csrf_fail('dc_list.php');
    }
    $update_id = $_REQUEST['txtHid'];
    try
    {
    	db_begin($conn);
        $_REQUEST['dc_date'] = date("Y-m-d", strtotime($_REQUEST['dc_date']));
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];
        $stmt = null;               
        $stmt = $conn->prepare("UPDATE  tbl_dc SET dc_slno = :dc_slno,dc_date = :dc_date, corrugated_box = :corrugated_box, wooden_box = :wooden_box, gunny_bags = :gunny_bags, poly_bags = :poly_bags, modify_date_time = :modify_date_time, modify_by = :modify_by, dc_verify_status = :dc_verify_status, dc_verify_date_time = :dc_verify_date_time, dc_verify_by = :dc_verify_by WHERE dc_id = :dc_id");      
        $data = array(
            ':dc_id' => $update_id,             
            ':dc_slno' => $_REQUEST['pur_no'],
            ':dc_date' => $_REQUEST['dc_date'],
            ':corrugated_box' => $_REQUEST['corrugated_box'],
            ':wooden_box' => $_REQUEST['wooden_box'],
            ':gunny_bags' => $_REQUEST['gunny_bags'],
            ':poly_bags' => $_REQUEST['poly_bags'], 
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':dc_verify_status' => '0',
            ':dc_verify_date_time' => '',
            ':dc_verify_by' => '0'
        );
        
        $stmt->execute($data);

        $sqldelete_details =  "DELETE FROM tbl_dc_details WHERE dc_id = '".$update_id."'";
        $result_details = $conn->prepare($sqldelete_details);
        $result_details->execute();

        // item rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0) {
                    continue;
                }
                $stmt->execute(array(
                    ':dc_id'           => $update_id,
                    ':dc_item_id'      => $_REQUEST['dc_item_id'][$d],
                    ':dc_qty'          => $_REQUEST['dc_qty_h'][$d],
                    ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$d],
                    ':bal_qty'         => $_REQUEST['bal_qty'][$d],
                    ':dc_unit'         => $_REQUEST['dc_unit_h'][$d],
                    ':box_id'          => $_REQUEST['box_id'][$d],
                    ':no_of_box'       => $_REQUEST['no_of_box'][$d],
                    ':dc_remarks'      => $_REQUEST['dc_remarks'][$d]
                ));
            }
        }

        //Packing box details
        $pack_delete =  "DELETE FROM tbl_package_box_details WHERE dc_id = '".$update_id."'";
        $pack_delete_result = $conn->prepare($pack_delete);
        $pack_delete_result->execute();

        // packing rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, total_qty, box_id, dispatch_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :total_qty, :box_id, :dispatch_qty)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0 || trim($_REQUEST['pack_box_no_csv'][$d]) == '') {
                    continue;
                }
                $stmt->execute(array(
                    ':so_id'         => $_REQUEST['so_id'],
                    ':dc_id'         => $update_id,
                    ':item_id'       => $_REQUEST['dc_item_id'][$d],
                    ':pack_box_no'   => $_REQUEST['pack_box_no_csv'][$d],
                    ':pack_item_qty' => $_REQUEST['pack_item_qty_csv'][$d],
                    ':total_qty'     => $_REQUEST['pack_total_qty'][$d],
                    ':box_id'        => $_REQUEST['box_id'][$d],
                    ':dispatch_qty'  => $_REQUEST['dc_dispatch_qty'][$d]
                ));
            }
        }

    db_commit($conn);
    }
    catch (Exception $e)
    {
    	fnLogError($e);       
    	db_rollback($conn);
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
    $_SESSION['_msg'] = "DC succesfully Updated..!";
    header("location:dc_list.php"); 
    die();
}

if (isset($_POST['FINALIZE']))
{
    if (!csrf_check('dc_add')) {
    	csrf_fail('dc_list.php');
    }
    $update_id = $_REQUEST['txtHid'];
    try
    {
    	db_begin($conn);
        $_REQUEST['dc_date'] = date("Y-m-d", strtotime($_REQUEST['dc_date']));
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];
        $stmt = null;               
        $stmt = $conn->prepare("UPDATE  tbl_dc SET dc_slno = :dc_slno,dc_date = :dc_date, corrugated_box = :corrugated_box, wooden_box = :wooden_box, gunny_bags = :gunny_bags, poly_bags = :poly_bags, modify_date_time = :modify_date_time, modify_by = :modify_by, dc_verify_status = :dc_verify_status, dc_verify_date_time = :dc_verify_date_time, dc_verify_by = :dc_verify_by, dc_approve_status = :dc_approve_status WHERE dc_id = :dc_id");      
        $data = array(
            ':dc_id' => $update_id,             
            ':dc_slno' => $_REQUEST['pur_no'],
            ':dc_date' => $_REQUEST['dc_date'],
            ':corrugated_box' => $_REQUEST['corrugated_box'],
            ':wooden_box' => $_REQUEST['wooden_box'],
            ':gunny_bags' => $_REQUEST['gunny_bags'],
            ':poly_bags' => $_REQUEST['poly_bags'], 
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':dc_verify_status' => '1',
            ':dc_approve_status' => '0',
            ':dc_verify_date_time' => date('Y-m-d H:i:s'),
            ':dc_verify_by' => $_SESSION['_userid']
        );
        
        $stmt->execute($data);

        $sql_details_delete =  "DELETE FROM tbl_dc_details WHERE dc_id = '".$update_id."'";
            $result_details_delete = $conn->prepare($sql_details_delete);
            $result_details_delete->execute();

        // item rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0) {
                    continue;
                }
                $stmt->execute(array(
                    ':dc_id'           => $update_id,
                    ':dc_item_id'      => $_REQUEST['dc_item_id'][$d],
                    ':dc_qty'          => $_REQUEST['dc_qty_h'][$d],
                    ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$d],
                    ':bal_qty'         => $_REQUEST['bal_qty'][$d],
                    ':dc_unit'         => $_REQUEST['dc_unit_h'][$d],
                    ':box_id'          => $_REQUEST['box_id'][$d],
                    ':no_of_box'       => $_REQUEST['no_of_box'][$d],
                    ':dc_remarks'      => $_REQUEST['dc_remarks'][$d]
                ));
            }
        }

        //Packing box details
        $pack_delete =  "DELETE FROM tbl_package_box_details WHERE dc_id = '".$update_id."'";
        $pack_delete_result = $conn->prepare($pack_delete);
        $pack_delete_result->execute();

        // packing rows come from the form, not a temp table
        if (isset($_REQUEST['dc_item_id']) && is_array($_REQUEST['dc_item_id'])) {
            $stmt = null;
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, total_qty, box_id, dispatch_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :total_qty, :box_id, :dispatch_qty)");

            for ($d = 0; $d < count($_REQUEST['dc_item_id']); $d++) {
                if ((int)$_REQUEST['dc_item_id'][$d] <= 0 || trim($_REQUEST['pack_box_no_csv'][$d]) == '') {
                    continue;
                }
                $stmt->execute(array(
                    ':so_id'         => $_REQUEST['so_id'],
                    ':dc_id'         => $update_id,
                    ':item_id'       => $_REQUEST['dc_item_id'][$d],
                    ':pack_box_no'   => $_REQUEST['pack_box_no_csv'][$d],
                    ':pack_item_qty' => $_REQUEST['pack_item_qty_csv'][$d],
                    ':total_qty'     => $_REQUEST['pack_total_qty'][$d],
                    ':box_id'        => $_REQUEST['box_id'][$d],
                    ':dispatch_qty'  => $_REQUEST['dc_dispatch_qty'][$d]
                ));
            }
        }

    db_commit($conn);
    }
    catch (Exception $e)
    {
    	fnLogError($e);       
    	db_rollback($conn);
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
    $_SESSION['_msg'] = "DC succesfully Updated..!";
    header("location:dc_list.php"); 
    die();
}


$dc_date = date('d-m-Y');
$dc_rows = array();   // item rows for the table, filled from the SO or the saved DC
        $pack_box_no1 = '';
        $pack_box_no2 = '';
        $pack_box_no3 = '';
        $pack_box_no4 = '';



if (isset($_REQUEST['dc_id']))
{

    try
    {

        
        $is_exit = $dbconn->GetSingleReconrd("tbl_package_box_details","pack_id","dc_id",$_REQUEST['dc_id']);
        
        if($is_exit > 0)
        {
            
            $sql = "SELECT GROUP_CONCAT(pack_box_no) as pack_box_no FROM tbl_package_box_details WHERE box_id = 1 AND dc_id = ".$_REQUEST['dc_id']." ";
            $res = $conn->query($sql);
            $boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
            if ($res->rowCount()>0)
            {
                while ($obj = $res->fetch())
                {
                    if($obj->pack_box_no !='')
                    {
                        $box_no = explode(',', $obj->pack_box_no);
                        $result1 = array_unique($box_no, SORT_REGULAR);
                        $boxtype1 = sizeof($result1);
                    }
                }
            }
            
            $sql2 = "SELECT GROUP_CONCAT(pack_box_no) as pack_box_no FROM tbl_package_box_details WHERE box_id = 2 AND dc_id = ".$_REQUEST['dc_id']." ";
            $res2 = $conn->query($sql2);
        
            if ($res2->rowCount()>0)
            {
                while ($obj2 = $res2->fetch())
                {
                    if($obj2->pack_box_no !='')
                    {
                        $box_no = explode(',', $obj2->pack_box_no);
                        $result2 = array_unique($box_no, SORT_REGULAR);
                        $boxtype2 = sizeof($result2);
                    }
                }
            }
            
            $sql3 = "SELECT GROUP_CONCAT(pack_box_no) as pack_box_no FROM tbl_package_box_details WHERE box_id = 3 AND dc_id = ".$_REQUEST['dc_id']." ";
            $res3 = $conn->query($sql3);
            
            if ($res3->rowCount()>0)
            {
                while ($obj3 = $res3->fetch())
                {
                    if($obj3->pack_box_no !='')
                    {
                        $box_no = explode(',', $obj3->pack_box_no);
                        $result3 = array_unique($box_no, SORT_REGULAR);
                        $boxtype3 = sizeof($result3);
                    }
                }
            }
            
            $sql4 = "SELECT GROUP_CONCAT(pack_box_no) as pack_box_no FROM tbl_package_box_details WHERE box_id = 4 AND dc_id = ".$_REQUEST['dc_id']." ";
            $res4 = $conn->query($sql4);
        
            if ($res4->rowCount()>0)
            {
                while ($obj4 = $res4->fetch())
                {
                    if($obj4->pack_box_no !='')
                    {
                        $box_no = explode(',', $obj4->pack_box_no);
                        $result4 = array_unique($box_no, SORT_REGULAR);
                        $boxtype4 = sizeof($result4);
                    }
                }
            }
        }

    
        // rows for the table come straight from tbl_dc_details, no temp table
        $result1 = $conn->prepare("SELECT b.* FROM tbl_dc as a
                        LEFT JOIN tbl_dc_details as b ON a.dc_id = b.dc_id
                        WHERE a.dc_status = 1 AND b.dc_id = :dc_id");
        $result1->execute(array(':dc_id' => $_REQUEST['dc_id']));

        $pack_q = $conn->prepare("SELECT pack_box_no, pack_item_qty, total_qty FROM tbl_package_box_details
                                   WHERE dc_id = :dc_id AND item_id = :item_id");

        while ($value = $result1->fetch(PDO::FETCH_ASSOC)) {
            // packing for this line travels with the row instead of a temp table
            $pack_q->execute(array(':dc_id' => $_REQUEST['dc_id'], ':item_id' => $value['dc_item_id']));
            $pk = $pack_q->fetch(PDO::FETCH_ASSOC);

            $dc_rows[] = array(
                'dc_item_id'       => $value['dc_item_id'],
                'dc_qty'           => $value['dc_qty'],
                'dc_dispatch_qty'  => $value['dc_dispatch_qty'],
                'bal_qty'          => $value['bal_qty'],
                'dc_unit'          => $value['dc_unit'],
                'box_id'           => $value['box_id'],
                'no_of_box'        => $value['no_of_box'],
                'dc_remarks'       => $value['dc_remarks'],
                'pack_box_no'      => $pk ? $pk['pack_box_no'] : '',
                'pack_item_qty'    => $pk ? $pk['pack_item_qty'] : '',
                'pack_total_qty'   => $pk ? $pk['total_qty'] : ''
            );
        }

    }
    catch (Exception $e)
    {
    	fnLogError($e);       
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }

    $result = $conn->query("SELECT * FROM tbl_dc WHERE dc_status = '1' AND dc_id = ".$_REQUEST['dc_id']);   
    if ($result->rowCount()>0)
    {
        $get = $result->fetch(PDO::FETCH_OBJ);          
        
        if($get->dc_date != "0000-00-00" && $get->dc_date != ""){
            $dc_date = date("d-m-Y", strtotime($get->dc_date));
        }
    
        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_status = '1' AND supp_id",$get->supp_id);

        
            
        $supp_id = $get->supp_id;
        $cus_branch_id = $get->cus_branch_id;
        $so_id = $get->so_id;
    }
}
elseif (isset($_REQUEST['so_id'])) 
{
    $so_id = $_REQUEST['so_id'];
    // a new DC has nothing packed yet; the counts are kept in step by javascript
    $boxtype1 = $boxtype2 = $boxtype3 = $boxtype4 = 0;

    // rows for the table come straight from the sales order, no temp table
    $result1 = $conn->prepare("SELECT b.* FROM tbl_sales_order as a
                    LEFT JOIN tbl_sales_order_details as b ON a.so_id = b.so_id
                    WHERE b.so_id = :so_id");
    $result1->execute(array(':so_id' => $_REQUEST['so_id']));

    $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);

    while ($value = $result1->fetch(PDO::FETCH_ASSOC)) {
        $avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $value['item_id']);
        $dc_dispatch_qty = 0;

        if ($_REQUEST['status'] == 'partial') {
            $already = $conn->prepare("SELECT SUM(dc_dispatch_qty) as total_dispatch_qty FROM tbl_dc_details
                                        WHERE dc_id IN (SELECT dc_id FROM tbl_dc WHERE so_id = :so_id AND dc_approve_status = '1')
                                          AND dc_item_id = :item_id");
            $already->execute(array(':so_id' => $_REQUEST['so_id'], ':item_id' => $value['item_id']));
            $obj1 = $already->fetch(PDO::FETCH_OBJ);
            $dc_dispatch_qty = $value['so_qty'] - $obj1->total_dispatch_qty;
        } elseif ($avl_qty >= $value['so_qty']) {
            $dc_dispatch_qty = $value['so_qty'];
        }

        $dc_rows[] = array(
            'dc_item_id'      => $value['item_id'],
            'dc_qty'          => $value['so_qty'],
            'dc_dispatch_qty' => $dc_dispatch_qty,
            'bal_qty'         => 0,
            'dc_unit'         => $value['so_unit'],
            'box_id'          => 0,
            'no_of_box'       => '',
            'dc_remarks'      => '',
            'pack_box_no'     => '',
            'pack_item_qty'   => '',
            'pack_total_qty'  => ''
        );
    }

    $result = $conn->query("SELECT * FROM tbl_sales_order WHERE so_id = ".$_REQUEST['so_id']);   
    if ($result->rowCount()>0)
    {
        $get = $result->fetch(PDO::FETCH_OBJ);          
        $so_id = $_REQUEST['so_id'];
        // if($get->dc_date != "0000-00-00" && $get->dc_date != ""){
        //     $dc_date = date("d-m-Y", strtotime($get->dc_date));
        // }
    
       // $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_status = '1' AND supp_id",$get->supp_id);

        // $jpo_no = $dbconn->GetSingleReconrd("tbl_jpo","jpo_slno","jpo_status = '1' AND so_id",$get->so_id);
            
        // $supp_id = $get->supp_id;
        // $so_id = $get->so_id;
        $cus_branch_id=$get->branch_id;
        $so_id = $get->so_id;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Sales Order</title>
    <link href="css/main.css" rel="stylesheet" type="text/css" />

    <?php include_once("inc/common/css-js.php"); ?>

    <!-- AUTO COMPLETE -->
    <script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />
</head>

<body>
    <?php include("modal_supp_dets.php") ?>
    <?php include("inc/common/header.php") ?>
    <!-- /main navbar -->
    <!-- Page content -->
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Sales</a>
                            <span class="breadcrumb-item active">Delivery Challan</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- This Form UI Starts here --->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="dc_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                        	<?php csrf_fields('dc_add'); ?>
                            <fieldset>
                                <input type="hidden" name="so_id" id="so_id" value="<?php echo $so_id; ?>">
                                <input type="hidden" name="supp_id" id="supp_id" value="<?php echo $get->supp_id; ?>">
                                <input type="hidden" name="cus_branch_id" id="cus_branch_id" value="<?php echo $cus_branch_id; ?>">
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">New Delivery Challan</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                                <a class="list-icons-item" href="lst_sales_order.php" title="Sales Order List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                        

                                    </div>
                                    <?php 
                                        if($_REQUEST['dc_id'] != "")
                                        {
                                            $dc_no = leadingZeros($dbconn->GetSingleReconrd('tbl_dc','dc_slno','dc_status = "1" AND dc_id',$_REQUEST['dc_id']),4);
                                        }
                                        else
                                        {
                                            $dc_no = leadingZeros($dbconn->GetMaxValue('tbl_dc','dc_slno','branch_id="'.$_SESSION['_user_branch'].'" AND dc_finyr',$_REQUEST['dc_finyr'])+1,4); 
                                        }
                                        $so_no = $dbconn->GetSingleReconrd("tbl_sales_order","so_refno","so_id",$so_id);
                                        $cus_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $get->supp_id);
                                    ?>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">DC No <span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="pur_no" readonly id="pur_no" value="<?php echo $dc_no; ?>" />
                                            </div>

                                            <label class="col-lg-2 col-form-label">DC Date <span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">

                                                <input type="date" name="dc_date" id="dc_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" />
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">SO No <span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="so_no" id="so_no" readonly value="<?php echo $so_no; ?>" />
                                            </div>

                                            <label class="col-lg-2 col-form-label">Customer Name </label>
                                            <div class="col-lg-4">
                                                <input type="text" name="supp_name" id="supp_name" class="form-control" readonly value="<?php echo $cus_name; ?>" />
                                            </div>
                                        </div>

                                        <legend class="font-weight-semibold "><i class='fas fa-box'></i>&nbsp; DC Details</legend>
                                        <div class="form-group row">
                                            <div id="quo_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th>S.No</th>
                                                            <th>Description</th>
                                                            <th>Unit</th>
                                                            <th>In Stock</th>
                                                            <th>So Qty </th>
                                                            <th>Despatched </th>
                                                            <th>This DC </th>
                                                            <th>To Follow</th>
                                                            <th>Type of Box</th>
                                                            <th width="12%">Box Count</th>
                                                            <th>Remarks</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>

                                                        <?php

                                                            if (count($dc_rows) > 0)
                                                            {
                                                                $iSno=1;
                                                                foreach ($dc_rows as $dc_row)
                                                                {
                                                                    $obj = (object)$dc_row;
                                                                    $_SESSION['token'] = md5(session_id() . time().$iSno);
                                                                    if($so_id>0)
                                                                    {
                                                                        $already_dispatch_qty = $conn->query("SELECT SUM(dc_dispatch_qty) as total_dispatch_qty FROM `tbl_dc_details` WHERE dc_id IN (SELECT dc_id FROM tbl_dc WHERE so_id='".$so_id."' AND dc_approve_status='1') AND dc_item_id='".$obj->dc_item_id."'");
                                                                    }
                                                                    else
                                                                    {
                                                                        $already_dispatch_qty='';
                                                                    }
                                                                    
                                                                    if ($already_dispatch_qty->rowCount()>0)
                                                                    {
                                                                        $obj1 = $already_dispatch_qty->fetch(PDO::FETCH_OBJ);
                                                                    }

                                                                    $temp_item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id",$obj->dc_item_id);
                                                                    $temp_item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id",$obj->dc_item_id);
                                                                    $item_type = $dbconn->GetSingleReconrd("tbl_item_details","item_type","item_status = '1' AND item_id",$obj->dc_item_id);

                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);

                                                                    $temp_avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$obj->dc_item_id);

                                                                    //$temp_avl_qty = $dbconn->GetSingleReconrd("tbl_item_details","item_curr_stock","item_status = '1' AND item_id",$obj->dc_item_id);
                                                                    
                                                                   // $pack_box_no = $dbconn->GetSingleReconrd("tbl_package_box_details","pack_box_no"," item_id",$obj->dc_item_id); 
                                                                    if($obj->dc_qty == $obj1->total_dispatch_qty)
                                                                    {
                                                                        echo '<tr>
                                                                            <td>'.$iSno.'</td>
                                                                            <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>

                                                                            <td style = "text-align:center;">'.$obj->dc_unit.'</td>

                                                                            <td style = "text-align:center;">'.$temp_avl_qty.'<input type="hidden" name="temp_avl_qty" class="temp_avl_qty" value="'.$temp_avl_qty.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->dc_qty.'<input type="hidden" name="hide_dc_qty" class="hide_dc_qty" value="'.$obj->dc_qty.'"></td>

                                                                            <td style = "text-align:center;">'.$obj1->total_dispatch_qty.'<input type="hidden" name="hide_tot_dispatch_qty" class="hide_tot_dispatch_qty" value="'.$obj1->total_dispatch_qty.'"></td>

                                                                            <td><input type="text" name="dc_dispatch_qty[]" id="dc_dispatch_qty" readonly style="width:75px;" tabindex="-1" class="form-control validate[required] dc_dispatch_qty" value="'.$obj->dc_dispatch_qty.'"><input type="hidden" name="hide_dispatch_qty" class="hide_dispatch_qty" value="'.$obj->dc_dispatch_qty.'"></td>

                                                                            <td><input type="text" readonly name="bal_qty[]" tabindex="-1" id="bal_qty" class="form-control bal_qty" style="width:75px;" value="'.$obj->bal_qty.'"></td>

                                                                            <td>
                                                                                <select name="box_id[]" 
                                                                                class="select box_id"
                                                                                id= "box_id_'.$iSno.'"
                                                                                >

                                                                                    <option value="0">Select Box Type</option>
                                                                                        
                                                                                </select>
                                                                            </td>

                                                                            <td><div class="input-append"><input type="text" readonly tabindex="-1" style="width:85px;" name="no_of_box[]" class="form-control no_of_box"  id= "no_of_box_'.$iSno.'" onkeypress="return isNumberKey_With_Dot(event)" value="'.$obj->no_of_box.'" ></div></td>

                                                                            <td><input type="text" readonly tabindex="-1" class="form-control" name="dc_remarks[]" id="dc_remarks" value="'.$obj->dc_remarks.'"><input type="hidden" name="dc_item_id[]" value="'.$obj->dc_item_id.'"><input type="hidden" name="dc_qty_h[]" value="'.$obj->dc_qty.'"><input type="hidden" name="dc_unit_h[]" value="'.$obj->dc_unit.'"><input type="hidden" name="pack_box_no_csv[]" class="pack_box_no_csv" value="'.htmlspecialchars($obj->pack_box_no, ENT_QUOTES).'"><input type="hidden" name="pack_item_qty_csv[]" class="pack_item_qty_csv" value="'.htmlspecialchars($obj->pack_item_qty, ENT_QUOTES).'"><input type="hidden" name="pack_total_qty[]" class="pack_total_qty" value="'.htmlspecialchars($obj->pack_total_qty, ENT_QUOTES).'"></td>
                                                                        </tr>';
                                                                    }
                                                                    else
                                                                    {
                                                                        
                                                                        echo '<tr>
                                                                        <td>'.$iSno.'</td>
                                                                        <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>
                                                                        <td style = "text-align:center;">'.$obj->dc_unit.'</td>

                                                                        <td style = "text-align:center;">'.$temp_avl_qty.'<input type="hidden" name="temp_avl_qty" class="temp_avl_qty" value="'.$temp_avl_qty.'"></td>

                                                                        
                                                                        <td style = "text-align:center;">'.$obj->dc_qty.'<input type="hidden" name="hide_dc_qty" class="hide_dc_qty" value="'.$obj->dc_qty.'"></td>

                                                                        <td style = "text-align:center;">'.$obj1->total_dispatch_qty.'<input type="hidden" name="hide_tot_dispatch_qty" class="hide_tot_dispatch_qty" value="'.$obj1->total_dispatch_qty.'"></td>

                                                                        <td><input type="text" name="dc_dispatch_qty[]" id="dc_dispatch_qty" onKeyPress="return isNumberKey(event)" style="width:75px;" class="form-control validate[required] dc_dispatch_qty" value="'.$obj->dc_dispatch_qty.'"><input type="hidden" name="hide_dispatch_qty" class="hide_dispatch_qty" value="'.$obj->dc_dispatch_qty.'"></td>

                                                                        <td><input type="text" readonly name="bal_qty[]" tabindex="-1" id="bal_qty" class="form-control bal_qty" style="width:75px;" value="'.$obj->bal_qty.'"></td>


                                                                        <td>
                                                                            <select name="box_id[]" 
                                                                            class="select box_id"
                                                                            id= "box_id_'.$iSno.'"
                                                                            >

                                                                                <option value="0">Select Box Type</option>';
                                                                                    echo $dbconn->fnFillComboFromTable_Where("box_id","box_name","tbl_dc_package_box","box_id"," WHERE box_status = '1'");
                                                                            echo '</select>
                                                                            <script>document.thisForm.box_id_'.$iSno.'.value="'.$obj->box_id.'";
                                                                            </script>';
                                                                        echo '</td>    

                                                                        <td><div class="input-append"><input type="text" name="no_of_box[]" onKeyPress="return isNumberKey(event)" style="width:85px;" maxlength="3" class="no_of_box" id= "no_of_box_'.$iSno.'" onkeypress="return isNumberKey_With_Dot(event)" value="'.$obj->no_of_box.'" > <a  data-toggle="modal" data-target="#modalDCPack" href="" data-id="'.$obj->temp_dc_id.'" data-popup="tooltip" title="" class="btn btn-success fancybox">Box</a> 
                                                                        
                                                                       


                                                                       <td><input type="text"  class="form-control" tabindex="-1" name="dc_remarks[]" id="dc_remarks" value="' . $obj->dc_remarks . '"><input type="hidden" name="dc_item_id[]" value="'.$obj->dc_item_id.'"><input type="hidden" name="dc_qty_h[]" value="'.$obj->dc_qty.'"><input type="hidden" name="dc_unit_h[]" value="'.$obj->dc_unit.'"><input type="hidden" name="pack_box_no_csv[]" class="pack_box_no_csv" value="'.htmlspecialchars($obj->pack_box_no, ENT_QUOTES).'"><input type="hidden" name="pack_item_qty_csv[]" class="pack_item_qty_csv" value="'.htmlspecialchars($obj->pack_item_qty, ENT_QUOTES).'"><input type="hidden" name="pack_total_qty[]" class="pack_total_qty" value="'.htmlspecialchars($obj->pack_total_qty, ENT_QUOTES).'"></td>

                                                                       <input type="hidden" name="hide_item_id" class="hide_item_id" value="'.$obj->dc_item_id.'">
                                                                       <input type="hidden" name="item_type" class="item_type" id="item_type" value="'.$item_type.'">

                                                                       <input type="hidden" name="hide_token" class="hide_token" value="'.$_SESSION['token'].'">
                                                                       
                                                                        </tr>';
                                                                    }

                                                                    $iSno++;
                                                                }
                                                            }
                                                        

                                                        ?>

                                                    </tbody>
                                                    <tfoot>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <legend class="font-weight-semibold"></legend>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">Corrugated Box <span class="text-mandatory"></span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="corrugated_box" id="corrugated_box" readonly readonly value="<?php echo $boxtype1; ?>" />
                                            </div>
                                            <label class="col-lg-2 col-form-label">Wooden Box </label>
                                            <div class="col-lg-4">
                                                <input type="text" name="wooden_box" id="wooden_box" class="form-control" readonly value="<?php echo $boxtype2; ?>" />
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">Gunny Bags <span class="text-mandatory"></span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="gunny_bags" id="gunny_bags" readonly value="<?php echo $boxtype3; ?>" />
                                            </div>
                                            <label class="col-lg-2 col-form-label">Poly Bags </label>
                                            <div class="col-lg-4">
                                                <input type="text" name="poly_bags" id="poly_bags" class="form-control" readonly value="<?php echo $boxtype4; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <?php if(isset($_REQUEST['dc_id'])){ ?>
                                            <INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
                                            <INPUT class="btn btn-warning mr-2" type="submit" name="FINALIZE" value="Send for Approval" >
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['dc_id'];?>">
                                        <?php }elseif(isset($_REQUEST['so_id'])){ ?>
                                            <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                        <?php } ?>
                                    </div>
                                </div>
                    </div>
                </div>

                </fieldset>
                </form>

            </div>
            <!-- Footer -->
            <?php include("inc/common/footer.php") ?>
            <!-- /footer -->
        </div>
    </div>
    </div>
    </div>
</body>
<?php include("modal_dc_pack.php") ?>
</html>
<!---------Script-------->

<!-- AUTO COMPLETE -->
<script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />


<script type="text/javascript">
	var wasSubmitted = false;
    function fnValidate() {
		if (!wasSubmitted) {
            wasSubmitted = true;
            document.thisForm.submit();
            return true;
        }
        return false;
    }
    $(function() {


        $(".dc_dispatch_qty").change(function(){
            // alert();
            var dispatch_qty = $(this).val();
            var hide_dispatch_qty = $(this).closest('tr').find('.hide_dispatch_qty').val();
            var hide_dc_qty = $(this).closest('tr').find('.hide_dc_qty').val();
		    var temp_avl_qty = $(this).closest('tr').find('.temp_avl_qty').val();
            var hide_tot_dispatch_qty = $(this).closest('tr').find('.hide_tot_dispatch_qty').val();
            var item_type = $('.item_type').val();
           //var bal_qty = $(this).closest('tr').find('.bal_qty').val();
           //
           //alert(item_type);
			if(item_type !=8){
				if(parseInt(dispatch_qty) > parseInt(temp_avl_qty))
				{
					
					alert('Current Stock not Available for this DC Item');
					$(this).val(0);
					$(this).closest('tr').find('.bal_qty').val('');
					return false;
				}

			}
          
        

            if(hide_tot_dispatch_qty != '')
            {
                var validate_qty = parseInt(hide_dc_qty)-parseInt(hide_tot_dispatch_qty);
                //alert("dispatch_qty: "+dispatch_qty+" validate_qty:"+validate_qty);
                if(parseInt(dispatch_qty) > parseInt(validate_qty))
                {
                    alert('Dispatch Qty Must be Less Than the SO Qty');
                    $(this).val('');
                    $(this).closest('tr').find('.bal_qty').val('');
                    return false;
                }

                var bal_qty = parseInt(hide_dc_qty)-(parseInt(dispatch_qty)+parseInt(hide_tot_dispatch_qty));
                
            }
            else
            {
                if(parseInt(dispatch_qty) > parseInt(hide_dc_qty))
                {
                    alert('Dispatch Qty Must be Less Than the SO Qty');
                    $(this).val('');
                    $(this).closest('tr').find('.bal_qty').val('');
                    return false;
                }

                var bal_qty = parseInt(hide_dc_qty)-parseInt(dispatch_qty);
            }

            $(this).closest('tr').find('.bal_qty').val(bal_qty);
            
        });
        $(".dc_dispatch_qty,.no_of_box,.box_id").change(function(){
        // var dispatch_qty = $(".").val();
        
            var dispatch_qty = $(this).closest('tr').find('.dc_dispatch_qty').val();
            var box_count = $(this).closest('tr').find('.no_of_box').val();
            var item_id = $(this).closest('tr').find('.hide_item_id').val();
            var token = $(this).closest('tr').find('.hide_token').val();
            var box_id = $(this).closest('tr').find('.box_id').val();
            var so_id = $("#so_id").val();
            var dc_id = $("#txtHid").val();
            
            
            if(dispatch_qty >0 && box_count >0 && box_id >0)
            {
                $(this).closest('tr').find('.fancybox').fadeIn('slow');
                // var url = "inc/popup/fancybox_assign_pack_box.php"
                
                // $(this).closest('tr').find('.fancybox').attr('href', url + '?dispatch_qty='+dispatch_qty+'&box_count='+box_count+'&item_id='+item_id+'&so_id='+so_id+'&dc_id='+dc_id+'&token='+token+'&box_id='+box_id);
                
                // var testResult1 = $(".fancybox").contents().find('input#corrugated_box').val();
                // $('#corrugated_box').attr('value', testResult1);
                // var testResult2 = $(".fancybox").contents().find('input#wooden_box').val();
                // $('#wooden_box').attr('value', testResult2);
                // var testResult3 = $(".fancybox").contents().find('input#gunny_bags').val();
                // $('#gunny_bags').attr('value', testResult3);
                // var testResult4 = $(".fancybox").contents().find('input#poly_bags').val();
                // $('#poly_bags').attr('value', testResult4);
            }
            else
            {
                // alert('');
                
                
                $(this).closest('tr').find('.fancybox').fadeOut('slow');
                
                // $(this).closest('tr').find(getElementsByClassName("fancybox")).disabled=true;
            }

        }).trigger('change');


        // the row being packed, so SAVE knows where to write back
        var packRow = null;

        $('#modalDCPack').on('show.bs.modal', function(e) {
            packRow = $(e.relatedTarget).closest('tr');

            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_dc_pack_dets.php',
                data: {
                    'item_id':        packRow.find('.hide_item_id').val(),
                    'box_count':      packRow.find('.no_of_box').val(),
                    'dispatch_qty':   packRow.find('.dc_dispatch_qty').val(),
                    'pack_box_no':    packRow.find('.pack_box_no_csv').val(),
                    'pack_item_qty':  packRow.find('.pack_item_qty_csv').val(),
                    'pack_total_qty': packRow.find('.pack_total_qty').val()
                },
                success: function(data) {
                    var string = data.split("~");
                    $('#m_sales_rec').html(string[0]);
                    $('#m_sales_code').html(string[1]);
                }
            });
        });

        // running total inside the modal
        $(document).on('change', '#modalDCPack .qty', function() {
            var sum = 0;
            $('#modalDCPack .qty').each(function() {
                if ($(this).val() === '') { $(this).val(0); }
                sum += parseFloat($(this).val()) || 0;
            });
            $('#total').val(sum);

            if (sum > parseFloat($('#pack_dispatch_qty').val() || 0)) {
                alert("Qty must be less equal to dispatch qty");
                $('#total').val('');
            }
        });

        // SAVE writes back into the row, nothing goes to the database here
        $(document).on('click', '#modalDCPack #SAVE', function() {
            var count = parseInt($('#pack_box_count').val() || 0, 10);
            var box_no = [], qty = [];

            for (var i = 1; i <= count; i++) {
                var bn = $.trim($('#pack_box_no' + i).val());
                var bq = $.trim($('#pack_item_qty' + i).val());
                if (bn === '') { alert("One or more Box Number Missing.."); return false; }
                if (bq === '') { alert("One or more Box Qty Missing..");    return false; }
                box_no.push(bn);
                qty.push(bq);
            }

            var total = parseFloat($('#total').val() || 0);
            if (total !== parseFloat($('#pack_dispatch_qty').val() || 0)) {
                alert("Total qty and dispatch qty must be same");
                return false;
            }

            if (packRow) {
                packRow.find('.pack_box_no_csv').val(box_no.join(','));
                packRow.find('.pack_item_qty_csv').val(qty.join(','));
                packRow.find('.pack_total_qty').val(total);
            }

            fnCountBoxTypes();
            $("#modalDCPack .close").click();
        });

        // distinct box numbers per box type, across every row of this DC
        // 1 corrugated, 2 wooden, 3 gunny, 4 poly
        function fnCountBoxTypes() {
            var seen = {1: {}, 2: {}, 3: {}, 4: {}};

            $('#quo_table tbody tr').each(function() {
                var type = parseInt($(this).find('.box_id').val() || 0, 10);
                var csv = $(this).find('.pack_box_no_csv').val() || '';
                if (!seen[type] || csv === '') { return; }

                $.each(csv.split(','), function(i, n) {
                    n = $.trim(n);
                    if (n !== '') { seen[type][n] = true; }
                });
            });

            $('#corrugated_box').val(Object.keys(seen[1]).length);
            $('#wooden_box').val(Object.keys(seen[2]).length);
            $('#gunny_bags').val(Object.keys(seen[3]).length);
            $('#poly_bags').val(Object.keys(seen[4]).length);
        }

        $(function() { fnCountBoxTypes(); });
    });


   
</script>