 <div class="row"> 
      
      <!-- Profile Info and Notifications -->
      <div class="col-md-6 col-sm-8 clearfix">
        <ul class="user-info pull-left pull-none-xsm">
          
          <!-- Profile Info -->
          <li class="profile-info dropdown"><!-- add class "pull-right" if you want to place this from right class="img-circle" --> 
            <?php
                $profile_image = (empty($_SESSION['lm_auth']['image']))?'profile_default.png':$_SESSION['lm_auth']['image'];
                
            ?>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <div class="img-circle user-image-thumb">
            <img src="<?php echo $profile_image; ?>" alt=""/></div><span><?php echo $_SESSION['lm_auth']['name'];?></span> </a>
            
          </li>
        </ul>
        <ul class="user-info pull-left pull-right-xs pull-none-xsm">
          
          
          
        </ul>
      </div>
      
      <!-- Raw Links -->
      <div class="col-md-6 col-sm-4 clearfix hidden-xs">
        <ul class="list-inline links-list pull-right">
          <li> <a href="logout.php"> Log Out <i class="fa fa-power-off right"></i> </a> </li>
        </ul>
      </div>
    </div>
    <hr />