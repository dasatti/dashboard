<div class="sidebar-menu">
    <header class="logo-env"> 
      
      <!-- logo -->
      <div class="logo"> <a href="index.html"> <img src="assets/images/logo@2x.png" width="150" alt="" /> </a> </div>
      
      <!-- logo collapse icon -->
      
      <div class="sidebar-collapse"> <a href="#" class="sidebar-collapse-icon with-animation"><!-- add class "with-animation" if you want sidebar to have animation during expanding/collapsing transition --> 
        <i class="entypo-menu"></i> </a> </div>
      
      <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
      <div class="sidebar-mobile-menu visible-xs"> <a href="#" class="with-animation"><!-- add class "with-animation" to support animation --> 
        <i class="entypo-menu"></i> </a> </div>
    </header>
    <ul id="main-menu" class="">
      <!-- add class "multiple-expanded" to allow multiple submenus to open --> 
      <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" --> 
      <!-- Search Bar -->
      <li id="search">
        <form method="get" action="">
          <input type="text" name="q" class="search-input" placeholder="Search something..."/>
          <button type="submit"> <i class="entypo-search"></i> </button>
        </form>
      </li>
      
      <li <?php if($_GET['act']=='managecalls' || $_GET['act']=='manageemails' || $_GET['act']=='manageroi' || $_GET['act']=='dashboard'){?>class="active opened"<?php }?>> <a href="index.html"> <i class="entypo-chart-bar"></i> <span>Leads</span> </a>
        <ul>
          <li <?php if($_GET['act']=='dashboard'){?>class="active"<?php }?>> <a href="admin.php?act=dashboard">
                   <i class="fa fa-bar-chart-o"></i><span>Overview</span> </a> </li>
          <li <?php if($_GET['act']=='managecalls'){?>class="active"<?php }?>> <a href="admin.php?act=managecalls"> 
                   <i class="fa fa-tty"></i><span>Calls</span> </a> </li>
          <li <?php if($_GET['act']=='manageemails'){?>class="active"<?php }?>> <a href="admin.php?act=manageemails"> 
                   <i class="fa fa-envelope-o"></i><span>Emails</span> </a> </li>
        <li <?php if($_GET['act']=='managesms'){?>class="active"<?php }?>> <a href="admin.php?act=managesms"> <i class="fa fa-weixin"></i> <span>Manage SMS</span> </a> </li>
          <li <?php if($_GET['act']=='manageroi'){?>class="active"<?php }?>> <a href="admin.php?act=manageroi"> 
                   <i class="fa fa-line-chart"></i><span>ROI</span> </a> </li>
        </ul>
      </li>
      
      
      <!--<li <?php if($_GET['act']=='managewebsites'){?>class="active"<?php }?>> <a href="admin.php?act=managewebsites"> <i class="entypo-globe"></i> <span>Websites</span> </a> </li>
      
      
      
      
      
      <li <?php if($_GET['act']=='organic' || $_GET['act']=='visitors' || $_GET['act']=='keyword_positions'){?>class="active opened"<?php }?>> <a href="javascript:void(0)"> <i class="entypo-link"></i> <span>SEO</span> </a>
        <ul>
          <li <?php if($_GET['act']=='organic'){?>class="active"<?php }?>> <a href="admin.php?act=organic"> <span>Organic</span> </a> </li>
          <li <?php if($_GET['act']=='visitors'){?>class="active"<?php }?>> <a href="admin.php?act=visitors"> <span>Visitors</span> </a> </li>
          
          <li <?php if($_GET['act']=='keyword_positions'){?>class="active"<?php }?>> <a href="admin.php?act=keyword_positions"> <span>Keyword Positions</span> </a> </li>
        </ul>
      </li>
      
      
      <li <?php if($_GET['act']=='summary' || $_GET['act']=='timeline'){?>class="active opened"<?php }?>> <a href="javascript:void(0)"> <i class="entypo-star"></i> <span>Social Media</span> </a>
        <ul>
          <li <?php if($_GET['act']=='summary'){?>class="active"<?php }?>> <a href="admin.php?act=summary"> <span>Summary</span> </a> </li>
          <li <?php if($_GET['act']=='timeline'){?>class="active"<?php }?>> <a href="admin.php?act=timeline"> <span>Timeline</span> </a> </li>
        </ul>
      </li>
      
      <li <?php if($_GET['act']=='localsearch'){?>class="active"<?php }?>> <a href="admin.php?act=localsearch"> <i class="entypo-search"></i> <span>Local Search</span> </a> </li>
      
      
      <li <?php if($_GET['act']=='emtimeline' || $_GET['act']=='emcompaign'){?>class="active opened"<?php }?>> <a href="javascript:void(0)"> <i class="entypo-mail"></i> <span>Email Marketing</span> </a>
        <ul>
          <li <?php if($_GET['act']=='emtimeline'){?>class="active"<?php }?>> <a href="admin.php?act=emtimeline"> <span>Timeline</span> </a> </li>
          <li <?php if($_GET['act']=='emcompaign'){?>class="active"<?php }?>> <a href="admin.php?act=emcompaign"> <span>Campaign 01</span> </a> </li>
        </ul>
      </li>-->
    </ul>
  </div>