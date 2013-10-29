<?php
  //require('session.inc');

function header_select($selected){
  $options_l = array (
		      'יציאה' => 'logout.php',
		      'Admin' => '#'
		    );

  $options_r = array (
		      'קובץ חדש'  => 'upload.php',
		      'ריכוז פעולות'  => 'report_complete.php',
		     );
  /*
  $options_admin = array (
			  'רק"מ'        => 'vehicles.php',
			  'מקצועות'     => 'roles.php',
			  'סוגי משימות' => 'tasktypes.php',
			  'משתמשים'     => 'users.php'
			 );
  */
  $active = false;

  // Left side of the nav bar

  //echo "<div id=\"mainnav_left\" class=\"nav_left\" dir=\"ltr\">\n";
  echo "<ul id=\"menu\">\n"; 
  foreach ($options_l as $name => $link){
    /* Show 'Admin' tab only for admins */
    if ( 'admin' == strtolower($name) && isset($_SESSION['MilTrackRole']) && $_SESSION['MilTrackRole']!=7)
      continue;

    if ( !$active && $selected == $name ){
      echo "<li class=\"active\">";
      $active = true;
    }
    else {
      echo "<li>";
    }

    echo "<a href=\"$link\">$name</a>";
    if ( 'admin' == strtolower($name) && isset($options_admin)){
      echo "<ul>";
      foreach ($options_admin as $name_admin => $link_admin){
	echo "<li><a href=\"$link_admin\">$name_admin</a></li>";
      }
      echo "</ul>";
    }
      echo "</li>";
  }
  echo "\n</ul>\n";
  //echo "</div>\n";

  // Right side of the nav bar
  echo "<div id=\"mainnav\" class=\"nav\" dir=\"ltr\">\n";
  echo "<ul>\n";
  foreach ($options_r as $name => $link){
    if ( !$active && $selected == $name ){
      echo "<li class=\"active\">";
      $active = true;
    }
    else {
      echo "<li>";
    }
    echo "<a href=\"$link\">$name</a></li>";
  }
  echo "\n</ul>\n";
  echo "</div>\n";

  //echo "<div id=\"unit_name\"> גדוד האור, סוללה א</div>";
  //echo "<div id=\"logged_user\"> שלום, " . $_SESSION['MilTrackFullName'] . "</div>";
}
?>
