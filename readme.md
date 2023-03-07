# canal 客户端
连接 canal-server，同步 mysql 数据到 es。
## 版本
`php:^7.3`

`es:^7`

`elasticsearch/elasticsearch:7.17.1`

`laravel/lumen:^6`

`illuminate/redis:6.20.44`

`predis/predis:^2.1`
## 运行步骤
1. 拉取项目
2. 下载依赖
```
composer install
```
3. 配置 .env 文件
4. 运行项目-将binlog信息初步解析投递到队列
```
php artisan binlog_to_es
```
5. 启动队列任务
```
# timeout:超时时间(s)
# trie:重试次数
php artisan queue:work --timeout=60 --tries=3
```
