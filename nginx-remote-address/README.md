```shell
# open nginx.conf in /etc/nginx/nginx.conf
# in http {} put        real_ip_header X-Forwarded-For; and real_ip_recursive on;
http {
    real_ip_header X-Forwarded-For;
    real_ip_recursive on;
}
# in sites-available folder for example dev-majlestech-api-mysql.sureproducts.tech  
# update fastcgi_param to tell php-fpm REMOTE_ADDR shold be point to $http_x_real_ip; as we updated in the first step
location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param REMOTE_ADDR $http_x_real_ip; 
}
# note here i put  fastcgi_param REMOTE_ADDR $http_x_real_ip; after  include fastcgi_params; to prevent my config to be overwrite from fastcgi_params
```