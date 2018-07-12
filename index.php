<?php
ini_set("display_errors", "1");
  error_reporting(E_ALL);
session_start();

/**
 * @author Balaji
 * @name: Rainbow PHP Framework
 * @copyright © 2017 ProThemes.Biz
 *
 */
//Application Path
define('ROOT_DIR', realpath(dirname(__FILE__)) .DIRECTORY_SEPARATOR);
define('APP_DIR', ROOT_DIR .'core'.DIRECTORY_SEPARATOR);
define('CONFIG_DIR', APP_DIR .'config'.DIRECTORY_SEPARATOR);

//Load Configuration & Functions
require CONFIG_DIR.'config.php';
require APP_DIR.'functions.php';

/*print_r(APP_DIR);
die;*/



//Check installation
//detectInstaller();
//die('test');

//Database Connection
$con = dbConncet($dbHost,$dbUser,$dbPass,$dbName);




//Start the Application
require APP_DIR.'app.php';

//Theme & Output
require THEME_DIR.'header.php';
require THEME_DIR.VIEW.'.php';
require THEME_DIR.'footer.php';

//Close the database conncetion
mysqli_close($con);
?>