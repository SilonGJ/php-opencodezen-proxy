伪静态
``` php
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```
