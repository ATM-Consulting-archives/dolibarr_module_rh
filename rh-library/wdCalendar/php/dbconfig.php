<?php

require('../../../config.php');

class DBConnection{
	function getConnection(){
	  //change to your database server/user name/password
		mysql_connect(DB_HOST,DB_USER,DB_PASS) or
         die("Could not connect: " . mysql_error());
    //change to your database name
		mysql_select_db(DB_NAME) or 
		     die("Could not select database: " . mysql_error());
	}
}
