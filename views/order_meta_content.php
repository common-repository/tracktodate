<?php
// echo "<pre>";
// print_r($post);
// $order_id = (int) $post->ID;
$status = array('class' => '', 'style' => 'display:none', 'msg' => '');
$tracking_status = array('status' => 'failed', 'msg' => 'Tracking Not Initiated');
$updated_time = '-';
$plugin_key = get_option('tracktodate_plugin_key');
$response = get_option('ttd_ins_trk_response');

// var_dump($response);
// die();
$response = json_decode($response, true);
$display  = '';
if (isset($response['status']) && $response['status'] == 'error') {
  $display = (string) $response['msg'];
}
if (isset($response['status']) && $response['status'] == 'ok') {
  // $display = (string) $response['msg'];
  $tracking_status['msg'] = $response['msg'];
  update_option($post->ID.'_ttd_track_ui', '1');
}
// update_option('ttd_ins_trk_response','');
delete_option('ttd_ins_trk_response');
// Text field
$tracking_number = get_post_meta($post->ID, '_tracking_number', true);
$is_initiated = get_option($post->ID.'_ttd_track_ui');
if ($is_initiated) {
  echo wp_kses('<iframe class="responsive-iframe" src="http://tracktodate.loc/tracking/?no='.$tracking_number.'" frameborder="0" style="overflow:hidden;overflow-x:hidden;overflow-y:hidden;width:100%;top:0px;left:0px;right:0px;bottom:0px;height:70vh" width="100%"></iframe>','post');
    echo wp_kses('<hr>','post');
    echo wp_kses('<p><button type="submit" class ="button button-primary" value="ttk_reset_tracking_number" name="ttk_reset_tracking_number">Reset Tracking</button></p>','post');
    echo wp_kses('<small style="color:green">Resetting Tracking will not delete the number from Tracktodate<small>','post');
} else {
  echo '<p>' . __('Order / Ref Number', 'tracktodate') . ' <br>
  <input type="text" style="width:25%" id="order_number" name="order_number" value="' . esc_attr($post->ID) . '"></p>';
  echo '<p>' . __('Tracking number', 'tracktodate') . '<br>
  <input type="text" style="width:25%" id="tracking_number" name="tracking_number" value="' . esc_attr($tracking_number) . '"></p>';
  echo '<p>' . __('Shipping Carrier', 'tracktodate') . '<br>
  <select style="width:25%" id="carrier_select" name="ttd_carrier_select">
  <option value="none">None</option>
  <option value="ups">UPS</option>
  <option value="dhl">DHL</option>
  <option value="fedex">FEDEX</option>
  <option value="cp">Canada Post</option>
  <option value="purolator">Purolator</option>
  <option value="aramex">Aramex</option>
  </select></p>';
  echo wp_kses('<p><button type="submit" class ="button button-primary" value="update" name="insert_track">Insert</button></p>','post');
  if ($display != '') {
    echo wp_kses('<p style="color:red">' . $display . '<p>','post');
  }
  echo wp_kses('<hr>','post');
  // echo '<button id="tracknow_btn" class="button button-primary">Track Now</button></p>';
  if ($tracking_status['status'] == 'failed') {
    $trk_notify = ' <b><span style="color:red">' . $tracking_status['msg'] . '</span></b>';
  }
  echo '<br><p>' . __('Tracking Status : ', 'tracktodate') . wp_kses($trk_notify,'post') . '</p>';
  echo '<p>' . __('Last Updated : ', 'tracktodate') . wp_kses($updated_time,'post') . '</p>';
}


// echo '<!-- Trigger/Open The Modal -->
// <!-- The Modal -->
// <div id="myModal" class="modal">

//   <!-- Modal content -->
//   <div class="modal-content">
//     <span class="close">&times;</span>
//     <p>Some text in the Modal..</p>
//   </div>

// </div>';
?>
<!-- <style>
  /* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 20px;
  border: 1px solid #888;
  width: 45%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}
</style> -->
<script>
  jQuery(document).ready(function() {
    // jQuery("#carrier_select").click(function() {
    //     //for fetching configured account
    //     var plugin_key = <?php echo __('plugin_key','tracktodate'); ?>;
    //     console.log("SSS");
    //     jQuery.ajax({

    //               // url: 'https://app.tracktodate.com/json-api/get_service_list.php', // <-- point to server-side PHP script 
    //               url: 'http://tracktodate.loc/json-api/get_service_list.php', // <-- point to server-side PHP script 
    //               dataType: 'json',
    //               data: {get:'carrier_list','integrationKey':plugin_key},        
    //               type: 'post',
    //               // crossDomain: true,
    //               // headers: {'Access-Control-Allow-Origin': 'https://app.tracktodate.com'},
    //               success: function(res){
    //                   if(res.status == 'ok'){
    //                     // window.location.reload();
    //                     var carriers = res;
    //                     console.log(carrier);
    //                     // jQuery('#carrier_select').html(carriers);
    //                   }else{

    //                   }

    //               }
    //           });

    // });
  });
  // Get the modal
  var modal = document.getElementById("myModal");

  // Get the button that opens the modal
  var btn = document.getElementById("tracknow_btn");

  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close")[0];

  // When the user clicks on the button, open the modal
  btn.onclick = function() {
    modal.style.display = "block";
  }

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // When the user clicks anywhere outside of the modal, close it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }

    if (event.target.id == 'tracknow_btn') {
      event.preventDefault();
    }

  }
</script>