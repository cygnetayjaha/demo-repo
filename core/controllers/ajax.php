<?php
defined('APP_NAME') or die(header('HTTP/1.0 403 Forbidden'));

/*
 * @author Balaji
 * @name: Rainbow PHP Framework
 * @copyright © 2017 ProThemes.Biz
 *
 */

//AJAX ONLY 

//POST Request Handler 
if ($_SERVER['REQUEST_METHOD'] =='POST') {
    
    //AJAX Image Verification
    if($pointOut == 'verification') {
        //Load Image Verifcation
        extract(loadCapthca($con));
        
        $cap_type = strtolower($cap_type);
        $customCapPath = PLG_DIR.'captcha'.DIRECTORY_SEPARATOR.$cap_type.'_cap.php';
        define('CAP_VERIFY',1);
        define('CAP_GEN',1);
        
        //Verify image verification.
        require LIB_DIR.'verify-verification.php';  
        
        if(!isset($error)){
            //Verified
            die('1');
        }else{
            //Failed Verification
            echo $error;
            die();
        }
    }
    
}

//PHP Image Verification Reload
if($pointOut == 'phpcap'){
    if(isset($args[0]) && $args[0] != ''){
        extract(loadCapthca($con));
        
        //Generate Image Verification
        $_SESSION['twebCaptcha'] = elite_captcha($color,$mode,$mul,$allowed);   
        
        die($_SESSION['twebCaptcha']['image_src']);     
    }
}

//Set Language
if($pointOut == 'lang') {
    $langCode = raino_trim($args[0]);
    if($langCode != ''){
        $_SESSION['twebUserSelectedLang'] = $langCode;
        if(isset($_SESSION['twebLastCallbackLink']))
            $goToLink = $_SESSION['twebLastCallbackLink'];
        else
            $goToLink = createLink('',true);
        header('Location:'.$goToLink,true,301);
    }else{
        echo 'Language code missing!';
    }
    die();
}

//Set Theme
if($pointOut == 'theme'){
    $themeCode = raino_trim($args[0]);
    if($themeCode == 'unset'){
        unset($_SESSION['twebUserSelectedTheme']);
        unset($_SESSION['twebAdminSelectedTheme']);
        header('Location:'. createLink('',true));
        die();
    }
    if($themeCode != ''){
        if(isThemeExists($themeCode)){
            $_SESSION['twebUserSelectedTheme'] = $themeCode;
            header('Location:'. createLink('',true));
        }else{
            stop('Theme fails to load!');
        }
    }else{
         stop('Theme name missing!');
    }

}

//Say Hello
if($pointOut == 'hello'){
    echo 'Hello';
    die();
}

//Geo IP Information
if($pointOut == 'ip-info'){
    header('Content-Type: application/json');  
    echo getMyGeoInfo($ip, $item_purchase_code, true);
    die();
}

//Account Verification
if($pointOut == 'account-verify'){
    if(isset($_SESSION['twebUsername'])){
      redirectTo(createLink('',true));
      die();
    }
    if($args[0] != '' && $args[1] != ''){
        
        $username = raino_trim($args[0]);
        $code = raino_trim($args[1]);
        
        $row = mysqliPreparedQuery($con, "SELECT * FROM users WHERE username=?",'s',array($username));
        
        if($row !== false){
            //Username found
            $db_email_id = Trim($row['email_id']);
            $db_verified = $row['verified'];
            
            $ver_code = Md5(HASH_CODE . $db_email_id . HASH_CODE);
        
            if ($db_verified == '1'){
                die($lang['204']);
            }
            if ($ver_code == $code){
                if(updateToDbPrepared($con, 'users', array('verified' => '1'), array('username' => $username))) {
                    $error = $lang['205'];
                } else{
                    //echo $lang['16'];
                    header("Location: ".createLink('account/login/verification-success',true));
                    echo '<meta http-equiv="refresh" content="1;url='.createLink('account/login/verification-success',true).'">';
                    exit();
                }
            } else {
                die($lang['206']);
            }
        } else {
            die($lang['83']);
        }
    
    }else{
        die($lang['83']);
    }
die(); 
}

//Master JS Code
if($pointOut == 'master-js'){
$list = getBadWordsList($con);
header('Content-Type: application/javascript');  
echo 'emptyStr = \''. makeJavascriptStr($lang['226']) .'\'; oopsStr = \''. makeJavascriptStr($lang['193']) .'\'; baseUrl = \''. $baseURL .'\'; badStr = \''. makeJavascriptStr($lang['224']) .'\'; badWords = '.makeJavascriptArray($list[0]).'; var trackLink = \''.createLink('rainbow/track',true).'\'; '.detectAdBlockScript($con);
?>
function parseHost(url) {
    var a=document.createElement('a');
    a.href=url;
    return a.hostname;
}
jQuery(document).ready(function(){
	var screenSize = window.screen.width + 'x' + window.screen.height;
    var myUrl = window.location.href;
    var myHost = window.location.hostname;
    var refUrl = document.referrer;
    var refHost = parseHost(refUrl);
    if(myHost == refHost)
        refUrl = 'Direct';
    jQuery.post(trackLink,{page:myUrl,ref:refUrl,screen:screenSize},function(data){
    });    
    if(xdEnabled){
        var xdBlockEnabled = false;
        var testAd = document.createElement('div');
        testAd.innerHTML = '&nbsp;';
        testAd.className = 'adsbox';
        document.body.appendChild(testAd);
        window.setTimeout(function() {
          if (testAd.offsetHeight === 0) {
            xdBlockEnabled = true;
          }
          testAd.remove();
          if(xdBlockEnabled){
            if(xdOption == 'link'){
               window.location = xdData1;
            }else if(xdOption == 'close'){
               $('#xdTitle').html(xdData1);
               $('#xdContent').html(xdData2);
               $('#xdBox').modal('show');
            }else if(xdOption == 'force'){
               $('#xdClose').hide();
               $('#xdTitle').html(xdData1);
               $('#xdContent').html(xdData2);
               $('#xdBox').modal({
                  backdrop: 'static',
                  keyboard: false
               }); 
               $('#xdBox').modal('show');
            }
          }
        }, 100);
    }
});
<?php
die();
}

//Get Website Screenshot
if($pointOut == 'snap') {
    $site = raino_trim($args[0]);
    $site = clean_url($site); $site = "http://$site";
    $site = parse_url(Trim($site));
    $host = strtolower($site['host']);
    if (file_exists(HEL_DIR."site_snapshot/$host.jpg")) {
        $file = HEL_DIR."site_snapshot/$host.jpg";
    }else {
        $file = HEL_DIR.'site_snapshot/no-preview.png';
    }
    ob_clean();
    header("Pragma: cache");
    header('Cache-Control: max-age=86400, public');
    header("Content-type: image/png");
    readfile($file);
    die();
}

//Only Authenticated Users

//Admin Ajax Controller
if(isset($_SESSION['twebAdminToken'])){
    
    //Themes Preview 
    if($pointOut == 'templates'){
        $themeDir = raino_trim($args[0]);
        if(isThemeExists($themeDir)){
            unset($_SESSION['twebUserSelectedTheme']);
            $_SESSION['twebAdminSelectedTheme'] = $themeDir;
            header('Location:'. createLink('',true));
        }else{
            stop('Theme fails to load!');
        }
        die();
    }
    
    //User Account Login
    if($pointOut == 'user-acc'){
        if(isset($args[1]) && $args[1] != ''){
            $username = $args[1];
            $row = mysqliPreparedQuery($con, "SELECT * FROM users WHERE username=?",'s',array($username));
            if($row !== false){
                $db_oauth_uid = $row['oauth_uid'];
                $db_id = $row['id'];
                $_SESSION['twebUserToken'] = passwordHash($db_id . $username);
                $_SESSION['twebToken'] = Md5($db_id.$username);
                $_SESSION['twebOauth_uid'] = $db_oauth_uid;
                $_SESSION['twebUsername'] = $username;
                redirectTo(createLink('',true));
                die();
            }
        }
    }
}

//Script Information
if($pointOut == 'phpinfo'){
    if(isset($args[0]) && $args[0] != ''){
        if(raino_trim($args[0]) == $item_purchase_code) phpinfo();
            die();
    }
}

if($pointOut == 'appinfo'){
    if(isset($args[0]) && $args[0] != ''){
        if(raino_trim($args[0]) == $item_purchase_code){
        echo '<table>
            <tbody>
                <tr><td>Script Name: </td><td>'. APP_NAME .'</td></tr>
                <tr><td>Script Version: </td><td>'. VER_NO .'</td></tr>
                <tr><td>Framework Version: </td><td>'. getFrameworkVersion() .'</td></tr>
                <tr><td>PHP Version: </td><td>'. phpversion() .' <a href="'.createLink($controller.'/phpinfo/'.$item_purchase_code,true).'" target="_blank">(View PHP Info)</a></td></tr>
                <tr><td>MySQL Version: </td><td>'. mysqli_get_server_info($con) .'</td></tr>
                <tr><td>Script Root Dir: </td><td>'. ROOT_DIR .'</td></tr>
                <tr><td>Base URL: </td><td>'. $baseURL .'</td></tr>
                <tr><td>Admin Base URL: </td><td>'. adminLink('',true) .'</td></tr>
                <tr><td>Server IP: </td><td>'. $_SERVER['SERVER_ADDR'] .'</td></tr>
                <tr><td>Server CPU Usage: </td><td>'. getServerCpuUsage() .'</td></tr>
                <tr><td>Server Memory Usage: </td><td>'. round(getServerMemoryUsage(),2) .'</td></tr>
            </tbody>
        </table>';
        }    
    }
    die();
}

//AJAX END
die();
?>