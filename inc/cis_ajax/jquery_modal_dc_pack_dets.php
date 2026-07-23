<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$dc_temp_id=$_POST['dc_temp_id'];
$box_count = $_POST['box_count'];
$dispatch_qty = $_POST['dispatch_qty'];
$item_id = $_POST['item_id'];
$so_id = $_POST['so_id'];
$dc_id = $_POST['dc_id'];
$token = $_POST['token'];
$box_id = $_POST['box_id'];

if($dc_id > 0)
{
    $temp_token = $dbconn->GetSingleReconrd("tbl_package_box_details_temp","token","temp_dc_id = '".$dc_id."' AND temp_item_id",$item_id);
    if($token != $temp_token)
    {
        $temp_sql =  "DELETE FROM tbl_package_box_details_temp WHERE temp_dc_id = '".$dc_id."' AND temp_item_id = '".$item_id."'";
        $del_result = $conn->prepare($temp_sql);
        $del_result->execute();

        $SQL = "SELECT * FROM tbl_package_box_details WHERE dc_id = '".$dc_id."' AND item_id = '".$item_id."'";
        $result = $conn->query($SQL);
                                        
        if ($result->rowCount() > 0)
        {   
            $stmt = $conn->prepare("INSERT INTO tbl_package_box_details_temp (temp_so_id, temp_dc_id, temp_item_id, temp_pack_box_no, temp_pack_item_qty, temp_box_id, temp_dispatch_qty, session_id, token , temp_total_qty) VALUES (:temp_so_id, :temp_dc_id, :temp_item_id, :temp_pack_box_no, :temp_pack_item_qty, :temp_box_id, :temp_dispatch_qty, :session_id, :token, :temp_total_qty)");
            while($pa = $result->fetchAll(PDO::FETCH_ASSOC))
            {
                foreach ($pa as $key => $value) 
                {
                    $data = array(
                        ':temp_so_id' => $value['so_id'],
                        ':temp_dc_id' => $value['dc_id'],
                        ':temp_item_id' => $value['item_id'],
                        ':temp_pack_box_no' => $value['pack_box_no'],
                        ':temp_pack_item_qty' => $value['pack_item_qty'],
                        ':temp_box_id' => $value['box_id'],
                        ':temp_dispatch_qty' => $value['dispatch_qty'],
                        ':session_id' => $_SESSION['session_id'],
                        ':token' => $token,
                        ':temp_total_qty' => $value['total_qty']
                    );
                    $stmt->execute($data);
                }
            }
            // $pa = $result->fetch();

                
            $sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 1 AND temp_dc_id = ".$dc_id." ";
            $res = $conn->query($sql);
            $boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
            if ($res->rowCount()>0)
            {
                while ($obj = $res->fetch()){
                    if($obj->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj->temp_pack_box_no);
                        $result1 = array_unique($box_no, SORT_REGULAR);
                        $boxtype1 = sizeof($result1);
                    }
                }
            }
            
            $sql2 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 2 AND temp_dc_id = ".$dc_id." ";
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
            
            $sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 3 AND temp_dc_id = ".$dc_id." ";
            $res3 = $conn->query($sql);
            
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
            
            $sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 4 AND temp_dc_id = ".$dc_id." ";
            $res4 = $conn->query($sql);
            
            if ($res4->rowCount()>0)
            {
                while ($obj4 = $res4->fetch())
                {
                    if($obj4->temp_pack_box_no !='')
                    {
                        $box_no = explode(',', $obj4->temp_pack_box_no);
                        $result4 = array_unique($box_no, SORT_REGULAR);
                        $boxtype3 = sizeof($result4);
                    }
                }
            }
        }
    }
}

if($item_id > 0)
{
    if($dc_id > 0)
    {

        $select_temp = "SELECT * FROM tbl_package_box_details_temp WHERE temp_item_id = '".$item_id."' AND temp_dc_id='".$dc_id."'";
        $select = $conn->query($select_temp);
                                        
        if ($select->rowCount() > 0)
        {   
            $pa = $select->fetch();
            $pack_box_no = explode(', ', $pa->temp_pack_box_no);
            $pack_item_qty = explode(', ', $pa->temp_pack_item_qty);
        }
    }
    else
    {
        $select_temp = "SELECT * FROM tbl_package_box_details_temp WHERE temp_item_id = '".$item_id."' AND token = '".$token."'";
        $select = $conn->query($select_temp);
                                        
        if ($select->rowCount() > 0)
        {   
            $pa = $select->fetch();
            $pack_box_no = explode(', ', $pa->temp_pack_box_no);
            $pack_item_qty = explode(', ', $pa->temp_pack_item_qty);
        }   
    }
    
}

$item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id",$item_id);
$item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id",$item_id);


$header=   '<div class="row">
            <div class="col-lg-6" style=" text-align: left;">
                <span style="font-size: 14px; font-weight: bold;">' . $item_name . ' - '.$item_code.'</span>
            </div>
            <div class="col-lg-6" style=" text-align: right;">
                <span style="font-size: 14px; font-weight: bold;" id="" vlaue="">Dispatch Qty : ' . $dispatch_qty . ' </span>
            </div>
        </div>';

$modal_dets =' <input type="hidden" name="box_count" id="box_count" value="'.$box_count.'">
        <input type="hidden" name="so_id" id="so_id" value="'.$so_id.'">
        <input type="hidden" name="dc_id" id="dc_id" value="'.$dc_id.'">
        <input type="hidden" name="item_id" id="item_id" value="'.$item_id.'">
        <input type="hidden" name="token" id="token" value="'.$token.'">
        <input type="hidden" name="dispatch_qty" id="dispatch_qty" value="'.$dispatch_qty.'">
        <input type="hidden" name="box_id" id="box_id" value="'.$box_id.'">

      
        
        
    
        <form name="thisForm" class="form-horizontal" method="post" action ="jquery_modal_dc_add_dts.php" onSubmit="return fnValidate();">';
        for ($i=1; $i<=$box_count; $i++) 
        {
            $modal_dets .= '<div class="row pb-2">
                    <div class="col-lg-6" style=" text-align: center;">
                        <input type="text" class="form-control" onKeyPress="return isNumberKey(event)"  name="pack_box_no[]" id="pack_box_no'.$i.'" value="'.$pack_box_no[$i-1].'" />	
                    </div>
                    <div class="col-lg-6" style=" text-align: center;">
                        <input type="text" class="form-control qty"  onKeyPress="return isNumberKey(event)"  name="pack_item_qty[]" id="pack_item_qty'.$i.'" value="'.$pack_item_qty[$i-1].'" />
                    </div>              
                </div>';
        }
$modal_dets .= '<div class="row">
            <div  class="col-lg-6" style="font-size: 14px; font-weight: bold; text-align: center;">Total</div>
            <div  class="col-lg-6" style="font-size: 14px; font-weight: bold; text-align: center;">
                <input readonly type="text" id="total" class="form-control font-weight-bold"  name="total" value="'.$pa->temp_total_qty.'" />
            </div>
        </div>
        <hr>
        <div class="col-lg-12" style="font-size: 14px; font-weight: bold; text-align: center;">
            <input class="btn btn-custom" type="button" name="SAVE" id="SAVE" value="SAVE">
        </div>        
</form>';

echo $header.'~'.$modal_dets;

?>

<script type="text/javascript">
    $('.qty').change(function() {
        get_total();
    });

    function get_total() {
        var sum = 0;
        $(".qty").each(function() {
            if ($(this).val() == '') {
                $(this).val(0);
            }
            sum += parseFloat($(this).val());
        });
        $("#total").val(sum);

        var dispatch_qty = $("#dispatch_qty").val();
        if (sum > dispatch_qty) {
            alert("Qty must be less equal to dispatch qty");
            $("#total").val('');
            return false;
        }
    }

    $('#SAVE').click(function(){
       
        var box_count = $("#box_count").val();
        var so_id = $("#so_id").val();
        var dc_id = $("#dc_id").val();
        var item_id = $("#item_id").val();
        var token = $("#token").val();
        var total = $("#total").val();
        var dispatch_qty = $("#dispatch_qty").val();
        var box_id = $("#box_id").val();

        var box_no = [];
        var qty = [];
        for(i=1; i<=box_count; i++)
        {
            var boxno_empty = $("#pack_box_no"+i).val();
            var boxqty_empty = $("#pack_item_qty"+i).val();

            if(boxno_empty == '')
            {
                alert("One or more Box Number Missing..");
                return false;
            }
            else
            {
                box_no[box_no.length] = $("#pack_box_no"+i).val();
            }

            if(boxqty_empty == '')
            {
                alert("One or more Box Qty Missing..");
                return false;   
            }
            else
            {
                qty[qty.length] = $("#pack_item_qty"+i).val();
            }
            
        }
        
        
        if(total != dispatch_qty)
        {
            alert("Total qty and dispatch qty must be same");
            return false;
        }

         $.ajax({
            type: "POST",
            url: "add_packing_box.php",
            data: {
                "box_no":box_no,
                "qty":qty,
                "so_id":so_id,
                "dc_id":dc_id,
                "item_id":item_id,
                "token":token,
                "total":total,
                "dispatch_qty":dispatch_qty,
                "box_id":box_id,
                "mode": 'save'
            }
        }).done(function(msg) {
            var res = $.trim(msg);
            var res1 = res.split("-");
            //alert(res);
            if(msg>0)
            {
                $("#modalDCPack .close").click();
            }
            else
            {
                $("#corrugated_box").val($.trim(res1[1]));
                $("#wooden_box").val($.trim(res1[2]));
                $("#gunny_bags").val($.trim(res1[3]));
                $("#poly_bags").val($.trim(res1[4]));

                $("#modalDCPack .close").click();
            }
            //$("#modalDCPack .close").click();
        });
        
       
    });
    // $('#SAVE').click(function() {
    //     var no_of_box = $("#no_of_box").val();
    //     var total_qty = $("#total").val();
    //     var dispatch_qty = $("#dispatch_qty").val();

    //     //var pack_item_qty = $("#pack_item_qty").val();
    //     var boxno_empty,boxnos=''; var boxqty_empty=''; var boxqtys='';
    //     //alert(no_of_box);
    

    //     for (i = 1; i <= no_of_box; i++) 
    //     {
    //         boxno_empty = $("#pack_box_no" + i).val();
    //         boxqty_empty = $("#pack_item_qty" + i).val();
           


    //         if (boxno_empty == '') 
    //         {    
    //             alert("One or more Box Number Missing..");             
    //             $("#pack_box_no" + i).focus();
    //             return false;
                
    //         }
    //         else
    //         {

    //             if (boxqty_empty == '') 
    //             {
    //                 alert("One or more Box Quantity Missing..");
    //                 $("#pack_item_qty"+i).focus();
    //                 return false;
    //             }
    //         }
    		

         

    //         boxnos += parseInt($("#pack_box_no" + i).val())+ "," ;
    //         boxqtys += parseInt($("#pack_item_qty" + i).val())+ ",";
    //     }

    //     if (total_qty != dispatch_qty) {
    //         alert("Total qty and dispatch qty must be same");
    //         return false;
    //     }
    //     var so_id = $('#so_id').val();
    //     var dets_id = $('#dets_id').val();
    //     var box_id = $('#box_id').val();

       
    //     // alert(boxnos + " \n" + boxqtys+" \n");
    //     alert(boxnos);
    //     alert(boxqtys);

    //     $.ajax({
    //         type: "POST",
    //         url: "temp_pack_box_save.php",
    //         data: {
    //             "so_id": so_id,
    //             "dets_id": dets_id,
    //             "pack_box_no": boxnos,
    //             "pack_item_qty": boxqtys,
    //             "box_id": box_id,
    //             "dispatch_qty": dispatch_qty,
    //             "total": total_qty,
    //             "mode": 'save'
    //         }
    //     }).done(function(msg) {
    //         $("#modalDcDets .close").click();
    //     });

    // });
</script>