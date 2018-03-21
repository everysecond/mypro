## 安装部署

```
git clone git@gitlab.51idc.com:cloud/itsm.git

cd itsm

cp ./.env.example  ./.env

composer update -vvv && composer install -vvv

php artisan key:generate

// For IDE Helper
php artisan ide-helper:generate
```

# ITSM
#启用监听队列  
php artisan queue:listen
#手动启用crontab 定时任务 
php artisan  schedule:run
#crontab编辑自动启动定时任务 * * * * *
php /path/to/artisan schedule:run >> /dev/null 2>&1


#定时重启命令
-rw-r--r-- 1 root root   76 Apr 27 18:19 dump.rdb
-rwxr-xr-x 1 root root  214 Jan  9 14:46 init.sh*
-rwxr-xr-x 1 root root   78 Jan  9 14:47 reload.sh*
-rwxr-xr-x 1 root root   93 Jan  9 14:47 stop.sh*
root@i-u1nf9ymg:/server/sh# vi reload.sh
root@i-u1nf9ymg:/server/sh# pwd
/server/sh
root@i-u1nf9ymg:/server/sh#

