<?php

/*
* @author Balaji
* @name Turbo Website Reviewer - PHP Script
* @copyright © 2017 ProThemes.Biz
*
*/

define('SAFE_API_KEY', "ABQIAAAANrgclgOSnI8GAOO2GKrfLxSjiiXprvFQi7Qdz4LWsrszinU-iQ");
define('SAFE_PROTOCOL_VER', '3.0');
define('SAFE_CLIENT', 'checkURLapp');
define('SAFE_APP_VER', '1.0');

function get_data_safe($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array('status' => $httpStatus, 'data' => $data);
}

function send_response($input) {
    if (!empty($input))
    {
        $urlToCheck = urlencode($input);

        $url = 'https://sb-ssl.google.com/safebrowsing/api/lookup?client=' . SAFE_CLIENT .
            '&apikey=' . SAFE_API_KEY . '&appver=' . SAFE_APP_VER . '&pver=' . SAFE_PROTOCOL_VER . '&url=' .
            $urlToCheck;

        $response = get_data_safe($url);

        if ($response['status'] == 204)
        {
            return json_encode(array(
                'status' => 204,
                'checkedUrl' => $urlToCheck,
                'message' => 'The website is not blacklisted and looks safe to use.'));
        } elseif ($response['status'] == 200)
        {
            return json_encode(array(
                'status' => 200,
                'checkedUrl' => $urlToCheck,
                'message' => 'The website is blacklisted as ' . $response['data'] . '.'));
        } else
        {
            return json_encode(array(
                'status' => 501,
                'checkedUrl' => $urlToCheck,
                'message' => 'Something went wrong on the server. Please try again.'));
        }
    } else
    {
        return json_encode(array(
            'status' => 401,
            'checkedUrl' => '',
            'message' => 'Please enter URL.'));
    };
}

function safeBrowsing($site) {
    $checkMalware = send_response($site);
    $checkMalware = json_decode($checkMalware, true);
    $malwareStatus = $checkMalware['status'];
    return $malwareStatus;
}

?>