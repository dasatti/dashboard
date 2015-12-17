<?php 
include('../include/siteconfig.inc.php');


include('../include/sitefunction.php');


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Neon Admin Panel" />
<meta name="author" content="" />
<title><?php echo ADMIN_TITLE?></title>

<?php include('script_include.php');?>
</head>

<body class="page-body login-page login-form-fall" data-url="http://neon.dev">

<!-- This is needed when you send requests via Ajax --><script type="text/javascript">
var baseurl = '';
</script>
<div class="login-container">
  <div class="login-header login-caret">
    <div class="login-content"> <a href="../index.html" class="logo"> <img src="assets/images/logo@2x.png" width="210" alt="" /> </a>
      <p class="description">CLIENT LOGIN</p>
      
      <!-- progress bar indicator -->
      <div class="login-progressbar-indicator">
        <h3>43%</h3>
        <span>logging in...</span> </div>
    </div>
  </div>
  <div class="login-progressbar">
    <div></div>
  </div>
  <div class="login-form">
    <div class="login-content">
      <div class="form-login-error">
        <h3>Invalid login</h3>
        <p>Invalid username or password.
       
        </p>
      </div>
       <?php
	if(isset($_GET['msg'])){
		echo '<div class="form-login-error" style="display:block;">'.base64_decode($_GET['msg']).'</div>';
	}
	?>
      <form name="form_login" id="form_login" role="form" method="post" action="">
      
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon"> <i class="entypo-user"></i> </div>
            <input type="text" class="form-control" name="username" id="username" placeholder="Username" autocomplete="off" />
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon"> <i class="entypo-key"></i> </div>
            <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off" />
          </div>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-block btn-login"> <i class="entypo-login"></i> Login </button>
        </div>
        
      </form>

    </div>
  </div>
</div>

<!-- Bottom Scripts --> 
<script src="<?php echo SURL?>assets/js/gsap/main-gsap.js"></script> 
<script src="<?php echo SURL?>assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script> 
<script src="<?php echo SURL?>assets/js/bootstrap.js"></script> 
<script src="<?php echo SURL?>assets/js/joinable.js"></script> 
<script src="<?php echo SURL?>assets/js/resizeable.js"></script> 
<script src="<?php echo SURL?>assets/js/neon-api.js"></script> 
<script src="<?php echo SURL?>assets/js/jquery.validate.min.js"></script> 
<script src="<?php echo SURL?>assets/js/neon-login.js"></script> 
<script src="<?php echo SURL?>assets/js/neon-custom.js"></script> 
<script src="<?php echo SURL?>assets/js/neon-demo.js"></script>
</body>

	
</html>