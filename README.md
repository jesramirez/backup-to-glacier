# Backup MySQL .gz files To Amazon Glacier

This script is a simple code to push your MySQL backup '.gz' files into Amazon
Glacier.

## Instalation

Download and install Composer.

```
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

Edit the config/config.yml with your information. You'll need to set up your IAM Amazon Key. Also complete your
MySQL informations in these config file.

```
php backup-to-gracier.php file-to-mysql.gz
```

