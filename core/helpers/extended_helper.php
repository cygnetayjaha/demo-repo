<?php

function shortCodeFilter($string){

    $regex = "/\{{(.*?)\}}/";
    $arrRegex = "/\[(.*?)\]/";
    preg_match_all($regex, $string, $matches);

    for($i = 0; $i < count($matches[1]); $i++) {
        $match = $matches[1][$i];
        preg_match($arrRegex, $match, $arrMatches);
        if(isset($arrMatches[1])){
            $newMatch =  str_replace("[".$arrMatches[1]."]",'',$match);
            if(isset($GLOBALS[$newMatch][$arrMatches[1]]))
                $string = str_replace("{{".$match."}}",$GLOBALS[$newMatch][$arrMatches[1]],$string);
            else
                stop('SHORT CODE ERROR - "'. $match.'" NOT FOUND');
        }else{
            if(isset($GLOBALS[$match]) && $match != '')
                $string = str_replace("{{".$match."}}",$GLOBALS[$match],$string);
            else
                stop('SHORT CODE ERROR - "'. $match.'" NOT FOUND');
        }
    }
    return $string;
}

function dbStrToArr($dataBal_aji){
    $dataBal_aji = Trim($dataBal_aji);
    if($dataBal_aji == '')
        return array();
    else{
        //return json_decode(stripcslashes($dataBal_aji),true);
        $dataBal_aji = str_replace('\"','"',$dataBal_aji);
        $dataBal_aji = json_decode($dataBal_aji,true);
        $dataBal_aji = array_map_recursive('stripcslashes',$dataBal_aji);
        return $dataBal_aji;
    }
}

function ipBanCheck($con,$ip,$site_name) {
    $query = mysqli_query($con, "SELECT * FROM banned_ip WHERE ip='$ip'");
    if (mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_array($query);
        if($row['reason'] != '')
            die("You have been banned from ".$site_name." <br>"."Reason: ".$row['reason']);
        else
            die("You have been banned from ".$site_name);
    }
}

function getTheme($con){
    $result = mysqli_query($con, "SELECT * FROM interface where id='1'");
    $row = mysqli_fetch_array($result);
    return trim($row['theme']);
}

function getThemeOptions($con,$themeName,$baseURL){
    $themeOptions = array();
    $result = mysqli_query($con, "SELECT * FROM themes_data where id='1'");
    $row = mysqli_fetch_array($result);
    if(isset($row[$themeName.'_theme'])){
        $themeOptions = dbStrToArr($row[$themeName.'_theme']);
        if(isSelected($themeOptions['general']['imgLogo']))
            $themeOptions['general']['themeLogo'] = '<img class="themeLogoImg" src="'.$baseURL.$themeOptions['general']['logo'].'" />';
        else
           $themeOptions['general']['themeLogo'] = '<span class="themeLogoText">'.htmlspecialchars_decode(shortCodeFilter($themeOptions['general']['htmlLogo'])).'</span>';
         $themeOptions['general']['favicon'] = $baseURL.$themeOptions['general']['favicon'];
    }
    return $themeOptions;
}

function isSelected($val,$bol=true,$model=null,$matchString=null,$returnVal=false){

    $checkBalajiVal = null;

    if($matchString == null){
        $checkBalajiVal = filter_var($val, FILTER_VALIDATE_BOOLEAN);
    } else{
        if($matchString == $val)
            $checkBalajiVal = true;
        else
            $checkBalajiVal = false;
    }

    if($checkBalajiVal){
        if($bol){
            if($model == null)
                return true;
            elseif($model == '1'){
                if($returnVal)
                    return 'selected=""';
                else
                    echo 'selected=""';
            }elseif($model == '2'){
                if($returnVal)
                    return 'checked=""';
                else
                    echo 'checked=""';
            }
        }else{
            if($model == null)
                return false;
            elseif($model == '1'){
                if($returnVal)
                    return '';
                else
                    echo '';
            }elseif($model == '2'){
                if($returnVal)
                    return '';
                else
                    echo '';
            }
        }
     }else{
        if($bol){
            if($model == null)
                return false;
            elseif($model == '1'){
                if($returnVal)
                    return '';
                else
                    echo '';
            }elseif($model == '2'){
                if($returnVal)
                    return '';
                else
                    echo '';
            }
        }else{
            if($model == null)
                return true;
            elseif($model == '1'){
                if($returnVal)
                    return 'selected=""';
                else
                    echo 'selected=""';
            }elseif($model == '2'){
                if($returnVal)
                    return 'checked=""';
                else
                    echo 'checked=""';
            }
        }
    }
}


function getLang($con){
    $result = mysqli_query($con, "SELECT * FROM interface where id='1'");
    $row = mysqli_fetch_array($result);
    return trim($row['lang']);
}


function createLink($link='',$return=false){
    $langShortCode = '';
    if(!defined('BASEURL'))
        die('Base URL not set!');
    if(defined('LANG_SHORT_CODE'))
           $langShortCode = LANG_SHORT_CODE.'/';
    if($return)
        return BASEURL.$langShortCode.$link;
    else
        echo BASEURL.$langShortCode.$link;
}


function decSerBase($str){
    return unserialize(base64_decode($str));
}

function getMenuBarLinks($con,$userPageUrl=''){

    $result = mysqli_query($con, "SELECT * FROM pages");
    $rel = $target = $classActive = $relActive = $targetActive = '';
    $headerLinks = $footerLinks = array();

    while($row = mysqli_fetch_array($result)) {
        $header_show = filter_var($row['header_show'], FILTER_VALIDATE_BOOLEAN);
        $footer_show = filter_var($row['footer_show'], FILTER_VALIDATE_BOOLEAN);
        $linkShow = filter_var($row['status'], FILTER_VALIDATE_BOOLEAN);
        $langCheck = $row['lang'] == '' ? 'all' :  $row['lang'];

        if($linkShow){
            if($header_show || $footer_show){
                if($langCheck == 'all' || $langCheck == ACTIVE_LANG){
                    $classActive = $relActive = $targetActive = '';
                    $sort_order = $row['sort_order'];
                    $page_name = shortCodeFilter($row['page_name']);
                    /*if($row['page_name'] == '{{lang[1]}}'){
                        if(isset($_SESSION['twebUsername'])){
                            $page_name = shortCodeFilter('{{lang[217]}}');
                            $row['page_url'] = createLink('dashboard',true);
                        }
                    }*/
                    if($row['type'] != 'page'){
                        $page_content = decSerBase($row['page_content']);
                        $rel = $page_content[0]; $target = $page_content[1];
                    }

                    if($row['type'] == 'page')
                        $page_url = createLink('page/'.$row['page_url'],true);
                    elseif($row['type'] == 'internal')
                        $page_url = shortCodeFilter($row['page_url']);
                    elseif($row['type'] == 'external'){
                        $page_url = $row['page_url'];
                        if($rel != 'none' && $rel != '')
                            $relActive = ' rel="'.$rel.'"';
                        if($target != 'none' && $target != '')
                            $targetActive = ' target="'.$target.'"';
                    }

                    if(rtrim($page_url,'/') == rtrim($userPageUrl,'/'))
                        $classActive = ' class="active"';

                    //Fix - Not needed
                    $classActive = '';
                    if($header_show)
                        $headerLinks[] = array($sort_order,'<li'.$classActive.'><a'.$relActive.$targetActive.' href="'.$page_url.'">'.$page_name.'</a></li>');
                    sort($headerLinks);

                    if($footer_show)
                        $footerLinks[] = array($sort_order,'<li'.$classActive.'><a'.$relActive.$targetActive.' href="'.$page_url.'">'.$page_name.'</a></li>');
                    sort($footerLinks);
                }
            }
        }
    }
    return array($headerLinks,$footerLinks);
}

function trans($str,$customStr=null,$returnStr=false){
    $noNullCheck = false;  //Enable for testing!
    if($noNullCheck)
        $nullData = 'NoNullCheck-Ba-la-ji';
    else
        $nullData = null;
    if(LANG_TRANS){
        if($customStr != $nullData){
            if($returnStr)
                return $customStr;
            else
                echo $customStr;
        }
        else{
            if($returnStr)
                return $str;
            else
                echo $str;
        }
    }else{
            if($returnStr)
                return $str;
            else
                echo $str;
    }
    return true;
}

function genCanonicalData($baseURL, $currentLink, $loadedLanguages=array(), $return = false){
    $data = $activeLang = '';
    if(defined('ACTIVE_LANG'))
        $activeLang = ACTIVE_LANG.'/';

    $activeLink = str_replace(array($baseURL.$activeLang, $baseURL), '', $currentLink);

    $data .= '<link rel="canonical" href="'.$currentLink.'" />'.PHP_EOL;

    foreach($loadedLanguages as $language){
      $data .= '        <link rel="alternate" hreflang="'.$language[2].'" href="'.$baseURL.$language[2].'/'.$activeLink.'" />'.PHP_EOL;
    }
    if($return)
        return $data;
    else
        echo $data;
}


function htmlPrint($htmlCode,$return=false){
    if($return)
        return htmlspecialchars_decode($htmlCode);
    else
        echo htmlspecialchars_decode($htmlCode);
}

function loadCapthca($con){
    $query = mysqli_query($con, "SELECT * FROM capthca where id='1'");
    $row = mysqli_fetch_array($query);
    $cap_options = dbStrToArr($row['cap_options']);
    $cap_data = dbStrToArr($row['cap_data']);
    $cap_type = Trim($row['cap_type']);
    return array_merge($cap_options,$cap_data[$cap_type],array('cap_type'=>$cap_type));
}

function loadAllCapthca($con){
    $query = mysqli_query($con, "SELECT * FROM capthca where id='1'");
    return mysqli_fetch_array($query);
}

function serBase($arr=array()){
    return base64_encode(serialize($arr));
}

function calTextRatio($pageData) {
    $orglen = strlen($pageData);
    $pageData = preg_replace('/(<script.*?>.*?<\/script>|<style.*?>.*?<\/style>|<.*?>|\r|\n|\t)/ms', '', $pageData);
    $pageData = preg_replace('/ +/ms', ' ', $pageData);
    $textlen = strlen($pageData);
    $per = (($textlen * 100) / $orglen);
    return array($orglen,$textlen,$per);
}

function size_as_kb($yoursize) {
    $size_kb = round($yoursize/1024);
    return $size_kb;
}


function calPrice($global_rank) {
    $monthly_inc =round((pow($global_rank, -1.008)* 104943144672)/524);
    $monthly_inc = (is_infinite($monthly_inc)? '5' :$monthly_inc);
    $daily_inc  =round($monthly_inc/30);
    $daily_inc = (is_infinite($daily_inc)? '0':$daily_inc);
    $yearly_inc =round($monthly_inc*12);
    $yearly_inc = (is_infinite($yearly_inc)? '0':$yearly_inc);
    $yearly_inc = ($yearly_inc < 9 ? 10 : $yearly_inc);
    return $yearly_inc;
}


function arrToDbStr($con,$dataBala_ji){
    return mysqli_real_escape_string($con, json_encode($dataBala_ji));
    //return escapeMe($con, json_encode($dataBala_ji));
}


function loadMailSettings($con){
    $query = mysqli_query($con, "SELECT * FROM mail WHERE id='1'");
    return mysqli_fetch_array($query);
}

function filBoolean($val){
    return filter_var($val, FILTER_VALIDATE_BOOLEAN);
}


function quickLoginCheck($con,$ip){

    $date = date('Y-m-d');
    $taskData =  mysqli_query($con, "SELECT * FROM rainbowphp_temp where task='quick_login'");
    $taskRow = mysqli_fetch_array($taskData);
    $taskData = dbStrToArr($taskRow['data']);

    if(isset($taskData[$date])){
        if(isset($taskData[$date][$ip]))
            return false;
    }
    return true;
}


function getMailTemplates($con,$code){
    $query = mysqli_query($con, "SELECT * FROM mail_templates WHERE code='$code'");
    return mysqli_fetch_array($query);
}

function quickLoginDisable($con,$ip){

    $date = date('Y-m-d');
    $taskData =  mysqli_query($con, "SELECT * FROM rainbowphp_temp where task='quick_login'");
    $taskRow = mysqli_fetch_array($taskData);
    $taskData = dbStrToArr($taskRow['data']);

    if(isset($taskData[$date])){
        if(isset($taskData[$date][$ip])){
            return false;
        }else{
            //New IP Record
            $taskData[$date][$ip] = array('time' => time());
        }
    }else{
        //Clear old date and insert new!
        $prevDate = date('Y-m-d', strtotime($date .' -1 day'));
        if(isset($taskData[$prevDate]))
            unset($taskData[$prevDate]);
        $taskData[$date][$ip] = array('time' => time());
    }
    updateToDb($con,'rainbowphp_temp', array(
        'data' => arrToDbStr($con,$taskData)), array('task' => 'quick_login'));
    return true;
}

?>