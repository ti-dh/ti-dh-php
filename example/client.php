<?php
require __DIR__ . '/vendor/autoload.php';
use \Curl\Curl;
$curl = new Curl();

// 初始化客户端数据
$client_number = mt_rand( 100000, 999999 );

// 1、第一步，获取服务器的p、g和server_number
$ret = $curl->get( 'https://t.ti-node.com/dh/getdhbasedata' );
$ret = json_decode( $ret, true );
$p = $ret['p'];
$g = $ret['g'];
$server_number = $ret['server_num'];

// 2、第二步，根据服务器获取到的数据计算出client-number
$process_client_number = gmp_powm( $g, $client_number, $p );

// 3、第三步，将计算过后的client-number发送给服务器
$ret = $curl->post( 'https://t.ti-node.com/dh/postdhclientdata', array(
  'client_number' => gmp_strval( $process_client_number ),
) );
$ret = json_decode( $ret, true );

// 4、第四步，根据server-number，client-number和p 计算出公共密钥K
$key = gmp_powm( $server_number, $client_number, $p );

echo PHP_EOL.PHP_EOL;
echo "本演示客户端是利用了https://t.ti-node.com当作服务端进行demo演示的，你自己要把本repo中的Dh.php库集成到你的服务端里然后结合本client进行交互即可！";

echo PHP_EOL."DH非对称密钥产生交换：".PHP_EOL;
echo 'client计算出的public key : '.$key.PHP_EOL;
echo 'server计算出的public key : '.$ret['key'].PHP_EOL.PHP_EOL;

echo "请注意：👆返回的public key便是用于参与对称加解密的密钥，正式环境中使用无论如何都是不能在公网上来回传递的，这里之所以显示出来就是为了演示服务端和客户端计算出来的对称密钥是一样的！正式环境里，服务端和客户端就已经可以利用该对称密钥进行加密和解密了！";
echo PHP_EOL.PHP_EOL.PHP_EOL;


