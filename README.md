# ti-dh-php
DH算法的API端，DH是一种利用非对称协商对称密钥的交换算法，他避免了对称密钥于公网来回传递的问题。该算法可以利用2次交互作用，在已有API和客户端不通过交换密钥的方式下双方计算出一个一模一样的对称密钥用于对称加解密，而且还可以设置该对称密钥的有效期实时更换该对称密钥，以最大程度保证API本身以及API中收到和返回数据的安全性！

## API说明：

### init()函数
参数：无需任何参数
返回：PHP Array

| 字段 | 含义 |
| ------------ | ------------ |
| p |服务端计算出来的p，返回给客户端 |
| g |服务端计算出来的g，返回给客户端 |
| server_number |服务端【私钥】，请保密，不可以外泄 |
| processed_server_number |处理过的服务端【私钥】，返回给客户端 |


### compute\_share\_key()函数
原型：string compute\_share\_key( string client_number, string server_number, string p )
参数：

| 字段 | 含义 |
| ------------ | ------------ |
| client_number |客户端提交过来client_number |
| server_number |服务端server_number，未经过处理的需要保密的那个 |
| p |服务端计算出来的p |

输出：String，最终结果就是协商完成的用于对称加解密的密钥

## DEMO运行：
对于DH的用法以及使用案例，参考example文件夹，我用PHP基于curl模拟了一个客户端与服务端进行交互，最终换算出一个相同的用于对称加解密的密钥！
```php
// 1.cd example
// 2.在终端命令行下执行：php client
```
___
新增的server.php支持直接在本地起一个DH服务  例如你要起在8877端口 在当前目录执行
```php
php -S 0.0.0.0:8877 server.php
```
