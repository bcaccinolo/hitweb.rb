<?php 

DEFINE('INSERT_CATEGORY', true); 
DEFINE('INSERT_CATEGORY_PARENT_LINKS', false); 

DEFINE('INSERT_LINKS', false); 
DEFINE('UPDATE_LINKS', false); 

// mysql connection
$dmoz = mysql_connect(':/tmp/mysql.sock', 'root', '');
if (!$dmoz) {
    die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully';
echo "\n";

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
    // echo $row['catid'];
    // echo ', ';
    // echo $row['name'];
    // echo ', ';
    // echo $row['title'];
    // echo ', ';
    // echo $row['description'];
    echo "\n";
    echo "\n";

    $query  = "insert into categories (id, created_at, title, description) VALUES(";
    $query .= $row['catid'].", ";
    $query .= "NOW(), ";
    $query .= "'".mysql_escape_string($row['name'])."', ";
    $query .= "'".mysql_escape_string( $row['description'] )."');";
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
  $query = "select `categories`.`title` as name from `categories` where `parent_id` = 0;";
  echo $query;
  echo "\n";
  $result = mysql_query($query); 
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  echo "\n";
  while ($row = mysql_fetch_assoc($result)) {
    // echo $row['title'];
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
    $query = "select `categories`.`id` as catid from `categories` where `categories`.`title`='".mysql_escape_string($r)."';";
    // echo $query;
    // echo "\n";
    $catids = mysql_query($query); 
    if (!$catids) {
      die('Invalid query: ' . mysql_error());
    }
    $parentid = mysql_result($catids, 0);

    // update the category entry with the parent id
    if($parentid){
      $query = "update `categories` set `categories`.`parent_id` = ".$parentid." where `categories`.`title` = '".mysql_escape_string($row['name'])."';";
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
    $query = "select count(*) as num from `categories` where `categories`.`id` = ".$row['catid'].";";
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
      $query = "insert into `links` (`url`, `created_at`, `category_id`) values ('".  mysql_escape_string($row['resource']) ."', NOW(),  ".  $row['catid'] .");"; 
      echo $query;
      echo "\n";
      $res_insert = mysql_query($query);
      if (!$res_insert) {
        die('Invalid query: ' . mysql_error());
      }
      // $insert_id = mysql_insert_id();
      // linking the category and the link
      // $query = "insert into `categories_LIENS` (`categories_LIENS`.`CATEGORIES_LIENS_LIENS_ID`, `categories_LIENS`.`CATEGORIES_LIENS_CATEGORIES_ID`) values (".$insert_id.", ".$row['catid']." ); "; 
      // echo $query;
      // echo "\n";
      // $res_insert = mysql_query($query);
      // if (!$res_insert) {
      //   die('Invalid query: ' . mysql_error());
      // }
    }
  }
}


if(UPDATE_LINKS){

  $db_selected = mysql_select_db('hitweb', $dmoz);
  if (!$db_selected) {
    die ('Can\'t use hitweb : ' . mysql_error());
  }
 
  // getting data links from hitweb
  $query = " SELECT * FROM `links` WHERE `links`.`description` IS NULL; "; // limit 20;";
  echo $query;
  echo "\n";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  while ($row = mysql_fetch_assoc($result)) {

    echo $row['url'];
    echo "\n";

    $db_selected = mysql_select_db('dmoz', $dmoz);
    if (!$db_selected) {
      die ('Can\'t use hitweb : ' . mysql_error());
    }
    $query = "select * from content_description where externalpage = '".
              mysql_escape_string($row['url'])."';";
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

    $query = "update `links` set `description` = '".
              mysql_escape_string($link_data['description']) ."', ".
              "`title`= '".
              mysql_escape_string($link_data['title']) ."' ".
              " where `id`=". $row['id']  ." ; ";
    
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
