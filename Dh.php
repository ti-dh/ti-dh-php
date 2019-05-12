<?php

/*
 * @desc : 依赖gmp扩展!
 */

class Dh {
  
  // 固定的生成大质数p的source
  private $p_source = '0xFFFFFFFFFFFFFFFFC90FDAA22168C234C4C6628B80DC1CD129024E088A67CC74020BBEA63B139B22514A08798E3404DDEF9519B3CD3A431B302B0A6DF25F14374FE1356D6D51C245E485B576625E7EC6F44C42E9A637ED6B0BFF5CB6F406B7EDEE386BFB5A899FA5AE9F24117C4B1FE649286651ECE45B3DC2007CB8A163BF0598DA48361C55D39A69163FA8FD24CF5F83655D23DCA3AD961C62F356208552BB9ED529077096966D670C354E4ABC9804F1746C08CA18217C32905E462E36CE3BE39E772C180E86039B2783A2EC07A28FB5C55DF06F4C52C9DE2BCBF6955817183995497CEA956AE515D2261898FA051015728E5A8AAAC42DAD33170D04507A33A85521ABDF1CBA64ECFB850458DBEF0A8AEA71575D060C7DB3970F85A6E1E4C7ABF5AE8CDB0933D71E8C94E04A25619DCEE3D2261AD2EE6BF12FFA06D98A0864D87602733EC86A64521F2B18177B200CBBE117577A615D6C770988C0BAD946E208E24FA074E5AB3143DB5BFCE0FD108E4B82D120A93AD2CAFFFFFFFFFFFFFFFF';

  // 公共大质数 : p
  private $p = null;

  // 公共底数 : g
  private $g = null;

  // server端的Bob number，也就是A
  private $server_number = null;

  public function __construct() {
    // 检测gmp扩展是否存在 
    if ( !extension_loaded( 'gmp' ) ) {
      exit( 'GMP扩展不存在，请确保您的PHP环境中是否配置了GMP扩展' );
    }
  }

  /*
   * @desc : 生成p、g和server_number( 也就是Bob )
   * @return : array(
                 p => big-num
                 g => big-num
                 server_number => A
               )
   */
  public function init() {
    // 初始化p g 和 server-number
    $this->_genereate_base_info();
    // 根据p, g, server得到A
    $processed_server_number = $this->_process_server_key();
    // 然后将p 和 g以及server_number和processed_server_number 返回
    return array( 
      'p' => $this->p,
      'g' => $this->g,
      'server_number'           => $this->server_number,
      'processed_server_number' => gmp_strval( $processed_server_number ), 
    );
  }

  /*
   * @desc  : 接受来自与客户端dh数据
   * @param : client_number，来自于客户端的随机数字
   * @param : server_number，来自于客户端的随机数字
   */
  public function compute_share_key( $client_number, $server_number, $p ) {
    // 接受client_number（实际上是经过了client客户端处理过后的client_number）
    // 利用client_number,server_number和p计算出公共密钥key
    $key = gmp_powm( $client_number, $server_number, $p ); 
    // 这个key便是计算出出来的用于对称加解密的公钥
    return gmp_strval( $key );
  }

  /*
   * @desc : 目前先暂时根据固定的大质数生成p和对应的base数字g
   */
  private function _genereate_base_info() {
    // 第一步：根据p_source生成服务器当前固定的p
    $p = gmp_strval( gmp_init( $this->p_source ) ); 
    // 第二步：根据上一步随机出来的质数p，按照算法推出基数g.
    $primitive_flag = 0;
    while( !$primitive_flag ) {
      $g      = gmp_strval( gmp_random_range( 2, gmp_sub( $p, 1 ) ) );
      $g_flag = gmp_powm( gmp_strval( $g ), gmp_sub( $p, 1 ), $p );
      if ( 1 == $g_flag ) {
        $primitive_flag = 1;
      }
    }
    // 随机一个server_number
    $this->server_number = mt_rand( 100, 100000 );
    $this->g = $g;
    $this->p = $p;
  }
  /*
   * @desc : 返回已处理的服务端server-number
   */
  private function _process_server_key() {
    $processed_server_number = gmp_powm( $this->g, $this->server_number, $this->p );
    return $processed_server_number;
  }

  /*
   * @desc : 动态产生新的随机的大质数p和对应的base数字g
             !!!!!!!!!!!!本方法暂时不启用！！
             !!!!!!!!!!!!本方法暂时不启用！！
             !!!!!!!!!!!!本方法暂时不启用！！
             !!!!!!!!!!!!本方法暂时不启用！！
             !!!!!!!!!!!!本方法暂时不启用！！
             !!!!!!!!!!!!本方法暂时不启用！！
   */
  private function _generatepg() {
    $is_prime = false;
    $p        = 23;
    // length是p质数的长度,自然长度，比如123就是3位，24234就是5位
    $length = 21;
    $min    = gmp_init( str_pad( 1, $length, 0, STR_PAD_RIGHT ), 2 );
    $max    = gmp_init( str_pad( 1, $length, 1, STR_PAD_RIGHT ), 2 );  
    // 利用gmp随机出来一个数字，但是这个数字不一定是个质数，所以要确保是个质数
    // 第一步：按照length自然长度随机出一个质数！一定要是质数！
    while ( true != $is_prime ) {
      $random_p = gmp_random_range( $min, $max );
      // gmp_prob_prime返回2，就表示该数字一定是个质数
      //if ( 2 == gmp_prob_prime( $random_p ) ) {
      if ( 1 == gmp_prob_prime( $random_p ) ) {
        $p        = $random_p;
        $is_prime = true;
      }
    }
    // 第二步：根据上一步随机出来的质数p，按照算法推出基数g.
    $primitive_flag = 0;
    while( !$primitive_flag ) {
      $g      = gmp_strval( gmp_random_range( 2, gmp_sub( $p, 1 ) ) );
      $g_flag = gmp_powm( gmp_strval( $g ), gmp_sub( $p, 1 ), $p );
      if ( 1 == $g_flag ) {
        $primitive_flag = 1;
      }
    }
    $this->p = gmp_strval( $p );
    $this->g = gmp_strval( $g );
  } 

}
