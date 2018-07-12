<?php

defined('SITEMAP_') or die(header('HTTP/1.0 403 Forbidden'));

/*
 * @author Balaji
 * @name: Turbo Website Reviewer
 * @copyright © 2017 ProThemes.Biz
 *
 */
 
$priorityArr = $freqArr = $linkArr = $tempLangList = array();
$tempLangPageCode = $tempLink = ''; $linkArr[] = $baseURL;

if(isSelected($other['other']['sitemap']['auto'])){
    $priorityArr = array('0.5', '0.6', '0.7', '0.8', '0.9');
    $freqArr = array('hourly', 'daily', 'weekly');
}else{
    $priorityArr = array($other['other']['sitemap']['priority']);
    $freqArr = array($other['other']['sitemap']['freqrange']);
}

$langList = getAvailableLanguageCodes($con);
$sitemap = new Sitemap(substr($baseURL,0,-1));
$sitemap->deleteOldSitemaps(true);
$sitemap->setMultilingual(isSelected($other['other']['sitemap']['multilingual']));
$sitemap->setGzip(isSelected($other['other']['sitemap']['gzip']));
$sitemap->addItem('/', '1.0', 'daily', 'Today', $langList);

$query = 'SELECT * FROM domains_data';
$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_array($result)) {
    $tempLink = $baseURL.'domain/'.$row['domain'];
    if(!in_array($tempLink,$linkArr)){
        $sitemap->addItem('/domain/'.$row['domain'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $langList);
        $linkArr[] = $tempLink;
    }
}

$query = 'SELECT * FROM comp_recent_history';
$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_array($result)) {
    $tempLink = $baseURL.'compare/'.$row['first_domain'].'/vs/'.$row['sec_domain'];
    if(!in_array($tempLink,$linkArr)){
        $sitemap->addItem('/compare/'.$row['first_domain'].'/vs/'.$row['sec_domain'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $langList);
        $linkArr[] = $tempLink;
    }
}

$query = 'SELECT * FROM pages';
$result = mysqli_query($con, $query);

while($row = mysqli_fetch_array($result)) {
    $tempLangPageCode = '';
    if($row['type'] == 'page'){
        if(isSelected($row['status'])){
            if($row['lang'] == 'all'){
                $tempLangList = $langList;
                $tempLink = $baseURL.'page/'.$row['page_url'];
            }else{
                $tempLangPageCode = $row['lang'];
                $tempLangList = array($tempLangPageCode);
                $tempLink = $baseURL.$tempLangPageCode.'/page/'.$row['page_url'];
            }
            if(!in_array($tempLink,$linkArr)){
                if($tempLangPageCode == '')
                    $sitemap->addItem('/page/'.$row['page_url'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $tempLangList);
                else
                    $sitemap->addItem('/'.$tempLangPageCode.'/page/'.$row['page_url'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $tempLangList);
                $linkArr[] = $tempLink;
            }
        }
    }elseif($row['type'] == 'internal'){
        $row['page_url'] = removeShortCodes($row['page_url']);
        if(isSelected($row['status'])){
            if($row['lang'] == 'all'){
                $tempLangList = $langList;
                $tempLink = $baseURL.$row['page_url'];
            }else{
                $tempLangPageCode = $row['lang'];
                $tempLangList = array($tempLangPageCode);
                $tempLink = $baseURL.$tempLangPageCode.'/'.$row['page_url'];
            }
            if(!in_array($tempLink,$linkArr)){
                if($tempLangPageCode == '')
                    $sitemap->addItem('/'.$row['page_url'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $tempLangList);
                else
                    $sitemap->addItem('/'.$tempLangPageCode.'/'.$row['page_url'], pickUpRandom($priorityArr), pickUpRandom($freqArr), 'Today', $tempLangList);
                $linkArr[] = $tempLink;
            }
        }
    }
}

$sitemap->createSitemapIndex($baseURL, 'Today');