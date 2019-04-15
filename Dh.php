<?php
class Dh {
  private $p_source = '0xFFFFFFFFFFFFFFFFC90FDAA22168C234C4C6628B80DC1CD129024E088A67CC74020BBEA63B139B22514A08798E3404DDEF9519B3CD3A431B302B0A6DF25F14374FE1356D6D51C245E485B576625E7EC6F44C42E9A637ED6B0BFF5CB6F406B7EDEE386BFB5A899FA5AE9F24117C4B1FE649286651ECE45B3DC2007CB8A163BF0598DA48361C55D39A69163FA8FD24CF5F83655D23DCA3AD961C62F356208552BB9ED529077096966D670C354E4ABC9804F1746C08CA18217C32905E462E36CE3BE39E772C180E86039B2783A2EC07A28FB5C55DF06F4C52C9DE2BCBF6955817183995497CEA956AE515D2261898FA051015728E5A8AAAC42DAD33170D04507A33A85521ABDF1CBA64ECFB850458DBEF0A8AEA71575D060C7DB3970F85A6E1E4C7ABF5AE8CDB0933D71E8C94E04A25619DCEE3D2261AD2EE6BF12FFA06D98A0864D87602733EC86A64521F2B18177B200CBBE117577A615D6C770988C0BAD946E208E24FA074E5AB3143DB5BFCE0FD108E4B82D120A93AD2CAFFFFFFFFFFFFFFFF';
  // 公共大质数 : p
  private $p = null;
  // 公共底数 : g
  private $g = null;
  // 私有服务器端随机数
  private $server_number = 122;
  public function __construct() {
    $this->_genereatebase();
  }
  public function getdhbasedata() {
    // 根据p, g, server得到A
    // processed_server_number = ( $this->g )^( $this->server_number ) mod ( $this->p )
    $processed_server_number = gmp_powm( $this->g, $this->server_number, $this->p );
    // 然后将p 和 g以及processed_server_number发送给客户端client
    echo json_encode( array(
      'p' => $this->p,
      'g' => $this->g,
      'server_number' => gmp_strval( $processed_server_number ),
    ) );
  }
  public function postdhclientdata() {
    if ( $this->getRequest()->isPost() ) {
      if ( empty( $_POST['client_number'] ) || !is_numeric( $_POST['client_number'] ) ) {
        exit( json_encode( array(
          'code'    => -1,
          'message' => 'wrong parameters',
        ) ) );
      }
      // 接受client_number（实际上是经过了client客户端处理过后的client_number）
      // 利用client_number,server_number和p计算出公共密钥key
      $key = gmp_powm( $_POST['client_number'], $this->server_number, $this->p );
      echo json_encode( array(
        'key' => gmp_strval( $key ),
      ) );
    }
  }
  private function _genereatebase() {
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
    $this->g = $g;
    $this->p = $p;
  }
}
