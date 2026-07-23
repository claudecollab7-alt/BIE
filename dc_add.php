<?PHP
ob_start();
session_start();
ini_set('max_execution_time', '0');
require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$_REQUEST['dc_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
if (isset($_POST['SAVE']))
{
    
    try
    {
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
        
        $result = $conn->query("SELECT * FROM tbl_dc_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_dc_id");
            
        if ($result->rowCount()>0)
        {
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($obj as $row => $value) 
                {
                    //print_r($_REQUEST['dc_remarks'][$row]);exit;
                    $data = array(              
                        ':dc_id' => $last_id,
                        ':dc_item_id' => $value['temp_dc_item_id'],
                        ':dc_qty' => $value['temp_dc_qty'],
                        ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$row],
                        ':bal_qty' => $_REQUEST['bal_qty'][$row],
                        ':dc_unit' => $value['temp_dc_unit'],
                        ':box_id' => $_REQUEST['box_id'][$row],
                        ':no_of_box' => $_REQUEST['no_of_box'][$row],
                        ':dc_remarks' => $_REQUEST['dc_remarks'][$row]
                    );
                    $stmt->execute($data);
                }
            }
        
            $sql_temp_delete =  "DELETE FROM tbl_dc_details_temp 
                    WHERE session_id = '".$_SESSION['session_id']."'";
            $result_temp_delete = $conn->prepare($sql_temp_delete);
            $result_temp_delete->execute();
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

        $pack_result = $conn->query("SELECT * FROM tbl_package_box_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_dc_id");
            
        if ($pack_result->rowCount()>0)
        {
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, total_qty, box_id, dispatch_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :total_qty, :box_id, :dispatch_qty)");

            while ($pa = $pack_result->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($pa as $row => $value) 
                {
                    $data = array(              
                        ':dc_id' => $last_id,
                        ':so_id' => $_REQUEST['so_id'],
                        ':item_id' => $value['temp_item_id'],
                        ':pack_box_no' => $value['temp_pack_box_no'],
                        ':pack_item_qty' => $value['temp_pack_item_qty'],
                        ':box_id' => $value['temp_box_id'],
                        ':dispatch_qty' => $value['temp_dispatch_qty'],
                        ':total_qty' => $value['temp_total_qty']
                    );
                    $stmt->execute($data);
                }
            }

            
        
            $pack_sql =  "DELETE FROM tbl_package_box_details_temp 
                    WHERE session_id = '".$_SESSION['session_id']."'";
            $pack_temp_result = $conn->prepare($pack_sql);
            $pack_temp_result->execute();
        }
    
    }
    catch (Exception $e)
    {       
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
                    
    
    
    $_SESSION['_msg'] = "DC succesfully Saved..!";
    header("location:dc_list.php"); 
    die();
}

if (isset($_POST['UPDATE']))
{
    $update_id = $_REQUEST['txtHid'];
    try
    {
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

        $result = $conn->query("SELECT * FROM tbl_dc_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_dc_id");
            
        if ($result->rowCount()>0)
        {
            $quo_value = 0;
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($obj as $row => $value) {
                    $data = array(              
                        ':dc_id' => $update_id,
                        ':dc_item_id' => $value['temp_dc_item_id'],
                        ':dc_qty' => $value['temp_dc_qty'],
                        ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$row],
                        ':bal_qty' => $_REQUEST['bal_qty'][$row],
                        ':dc_unit' => $value['temp_dc_unit'],
                        ':box_id' => $_REQUEST['box_id'][$row],
                        ':no_of_box' => $_REQUEST['no_of_box'][$row],
                        ':dc_remarks' => $_REQUEST['dc_remarks'][$row]
                    );
                    $stmt->execute($data);
                    
                }
            }
        
            $sql =  "DELETE FROM tbl_dc_details_temp WHERE session_id = '".$_SESSION['session_id']."'";
            $result = $conn->prepare($sql);
            $result->execute();
        }


        //Packing box details
        $pack_delete =  "DELETE FROM tbl_package_box_details WHERE dc_id = '".$update_id."'";
        $pack_delete_result = $conn->prepare($pack_delete);
        $pack_delete_result->execute();

        $pack_result = $conn->query("SELECT * FROM tbl_package_box_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_dc_id");
            
        if ($pack_result->rowCount()>0)
        {
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, box_id, dispatch_qty, total_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :box_id, :dispatch_qty, :total_qty)");

            while ($pa = $pack_result->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($pa as $row => $value) 
                {
                    $data = array(              
                        ':dc_id' => $update_id,
                        ':so_id' => $_REQUEST['so_id'],
                        ':item_id' => $value['temp_item_id'],
                        ':pack_box_no' => $value['temp_pack_box_no'],
                        ':pack_item_qty' => $value['temp_pack_item_qty'],
                        ':box_id' => $value['temp_box_id'],
                        ':dispatch_qty' => $value['temp_dispatch_qty'],
                        ':total_qty' => $value['temp_total_qty']
                    );
                    $stmt->execute($data);
                }
            }

            
        
            $pack_sql =  "DELETE FROM tbl_package_box_details_temp 
                    WHERE session_id = '".$_SESSION['session_id']."'";
            $pack_temp_result = $conn->prepare($pack_sql);
            $pack_temp_result->execute();
        }
    }
    catch (Exception $e)
    {       
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
    $_SESSION['_msg'] = "DC succesfully Updated..!";
    header("location:dc_list.php"); 
    die();
}

if (isset($_POST['FINALIZE']))
{
    $update_id = $_REQUEST['txtHid'];
    try
    {
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

        $result_temp = $conn->query("SELECT * FROM tbl_dc_details_temp WHERE session_id = '".$_SESSION['session_id']."' ");
            
        if ($result_temp->rowCount()>0)
        {
            $quo_value = 0;
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details (dc_id, dc_item_id, dc_qty, dc_dispatch_qty, bal_qty, dc_unit, box_id, no_of_box, dc_remarks) VALUES (:dc_id, :dc_item_id, :dc_qty, :dc_dispatch_qty, :bal_qty, :dc_unit, :box_id, :no_of_box, :dc_remarks)");

            while ($obj = $result_temp->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($obj as $row => $value) {
                    $data = array(              
                        ':dc_id' => $update_id,
                        ':dc_item_id' => $value['temp_dc_item_id'],
                        ':dc_qty' => $value['temp_dc_qty'],
                        ':dc_dispatch_qty' => $_REQUEST['dc_dispatch_qty'][$row],
                        ':bal_qty' => $_REQUEST['bal_qty'][$row],
                        ':dc_unit' => $value['temp_dc_unit'],
                        ':box_id' => $_REQUEST['box_id'][$row],
                        ':no_of_box' => $_REQUEST['no_of_box'][$row],
                        ':dc_remarks' => $_REQUEST['dc_remarks'][$row]
                    );
                    $stmt->execute($data);
                    
                }
            }
        
            $sql_temp_delete =  "DELETE FROM tbl_dc_details_temp WHERE session_id = '".$_SESSION['session_id']."'";
            $result_temp_delete = $conn->prepare($sql_temp_delete);
            $result_temp_delete->execute();
        }


        //Packing box details
        $pack_delete =  "DELETE FROM tbl_package_box_details WHERE dc_id = '".$update_id."'";
        $pack_delete_result = $conn->prepare($pack_delete);
        $pack_delete_result->execute();

        $pack_result = $conn->query("SELECT * FROM tbl_package_box_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_dc_id");
            
        if ($pack_result->rowCount()>0)
        {
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details (so_id, dc_id, item_id, pack_box_no, pack_item_qty, box_id, dispatch_qty, total_qty) VALUES (:so_id, :dc_id, :item_id, :pack_box_no, :pack_item_qty, :box_id, :dispatch_qty, :total_qty)");

            while ($pa = $pack_result->fetchAll(PDO::FETCH_ASSOC)) 
            {
                foreach ($pa as $row => $value) 
                {
                    $data = array(              
                        ':dc_id' => $update_id,
                        ':so_id' => $_REQUEST['so_id'],
                        ':item_id' => $value['temp_item_id'],
                        ':pack_box_no' => $value['temp_pack_box_no'],
                        ':pack_item_qty' => $value['temp_pack_item_qty'],
                        ':box_id' => $value['temp_box_id'],
                        ':dispatch_qty' => $value['temp_dispatch_qty'],
                        ':total_qty' => $value['temp_total_qty']
                    );
                    $stmt->execute($data);
                }
            }

            
        
            $pack_sql =  "DELETE FROM tbl_package_box_details_temp 
                    WHERE session_id = '".$_SESSION['session_id']."'";
            $pack_temp_result = $conn->prepare($pack_sql);
            $pack_temp_result->execute();
        }
    }
    catch (Exception $e)
    {       
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
    $_SESSION['_msg'] = "DC succesfully Updated..!";
    header("location:dc_list.php"); 
    die();
}


$dc_date = date('d-m-Y');
$sql2 =  "DELETE FROM tbl_dc_details_temp";
            $result2 = $conn->prepare($sql2);
            $result2->execute();
            
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
        else
        {
            
            $sql1 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 1 AND temp_dc_id = ".$_REQUEST['dc_id']." ";
            $res1 = $conn->query($sql1);
            $boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
            if ($res1->rowCount()>0)
            {
                while ($obj1 = $res1->fetch())
                {
                    if($obj->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj1->temp_pack_box_no);
                        $result1 = array_unique($box_no, SORT_REGULAR);
                        $boxtype1 = sizeof($result1);
                    }
                }
            }
            
            $sql2 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 2 AND temp_dc_id = ".$_REQUEST['dc_id']." ";
            $res2 = $conn->query($sql2);
            
            if ($res2->rowCount()>0)
            {
                while ($obj2 = $res2->fetch())
                {
                    if($obj2->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj2->temp_pack_box_no);
                        $result2 = array_unique($box_no, SORT_REGULAR);
                        $boxtype2 = sizeof($result2);
                    }
                }
            }
            
            $sql3 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 3 AND temp_dc_id = ".$_REQUEST['dc_id']." ";
            $res3 = $conn->query($sql3);
            
            if ($res3->rowCount()>0)
            {
                while ($obj3 = $res3->fetch())
                {
                    if($obj3->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj3->temp_pack_box_no);
                        $result3 = array_unique($box_no, SORT_REGULAR);
                        $boxtype3 = sizeof($result3);
                    }
                }
            }
            
            $sql4 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 4 AND temp_dc_id = ".$_REQUEST['dc_id']." ";
            $res4 = $conn->query($sql4);
            
            if ($res4->rowCount()>0)
            {
                while ($obj4 = $res4->fetch())
                {
                    if($obj4->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj4->temp_pack_box_no);
                        $result4 = array_unique($box_no, SORT_REGULAR);
                        $boxtype4 = sizeof($result4);
                    }
                }
            }
        }
    
        $sql =  "DELETE FROM tbl_dc_details_temp 
                    WHERE session_id = '".$_SESSION['session_id']."'";
            $result = $conn->prepare($sql);
            $result->execute();
        $result1 = $conn->query("SELECT * FROM tbl_dc as a
                        LEFT JOIN tbl_dc_details as b ON a.dc_id = b.dc_id
                        WHERE a.dc_status = 1 AND b.dc_id =".$_REQUEST['dc_id']);   
        if ($result1->rowCount()>0)
        {
            $stmt = null;               
            $stmt = $conn->prepare("INSERT INTO tbl_dc_details_temp (temp_dc_details_id, temp_dc_item_id, temp_dc_qty, temp_dc_dispatch_qty, temp_bal_qty, temp_dc_unit, temp_box_id, temp_no_of_box, temp_dc_remarks, session_id, temp_date) VALUES (:temp_dc_details_id, :temp_dc_item_id, :temp_dc_qty, :temp_dc_dispatch_qty, :temp_bal_qty, :temp_dc_unit, :temp_box_id, :temp_no_of_box, :temp_dc_remarks, :session_id, :temp_date)");
            while($obj = $result1->fetchAll(PDO::FETCH_ASSOC))
            {
                foreach ($obj as $key => $value) 
                {
                    $data = array(
                        ':temp_dc_details_id' => $value['dc_details_id'],
                        ':temp_dc_item_id' => $value['dc_item_id'],
                        ':temp_dc_qty' => $value['dc_qty'],
                        ':temp_dc_dispatch_qty' => $value['dc_dispatch_qty'],
                        ':temp_bal_qty' => $value['bal_qty'],
                        ':temp_dc_unit' => $value['dc_unit'],
                        ':temp_box_id' => $value['box_id'],
                        ':temp_no_of_box' => $value['no_of_box'],
                        ':temp_dc_remarks' => $value['dc_remarks'],
                        ':session_id' => $_SESSION['session_id'],
                        ':temp_date' => date('Y-m-d')
                    );
                    $stmt->execute($data);
                    
                    //$pack_box_no1 = $dbconn->GetSingleReconrd("tbl_package_box_details","COUNT(DISTINCT pack_box_no)","box_id = 1 AND dc_id",$_REQUEST['dc_id']);
                    //Sample
                   
                    
                }
            }
            $result = $conn->query("SELECT * FROM tbl_dc_package_box"); 
            if ($result->rowCount()>0)
            {
                //$get = $result->fetch(PDO::FETCH_OBJ);    
                while ($obj = $result->fetch())
                {
                     
                }
            }
        }

        $temp_sql =  "DELETE FROM tbl_package_box_details_temp WHERE temp_dc_id = '".$_REQUEST['dc_id']."'";
        $del_result = $conn->prepare($temp_sql);
        $del_result->execute();

        $SQL = "SELECT * FROM tbl_package_box_details WHERE dc_id = '".$_REQUEST['dc_id']."'";
        $result = $conn->query($SQL);
        if ($result->rowCount() > 0)
        {   
            $stmt1 = $conn->prepare("INSERT INTO tbl_package_box_details_temp (temp_so_id, temp_dc_id, temp_item_id, temp_pack_box_no, temp_pack_item_qty, temp_box_id, temp_dispatch_qty, session_id, token , temp_total_qty) VALUES (:temp_so_id, :temp_dc_id, :temp_item_id, :temp_pack_box_no, :temp_pack_item_qty, :temp_box_id, :temp_dispatch_qty, :session_id, :token, :temp_total_qty)");
            $iSno=1;
            while($pa1 = $result->fetchAll(PDO::FETCH_ASSOC))
            {
                $_SESSION['token'] = md5(session_id() . time().$iSno); 
                foreach ($pa1 as $key1 => $value1) 
                {

                    $data1 = array(
                        ':temp_so_id' => $value1['so_id'],
                        ':temp_dc_id' => $value1['dc_id'],
                        ':temp_item_id' => $value1['item_id'],
                        ':temp_pack_box_no' => $value1['pack_box_no'],
                        ':temp_pack_item_qty' => $value1['pack_item_qty'],
                        ':temp_box_id' => $value1['box_id'],
                        ':temp_dispatch_qty' => $value1['dispatch_qty'],
                        ':session_id' => $_SESSION['session_id'],
                        ':token' => $_SESSION['token'],
                        ':temp_total_qty' => $value1['total_qty']
                    );
                    $stmt1->execute($data1);
                }
                $iSno++;
            }
        }
        
    }
    catch (Exception $e)
    {       
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
    $sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 1 AND temp_so_id = ".$so_id." ";
    $res = $conn->query($sql);
    $boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
    if ($res->rowCount()>0)
    {
        while ($obj = $res->fetch())
        {
            if($obj->temp_pack_box_no !='')
            {
                $box_no = explode(',', $obj->temp_pack_box_no);
                $result1 = array_unique($box_no, SORT_REGULAR);
                $boxtype1 = sizeof($result1);
            }
        }
    }
    
    $sql2 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 2 AND temp_so_id = ".$so_id." ";
    $res2 = $conn->query($sql2);
    
    if ($res2->rowCount()>0)
    {
        while ($obj2 = $res2->fetch())
        {
            if($obj2->temp_pack_box_no !='')
            {
                $box_no = explode(',', $obj2->temp_pack_box_no);
                $result2 = array_unique($box_no, SORT_REGULAR);
                $boxtype2 = sizeof($result2);
            }
        }
    }
    
    $sql3 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 3 AND temp_so_id = ".$so_id." ";
    $res3 = $conn->query($sql3);
    
    if ($res3->rowCount()>0)
    {
        while ($obj3 = $res3->fetch())
        {
            if($obj3->temp_pack_box_no !='')
            {
                $box_no = explode(',', $obj3->temp_pack_box_no);
                $result3 = array_unique($box_no, SORT_REGULAR);
                $boxtype3 = sizeof($result3);
            }
        }
    }
    
    $sql4 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 4 AND temp_so_id = ".$so_id." ";
    $res4 = $conn->query($sql4);
    
    if ($res4->rowCount()>0)
    {
        while ($obj4 = $res4->fetch())
        {
            if($obj4->temp_pack_box_no !='')
            {
                $box_no = explode(',', $obj4->temp_pack_box_no);
                $result4 = array_unique($box_no, SORT_REGULAR);
                $boxtype4 = sizeof($result4);
            }
        }
    }

    $sql =  "DELETE FROM tbl_dc_details_temp";
    $result = $conn->prepare($sql);
    $result->execute();
    $result1 = $conn->query("SELECT * FROM tbl_sales_order as a LEFT JOIN tbl_sales_order_details as b ON a.so_id = b.so_id WHERE b.so_id =".$_REQUEST['so_id']);   
    if ($result1->rowCount()>0)
    {
        $stmt = null;               
        $stmt = $conn->prepare("INSERT INTO tbl_dc_details_temp (temp_dc_item_id, temp_dc_qty, temp_dc_dispatch_qty, temp_bal_qty, temp_dc_unit, session_id, temp_date) VALUES (:temp_dc_item_id, :temp_dc_qty, :temp_dc_dispatch_qty, :temp_bal_qty, :temp_dc_unit, :session_id, :temp_date)");
        while($obj = $result1->fetchAll(PDO::FETCH_ASSOC))
        {
            foreach ($obj as $key => $value) 
            {
                $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);

                $avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$value['item_id']);
                
                
                if($_REQUEST['status'] == 'partial')
                {
                    $already_dispatch_qty = $conn->query("SELECT SUM(dc_dispatch_qty) as total_dispatch_qty FROM `tbl_dc_details` WHERE dc_id IN (SELECT dc_id FROM tbl_dc WHERE so_id='".$_REQUEST['so_id']."' AND dc_approve_status='1') AND dc_item_id='".$value['item_id']."'");
                    if ($already_dispatch_qty->rowCount()>0)
                    {
                        $obj1 = $already_dispatch_qty->fetch(PDO::FETCH_OBJ);

                        $dc_dispatch_qty = $value['so_qty'] - $obj1->total_dispatch_qty;
                    }
                }
                else
                {
                    if($avl_qty >= $value['so_qty'])
                    {
                        $dc_dispatch_qty = $value['so_qty'];
                    }
                    else
                    {
                        $dc_dispatch_qty = 0;
                        
                    }
                }

                $data = array(
                    ':temp_dc_item_id' => $value['item_id'],
                    ':temp_dc_qty' => $value['so_qty'],
                    ':temp_dc_dispatch_qty' => $dc_dispatch_qty,
                    ':temp_bal_qty' => 0,
                    ':temp_dc_unit' => $value['so_unit'],
                    ':session_id' => $_SESSION['session_id'],
                    ':temp_date' => date('Y-m-d')
                );
                $stmt->execute($data);
            }
        }
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

                                                            $get_dc_dets =  $conn->query("SELECT * FROM tbl_dc_details_temp");
                                                            if ($get_dc_dets->rowCount() > 0) 
                                                            {
                                                                $iSno=1;
                                                                while ($obj = $get_dc_dets->fetch(PDO::FETCH_OBJ)) 
                                                                {
                                                                    $_SESSION['token'] = md5(session_id() . time().$iSno);
                                                                    if($so_id>0)
                                                                    {
                                                                        $already_dispatch_qty = $conn->query("SELECT SUM(dc_dispatch_qty) as total_dispatch_qty FROM `tbl_dc_details` WHERE dc_id IN (SELECT dc_id FROM tbl_dc WHERE so_id='".$so_id."' AND dc_approve_status='1') AND dc_item_id='".$obj->temp_dc_item_id."'");
                                                                    }
                                                                    else
                                                                    {
                                                                        $already_dispatch_qty='';
                                                                    }
                                                                    
                                                                    if ($already_dispatch_qty->rowCount()>0)
                                                                    {
                                                                        $obj1 = $already_dispatch_qty->fetch(PDO::FETCH_OBJ);
                                                                    }

                                                                    $temp_item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id",$obj->temp_dc_item_id);
                                                                    $temp_item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id",$obj->temp_dc_item_id);
                                                                    $item_type = $dbconn->GetSingleReconrd("tbl_item_details","item_type","item_status = '1' AND item_id",$obj->temp_dc_item_id);

                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);

                                                                    $temp_avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$obj->temp_dc_item_id);

                                                                    //$temp_avl_qty = $dbconn->GetSingleReconrd("tbl_item_details","item_curr_stock","item_status = '1' AND item_id",$obj->temp_dc_item_id);
                                                                    
                                                                   // $pack_box_no = $dbconn->GetSingleReconrd("tbl_package_box_details","pack_box_no"," item_id",$obj->temp_dc_item_id); 
                                                                    if($obj->temp_dc_qty == $obj1->total_dispatch_qty)
                                                                    {
                                                                        echo '<tr>
                                                                            <td>'.$iSno.'</td>
                                                                            <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>

                                                                            <td style = "text-align:center;">'.$obj->temp_dc_unit.'</td>

                                                                            <td style = "text-align:center;">'.$temp_avl_qty.'<input type="hidden" name="temp_avl_qty" class="temp_avl_qty" value="'.$temp_avl_qty.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->temp_dc_qty.'<input type="hidden" name="hide_dc_qty" class="hide_dc_qty" value="'.$obj->temp_dc_qty.'"></td>

                                                                            <td style = "text-align:center;">'.$obj1->total_dispatch_qty.'<input type="hidden" name="hide_tot_dispatch_qty" class="hide_tot_dispatch_qty" value="'.$obj1->total_dispatch_qty.'"></td>

                                                                            <td><input type="text" name="dc_dispatch_qty[]" id="dc_dispatch_qty" readonly style="width:75px;" tabindex="-1" class="form-control validate[required] dc_dispatch_qty" value="'.$obj->temp_dc_dispatch_qty.'"><input type="hidden" name="hide_dispatch_qty" class="hide_dispatch_qty" value="'.$obj->temp_dc_dispatch_qty.'"></td>

                                                                            <td><input type="text" readonly name="bal_qty[]" tabindex="-1" id="bal_qty" class="form-control bal_qty" style="width:75px;" value="'.$obj->temp_bal_qty.'"></td>

                                                                            <td>
                                                                                <select name="box_id[]" 
                                                                                class="select box_id"
                                                                                id= "box_id_'.$iSno.'"
                                                                                >

                                                                                    <option value="0">Select Box Type</option>
                                                                                        
                                                                                </select>
                                                                            </td>

                                                                            <td><div class="input-append"><input type="text" readonly tabindex="-1" style="width:85px;" name="no_of_box[]" class="form-control no_of_box"  id= "no_of_box_'.$iSno.'" onkeypress="return isNumberKey_With_Dot(event)" value="'.$obj->temp_no_of_box.'" ></div></td>

                                                                            <td><input type="text" readonly tabindex="-1" class="form-control" name="dc_remarks[]" id="dc_remarks" value="'.$obj->temp_dc_remarks.'"></td>
                                                                        </tr>';
                                                                    }
                                                                    else
                                                                    {
                                                                        
                                                                        echo '<tr>
                                                                        <td>'.$iSno.'</td>
                                                                        <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>
                                                                        <td style = "text-align:center;">'.$obj->temp_dc_unit.'</td>

                                                                        <td style = "text-align:center;">'.$temp_avl_qty.'<input type="hidden" name="temp_avl_qty" class="temp_avl_qty" value="'.$temp_avl_qty.'"></td>

                                                                        
                                                                        <td style = "text-align:center;">'.$obj->temp_dc_qty.'<input type="hidden" name="hide_dc_qty" class="hide_dc_qty" value="'.$obj->temp_dc_qty.'"></td>

                                                                        <td style = "text-align:center;">'.$obj1->total_dispatch_qty.'<input type="hidden" name="hide_tot_dispatch_qty" class="hide_tot_dispatch_qty" value="'.$obj1->total_dispatch_qty.'"></td>

                                                                        <td><input type="text" name="dc_dispatch_qty[]" id="dc_dispatch_qty" onKeyPress="return isNumberKey(event)" style="width:75px;" class="form-control validate[required] dc_dispatch_qty" value="'.$obj->temp_dc_dispatch_qty.'"><input type="hidden" name="hide_dispatch_qty" class="hide_dispatch_qty" value="'.$obj->temp_dc_dispatch_qty.'"></td>

                                                                        <td><input type="text" readonly name="bal_qty[]" tabindex="-1" id="bal_qty" class="form-control bal_qty" style="width:75px;" value="'.$obj->temp_bal_qty.'"></td>


                                                                        <td>
                                                                            <select name="box_id[]" 
                                                                            class="select box_id"
                                                                            id= "box_id_'.$iSno.'"
                                                                            >

                                                                                <option value="0">Select Box Type</option>';
                                                                                    echo $dbconn->fnFillComboFromTable_Where("box_id","box_name","tbl_dc_package_box","box_id"," WHERE box_status = '1'");
                                                                            echo '</select>
                                                                            <script>document.thisForm.box_id_'.$iSno.'.value="'.$obj->temp_box_id.'";
                                                                            </script>';
                                                                        echo '</td>    

                                                                        <td><div class="input-append"><input type="text" name="no_of_box[]" onKeyPress="return isNumberKey(event)" style="width:85px;" maxlength="3" class="no_of_box" id= "no_of_box_'.$iSno.'" onkeypress="return isNumberKey_With_Dot(event)" value="'.$obj->temp_no_of_box.'" > <a  data-toggle="modal" data-target="#modalDCPack" href="" data-id="'.$obj->temp_dc_id.'" data-popup="tooltip" title="" class="btn btn-success fancybox">Box</a> 
                                                                        
                                                                       


                                                                       <td><input type="text"  class="form-control" tabindex="-1" name="dc_remarks[]" id="dc_remarks" value="' . $obj->temp_dc_remarks . '"></td>

                                                                       <input type="hidden" name="hide_item_id" class="hide_item_id" value="'.$obj->temp_dc_item_id.'">
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


        $('#modalDCPack').on('show.bs.modal', function(e) {
            // alert(dc_temp_id);
            var dc_temp_id = $(e.relatedTarget).data('id');
            var dispatch_qty = $(e.relatedTarget).closest('tr').find('.dc_dispatch_qty').val();
            var box_count = $(e.relatedTarget).closest('tr').find('.no_of_box').val();
            var item_id = $(e.relatedTarget).closest('tr').find('.hide_item_id').val();
            var token = $(e.relatedTarget).closest('tr').find('.hide_token').val();
            var box_id = $(e.relatedTarget).closest('tr').find('.box_id').val();
            var so_id = $("#so_id").val();
           var dc_id = $("#txtHid").val();
            
             
            if (dc_temp_id != '') {
                $.ajax({
                    type: 'post',
                    url: 'inc/cis_ajax/jquery_modal_dc_pack_dets.php',
                    data: {
                        'dc_temp_id': dc_temp_id,
                        'dispatch_qty':dispatch_qty,
                        'box_count': box_count,
                        'item_id':item_id,
                        'token':token,
                        'box_id': box_id,
                        'so_id': so_id,
                        'dc_id': dc_id
                        
                    },
                    success: function(data) {
                        // alert(data);
                        string = data.split("~");
                        $('#m_sales_rec').html(string[0]);
                        $('#m_sales_code').html(string[1]);
                    }
                });
            }

        });
    });


   
</script>