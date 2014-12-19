# Backup MySQL .gz files To Amazon Glacier

This script is a simple code to push your MySQL backup '.gz' files into Amazon
Glacier.

## Instalation

Download and install Composer.

```
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

Rename the config/config.yml.sample to config/config.yml with your information. You'll need to set up your IAM Amazon Key. I'll need to access Amazon Gaclier and create your Vault. Also complete your MySQL informations in these config file.

```
php backup-to-gracier.php file-to-mysql.gz
```

## TODO:
- Test! Yeah I didn't test yet;
- Create a mysqldump splitting the .gz in max 100 MB each file;
- Fix fugure bugz;
