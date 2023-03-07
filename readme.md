# canal 客户端
连接 canal-server，同步 mysql 数据到 es。
## 版本
`php:^7.3`

`es:^7`

`elasticsearch/elasticsearch:7.17.1`

`laravel/lumen:^6`

## 运行步骤
1. 拉取项目
2. 下载依赖
```
composer install
```
3. 配置 .env 文件
4. 运行项目
```
php artisan synchronization_to_es
```
