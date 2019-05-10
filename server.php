<?php
require_once 'Dh.php';

$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri);
$postData = $_POST;

//暂时只支持两个路由
if (preg_match('/getdhbasedata/', $uri['path'])) {
    //执行获取basedata的接口phop
    $dh = new Dh();
    $result = $dh->init();
    // 注意此处，你的业务系统里需要将p、g、server_number保存起来，而且每个
    // 不同的客户端和你业务系统协商出来的g和server_number都是不相同的，应该
    // 使用客户端的id或者token之类作为前缀保存
    // 此处demo里，我们使用redis存储第一步协商的临时数据，用于第二步计算使用
    $redis = new Redis();
    $redis->connect( '127.0.0.1', 6379 );
    $redis->hmset( 'test:pgs', $result );
    echo json_encode(array(
        'p' => $result['p'],
        'g' => $result['g'],
        'server_number' => $result['processed_server_number'],
    ), JSON_UNESCAPED_UNICODE);
} else if (preg_match('/postdhclientdata/', $uri['path'])) {
    $dh = new Dh();
    // 需要根据客户端传来的id或者token取出上一个接口中协商好的server_number和p
    $redis = new Redis();
    $redis->connect( '127.0.0.1', 6379 );
    $ret   = $redis->hgetall( 'test:pgs' );
    $key   = $dh->compute_share_key( $postData['client_number'], $ret['server_number'], $ret['p'] );
    if (!$key) {
        echo "post params error";
    } else {
        echo json_encode(array(
            'key' => $key
        ), JSON_UNESCAPED_UNICODE);
    }
} else {
    echo "uri error";
}
