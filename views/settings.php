<?php
$status = array('class' => '', 'style' => 'display:none', 'msg' => '');

if (isset($_POST['login_connect'])) {
  $plugin_key = sanitize_text_field(isset($_POST['plugin_key']) ? sanitize_text_field($_POST['plugin_key']) : '');
  $email = sanitize_text_field(isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '');
  $psw = sanitize_text_field(isset($_POST['psw']) ? sanitize_text_field($_POST['psw']) : '');
  $store_url = get_site_url();
  
  if ($plugin_key) {
    $link_site_url = "http://tracktodate.loc/link_site.php"; //Test URL
    // $link_site_url = "https://app.tracktodate.com/link_site.php";

    $link_site_response = wp_remote_post(
      $link_site_url,
      array(
        'method'      => 'POST',
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'        => json_encode(array('plugin_key' => $plugin_key, 'url' => $store_url, 'email' => $email, 'psw' => $psw)),
      )
    );
    $link_site_response = (is_array($link_site_response) && isset($link_site_response['body'])) ? json_decode($link_site_response['body'], true) : array();
  }
  if (isset($link_site_response['status']) && $link_site_response['status'] == 'true') {
    update_option('tracktodate_plugin_key', $link_site_response['plugin_key']);
  } else {
    $status['class'] = "-danger";
    $status['style'] = 'display:block';
    $status['msg'] = isset($link_site_response['message']) && !empty($link_site_response['message']) ? $link_site_response['message'] : '';
  }

  // echo "ss";
  // print_r($link_site_response);
  // die();
}

// LOGOUT
// delete_option('tracktodate_plugin_key');


if (isset($_POST['connect_store'])) {
  $email = sanitize_text_field(isset($_POST['email']) ? $_POST['email'] : '');
  $psw = sanitize_text_field(isset($_POST['psw']) ? $_POST['psw'] : '');
  $store_name = sanitize_text_field(isset($_POST['store_name']) ? $_POST['store_name'] : '');
  $store_url = get_site_url();
  $nounce = wp_create_nonce($store_url);
  set_transient('tracktodate_transient_temp', $nounce, 300);
  $traans = get_transient('tracktodate_transient_temp');
  // print_r($traans);
  // die();
  $is_valid_email = filter_var($email, FILTER_VALIDATE_EMAIL);
  $is_valid_psw = true;
  if (!$is_valid_email) {
    $status['style'] = 'display:block';
    $status['msg'] = 'Please Enter a Valid Email.';
  }
  if(strlen($psw) < 8){
    $is_valid_psw = false;
    $status['style'] = 'display:block';
    $status['msg'] = 'Password must be atleast 8 characters';
  }
  if ($email && $psw && $store_name && $is_valid_email && $is_valid_psw) {
    //$link_site_url = "https://app.tracktodate.com/link_site.php";
    $link_site_url = "http://tracktodate.loc/link_site.php";

    $link_site_response = wp_remote_post(
      $link_site_url,
      array(
        'method'      => 'POST',
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'        => json_encode(array('email' => $email, 'psw' => $psw, 'store_name' => $store_name, 'url' => $store_url, 'transient' => $traans, 'site_name' => get_bloginfo('name'))),
      )
    );
  
    $link_site_response = (is_array($link_site_response) && isset($link_site_response['body'])) ? json_decode($link_site_response['body'], true) : array();

    if ($link_site_response) {
      if ($link_site_response['status'] == 'true' && isset($link_site_response['plugin_key'])) {
        update_option('tracktodate_plugin_key', $link_site_response['plugin_key']);
      } else {
        $status = array('class' => 'alert-danger', 'style' => 'display:block', 'msg' => 'Error:' . $link_site_response['message']);
      }
    }else{
      $status = array('class' => 'alert-danger', 'style' => 'display:block', 'msg' => 'Error connecting to site please try again later');
    }
  }
}

$stock_key = get_option('tracktodate_plugin_key');
// $api_key = get_option('tracktodate_api_key');
if (!$stock_key) {

?>
  <style>
    a {
      text-decoration: none;
    }

    .hit_tracktodate_card {
      font-family: sans-serif;
      width: 500px;
      margin-left: auto;
      margin-right: auto;
      margin-top: 3em;
      margin-bottom: 3em;
      border-radius: 10px;
      background-color: #ffff;
      padding: 1.8rem;
      box-shadow: 2px 5px 20px rgba(0, 0, 0, 0.1);
    }

    .hit_tracktodate_title {
      text-align: center;
      font-weight: bold;
      margin: 0;
    }

    .hit_tracktodate_subtitle {
      text-align: center;
      font-weight: bold;
    }

    .hit_tracktodate_btn-text {
      margin: 0;
    }

    input[type="text"],
    input[type="password"] {
      padding: 15px 20px;
      margin-top: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }

    .hit_tracktodate_cta-btn {
      background-color: rgb(69, 69, 185);
      color: white;
      padding: 18px 20px;
      margin-top: 10px;
      margin-bottom: 20px;
      width: 100%;
      border-radius: 10px;
      border: none;
      cursor: pointer;
    }

    .hit_tracktodate_email-login {
      display: flex;
      flex-direction: column;
    }

    .hit_tracktodate_email-login label {
      color: rgb(170 166 166);
    }

    div#alert {
      background-color: #f8d7da;
      padding: 9px;
      text-align: center;
    }
  </style>
  <?php
  if (!isset($_GET['login'])) {
  ?>
    <!-- Sign up -->
    <div class="hit_tracktodate_card">
      <form method="post">
        <h1 class="hit_tracktodate_title"> TrackToDate </h1>
        <p class="hit_tracktodate_subtitle">Already have TrackToDate account? <a href="./admin.php?page=hit-tracktodate-setup&login=1"> Login</a></p>
        <div class="hit_tracktodate_email-login">
          <label for="email"> <b>Email</b></label>
          <input type="text" placeholder="Enter Email" name="email" required>
          <label for="psw"><b>Password</b></label>
          <input type="password" placeholder="Enter Password" name="psw" required>
          <label for="psw"><b>Store Name</b></label>
          <input type="text" placeholder="Enter Store Name" name="store_name" required>
        </div>
        <?php
        echo "<div id='alert' class='mt-4 alert" . esc_attr($status['class']) . "' style='" . esc_attr($status['style']) . "' >" . esc_attr($status['msg']) . "</div>";
        ?>
        <button type="submit" class="hit_tracktodate_cta-btn" name="connect_store">Create & Connect</button>
      </form>
    </div>

  <?php
  } else {
  ?>
    <!-- login -->
    <div class="hit_tracktodate_card">
      <form method="post">
        <h1 class="hit_tracktodate_title"> TrackToDate </h1>
        <p class="hit_tracktodate_subtitle">Don't have an account? <a href="./admin.php?page=hit-tracktodate-setup"> sign Up</a></p>
        <div class="hit_tracktodate_email-login">
          <label for="email"> <b>Email</b></label>
          <input type="text" placeholder="Enter Email" name="email" required>
          <label for="psw"><b>Password</b></label>
          <input type="password" placeholder="Enter Password" name="psw" required>
          <label for="plugin_key"> <b>Plugin Key / Integration Key</b></label>
          <input type="text" placeholder="Enter Pluginkey" name="plugin_key" required>
          <p class="hit_tracktodate_subtitle"><a target="_blank" href="https://app.tracktodate.com"> click here</a> To view Your existing plugin key</p>
        </div>
        <?php
        echo "<div id='alert' class='mt-4 alert" . esc_attr($status['class']) . "' style='" . esc_attr($status['style']) . "' >" . esc_attr($status['msg']) . "</div>";
        ?>
        <input type="submit" class="hit_tracktodate_cta-btn" value="Login" name="login_connect">
        <!-- Connect</button> -->
      </form>
    </div>
  <?php
  }
} else {
  // print_r($stock_key);
  // $embed_url = 'http://tracktodate.loc/'; //TEST URL
  // $embed_url = 'http://app.tracktodate.com/';
  ?>
  
  <!-- html -->
  <iframe class="responsive-iframe" src="https://app.tracktodate.com/embed.php?key=<?php echo esc_html($stock_key); ?>" frameborder="0" style="overflow:hidden;overflow-x:hidden;overflow-y:hidden;width:100%;top:0px;left:0px;right:0px;bottom:0px;height:100vh" width="100%"></iframe>
  <!-- html End -->
<?php
}
?>