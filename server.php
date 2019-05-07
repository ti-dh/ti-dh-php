<?php
require_once 'Dh.php';

$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri);
$postData = $_POST;

//暂时只支持两个路由
if (preg_match('/getdhbasedata/', $uri['path'])) {
    //执行获取basedata的接口phop
    $dh = new Dh();
    $result = $dh->getdhbasedata();
    echo json_encode(array(
        'p' => $result['p'],
        'g' => $result['g'],
        'server_num' => $result['server_number'],
    ), JSON_UNESCAPED_UNICODE);
} else if (preg_match('/postdhclientdata/', $uri['path'])) {

    $dh = new Dh();
    $key = $dh->postdhclientdata($postData);
    if(!$key){
        echo "post params error";
    } else {
        echo json_encode(['key' => $key], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo "uri error";
}