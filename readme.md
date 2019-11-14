# Laravel＋Vue.jsでタスク管理システムの開発を行う、   
基本的な機能は  
- プロジェクトを作成して、チームを招待。（招待されたユーザーしかアクセスできない）  
- リストでワークフローに適切なステップを作成。  
- 完了すべきタスクをカードとして作成。  
- チームがタスクのカードを開いて期限、コメントなどの詳細を追加。  
- リスト間を移動して進捗を明示。「To Do」から「完了済み」へ瞬時に変更！  
- タスクで担当者をアサインする  
- プロジェクト一覧管理  
- メール通知「招待された・アサインされた場合」  

# Laradockでの環境構築手順
Laradockのインストール  
下記コマンドでlaradockをCloneします。
```
git clone https://github.com/LaraDock/laradock.git
```
次に.envファイル作成
```
cd laradock
cp env-example .env

```
.envファイルに以下の3行を追加します。
```
DB_HOST=mysql
REDIS_HOST=redis
QUEUE_HOST=beanstalkd
```
envファイルにMySQLのところで以下の通り修正します。
```
MYSQL_VERSION=latest
MYSQL_DATABASE=task-management
MYSQL_USER=root
MYSQL_PASSWORD=root
MYSQL_PORT=3306
MYSQL_ROOT_PASSWORD=root
MYSQL_ENTRYPOINT_INITDB=./mysql/docker-entrypoint-initdb.d
```
それではlaradock内で各種必要なコンテナ(Nginx、Redis、Beanstalkd)を起動させます。
```
docker-compose up -d nginx mysql redis beanstalkd
```
下記コマンドでプロジェクトをCloneします。
```
git clone git@github.com:dachoa1995/task-management.git
```
次に.envファイル作成
```
cd task-management
cp .env-example .env
```
.envファイル修正
```
DB_CONNECTION=mysql
DB_HOST=laradock_mysql_1 // mysqlコンテナ名です。
DB_PORT=3306
DB_DATABASE=task-management
DB_USERNAME=root
DB_PASSWORD=root
```
「storage」「bootstrap/cache」ディレクトリについて、webサーバーから書き込みを許可するために、
パーミッションの設定を変更します。
 ```
docker exec -it laradock_workspace_1 bash
cd task-management
chmod 766 storage
chmod 766 bootstrap/cache
composer install
php artisan key:generate
```
Nginxのrootを変更
laradockディレクトリまで移動して、default.confを編集します。

```
cd /var/www/laradock/
vim nginx/sites/default.conf
```
編集するのは"default.conf"のroot部分です。
以下の通り編集します。
```
root /var/www/task-management/public;
```
編集が完了したら保存して、コンテナを再起動します。
```
docker-compose restart
```
Database作成
```
docker exec -it laradock_mysql_1 bash
mysql -u root -p root
create database `task-management`
```

これでlocalhost [http://localhost/] にアクセスしたらLaravelのトップ画面が表示されるはずです。

次は管理画面ライブラリ設定
```
docker exec -it laradock_workspace_1 bash
cd task-management
composer require encore/laravel-admin
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
php artisan admin:install
```
これでlocalhost [http://localhost/admin] にアクセスしたら管理画面が表示されるはずです。  
username: admin  
password: admin

# Vagrantでの環境構築手順
Vagrant インストール  
Vagrant のダウンロードページ「Vagrant Download」から、最新版のファイルをダウンロードします。

インストール後の確認  
インストールが完了したら、次のようにコマンドを打ってみてください、インストールした vagrant のバージョンが表示されればインストール完了です。
```
vagrant -v
```
Vagrantを初期化
```
vagrant init
```
以上実行したら、Vagrantfileが作成されます。

Vagrantfileを編集  
作成されたVagrantfileをテキストエディタで編集します。
```
# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure("2") do |config|
  config.vm.box = "centos/7"

  config.vm.network "private_network", ip: "10.0.0.2"
    config.vm.synced_folder "./project", "/var/www/html/",
    :mount_options => ["dmode=777,fmode=777"]

   config.vm.provider "virtualbox" do |vb|
     vb.memory = "2048"
   end

  config.vm.network :"forwarded_port", guest: 3306, host: 3306
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.hostname = "local"
  config.hostsupdater.aliases = [
    "local.taskmanegement"
  ]
EOT

end
```

仮想マシンを起動
```
vagrant up
```

下記コマンドでをprojectディレクトリ内にCloneします。
```
mkdir project
cd project
git clone git@github.com:dachoa1995/task-management.git
```
次に.envファイル作成
```
cd task-management
cp .env-example .env
```
.envファイル修正
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task-management
DB_USERNAME=root
DB_PASSWORD=
```
「storage」「bootstrap/cache」ディレクトリについて、webサーバーから書き込みを許可するために、
パーミッションの設定を変更します。
```
vagrant ssh
cd /var/www/html/task-management
chmod 766 storage
chmod 766 bootstrap/cache
composer install
php artisan key:generate
```
 Database作成
```
mysql -u root -p
create database `task-management`
```
Apache設定
```
vi /etc/httpd/conf/httpd.conf
```
以下の通り、httpd.confに追加します。
```
<VirtualHost *:80>
       ServerName local.taskmanegement
       DocumentRoot /var/www/html/task-management/public

       <Directory /var/www/html/task-management>
              AllowOverride All
       </Directory>
</VirtualHost>
```
apache スタート
```
systemctl start httpd.service
```
これでlocalhost [http://local.taskmanegement/] にアクセスしたらLaravelのトップ画面が表示されるはずです。

次は管理画面ライブラリ設定
```
vagrant ssh
cd /var/www/html/task-management
composer require encore/laravel-admin
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
php artisan admin:install
```
これでlocalhost [http://local.taskmanegement/admin] にアクセスしたら管理画面が表示されるはずです。  
username: admin  
password: admin
