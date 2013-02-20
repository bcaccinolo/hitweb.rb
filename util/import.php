<?php 

DEFINE('INSERT_CATEGORY', false); 
DEFINE('INSERT_CATEGORY_PARENT_LINKS', false); 

DEFINE('INSERT_LINKS', false); 
DEFINE('UPDATE_LINKS', true); 

// mysql connection
$dmoz = mysql_connect(':/tmp/mysql.sock', 'root', '');
if (!$dmoz) {
    die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully';
echo "\n";

// extract data from dmoz
if(INSERT_CATEGORY) {
  $db_selected = mysql_select_db('dmoz', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
  }

  // getting all the categoris from dmoz
  $query = "select * from structure where name like 'Top/World/Fran%';";
  echo $query;
  echo "\n";
  $result = mysql_query($query); 
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  $db_selected = mysql_select_db('hitweb', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use hitweb : ' . mysql_error());
  }

  // insert data in the hitweb
  while ($row = mysql_fetch_assoc($result)) {
    echo $row['catid'];
    echo ', ';
    echo $row['name'];
    echo ', ';
    echo $row['title'];
    echo ', ';
    echo $row['description'];
    echo "\n";

    $query = "insert into hitweb_CATEGORIES VALUES(".$row['catid'].", '".mysql_escape_string($row['name'])."', 0, '".mysql_escape_string($row['title'])."', '".mysql_escape_string( $row['description'] )."','')";
    echo $query;
    echo "\n";
    $insert_result = mysql_query($query); //, $db_selected);
    if (!$insert_result) {
      die('Invalid query: ' . mysql_error());
    }

  }
}


if (INSERT_CATEGORY_PARENT_LINKS) {

  $db_selected = mysql_select_db('hitweb', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use hitweb : ' . mysql_error());
  }

  // get the categorie name list
  $query = "select `hitweb_CATEGORIES`.`CATEGORIES_NAME` as name from `hitweb_CATEGORIES`;";
  echo $query;
  echo "\n";
  $result = mysql_query($query); 
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  echo "\n";
  while ($row = mysql_fetch_assoc($result)) {
    // echo $row['name'];
    // echo "\n";
    
    // calculation of the parent name
    // /Top/FR/Cycle => /Top/FR
    $r = preg_replace('/\/[\w][\wéèç\-,\'’àÉâëôîïüûöù&\.ÿÜÇóí°áéœÎñä,åÖìøŒ+şėã]*$/', '', $row['name']);
    // echo "Parent > " .$r;
    // echo "\n";
		// $last = $r[strlen($r)-1];
    //		if ($last != '/'){
    //			die('parse is not complete');
    //		}

    // getting the parent id from the name
    $query = "select `hitweb_CATEGORIES`.`CATEGORIES_ID` as catid from `hitweb_CATEGORIES` where `hitweb_CATEGORIES`.`CATEGORIES_NAME`='".mysql_escape_string($r)."';";
    // echo $query;
    // echo "\n";
    $catids = mysql_query($query); 
    if (!$catids) {
      die('Invalid query: ' . mysql_error());
    }
    $parentid = mysql_result($catids, 0);

    // update the category entry with the parent id
    if($parentid){
      $query = "update `hitweb_CATEGORIES` set `hitweb_CATEGORIES`.`CATEGORIES_PARENTS` = ".$parentid." where `hitweb_CATEGORIES`.`CATEGORIES_NAME` = '".mysql_escape_string($row['name'])."';";
      echo $query;
      echo "\n";
      $res = mysql_query($query); //, $db_selected);
      if (!$res) {
        die('Invalid query: ' . mysql_error());
      }
    }
  }
}


// import the link just is the related category exists
if (INSERT_LINKS) {

  // no memory limitation
  ini_set('memory_limit', '-1');

  $db_selected = mysql_select_db('dmoz', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use dmoz : ' . mysql_error());
  }

  // get all the links from dmoz
  $query = "select  * from `content_links` where topic like 'Top/World/Fran%';"; // limit 200;";
  echo $query;
  echo "\n";
  $result = mysql_query($query); 
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  $db_selected = mysql_select_db('hitweb', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use hitweb : ' . mysql_error());
  }

  // insert links in hitweb
  while ($row = mysql_fetch_assoc($result)) {
    // echo $row['resource'];
    // echo $row['catid'];

    // checking the category id exists on hitweb
    $query = "select count(*) as num from `hitweb_CATEGORIES` where `hitweb_CATEGORIES`.`CATEGORIES_ID` = ".$row['catid'].";";
    // echo $query;
    // echo "\n";
    $count = mysql_query($query); //, $db_selected);
    if (!$count) {
      die('Invalid query: ' . mysql_error());
    }
    $rowcount = mysql_fetch_assoc($count);
    $numCat = $rowcount['num'];
    // echo $numCat;
    // echo "\n";

    if($numCat > 0) {

      // insertion of the link if the category is present in hitweb
      $query = "insert into `hitweb_LIENS` (`LIENS_URL`) values ('".  mysql_escape_string($row['resource']) ."');"; 
      echo $query;
      echo "\n";
      $res_insert = mysql_query($query);
      if (!$res_insert) {
        die('Invalid query: ' . mysql_error());
      }
      $insert_id = mysql_insert_id();

      // linking the category and the link
      $query = "insert into `hitweb_CATEGORIES_LIENS` (`hitweb_CATEGORIES_LIENS`.`CATEGORIES_LIENS_LIENS_ID`, `hitweb_CATEGORIES_LIENS`.`CATEGORIES_LIENS_CATEGORIES_ID`) values (".$insert_id.", ".$row['catid']." ); "; 
      echo $query;
      echo "\n";
      $res_insert = mysql_query($query);
      if (!$res_insert) {
        die('Invalid query: ' . mysql_error());
      }
    }
  }
}


if(UPDATE_LINKS){

  $db_selected = mysql_select_db('hitweb', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use hitweb : ' . mysql_error());
  }
 
  // getting data links from hitweb
  $query = " SELECT * FROM `hitweb_LIENS` WHERE `hitweb_LIENS`.`LIENS_DESCRIPTION` IS NULL; "; // limit 20;";
  echo $query;
  echo "\n";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  while ($row = mysql_fetch_assoc($result)) {

    echo $row['LIENS_URL'];
    echo "\n";

    $db_selected = mysql_select_db('dmoz', $dmoz);
    if (!$db_selected) {
      die ('Can\'t use hitweb : ' . mysql_error());
    }
    $query = "select * from content_description where externalpage = '".
              mysql_escape_string($row['LIENS_URL'])."';";
    echo $query;
    echo "\n";
    $res_dmoz = mysql_query($query); //, $db_selected);
    if (!$res_dmoz) {
      die('Invalid query: ' . mysql_error());
    }
    $link_data = mysql_fetch_assoc($res_dmoz);

    $db_selected = mysql_select_db('hitweb', $dmoz);
    if (!$db_selected) {
      die ('Can\'t use hitweb : ' . mysql_error());
    }

    echo $link_data['title'];
    echo "\n";

    $query = "update `hitweb_LIENS` set `LIENS_DESCRIPTION` = '".
              mysql_escape_string($link_data['description']) ."', ".
              "`LIENS_NAME`= '".
              mysql_escape_string($link_data['title']) ."', ".
              "`LIENS_TITLE`= '".
              mysql_escape_string($link_data['title']) ."' ".
              " where `LIENS_ID`=". $row['LIENS_ID']  ." ; ";
    
    echo $query;
    echo "\n";
    $r = mysql_query($query); //, $db_selected);
    if (!$r) {
      die('Invalid query: ' . mysql_error());
    }

  }
}

mysql_close();
echo "\nConnection finished\n";

?>
