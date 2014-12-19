<?php
// error_reporting(E_ALL);
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
require 'vendor/autoload.php';

use Aws\Glacier\GlacierClient;
use Symfony\Component\Yaml\Yaml;

$countFiles = 0;
$file = 'config/config.yml';
$date = new DateTime(null, new DateTimeZone('America/Toronto'));
$tempFolder = '/tmp';


    if ($file && file_exists($file)) {
        $config = Yaml::parse(file_get_contents($file));
    }


    // print_r($config);
    // $config->amazon->amazonKey
    // $config->amazon->amazonSecret
    // $config->amazon->amazonRegion
    // $config->amazon->amazonVault

    $mysqlDump = sprintf('mysqldump -h %s -u %s -p%s %s | gzip > %s/%s.gz',
        $config['mysql']['host'],
        $config['mysql']['user'],
        $config['mysql']['pass'],
        $config['mysql']['db'],
        $tempFolder,
        $date->format('Y-m-d_H.i')
    );

    echo $mysqlDump;

die("\n");

    system($mysqlDump);


$client = GlacierClient::factory(array(
    'key'    => $config['amazon']['amazonKey'],
    'secret' => $config['amazon']['amazonSecret'],
    'region' => $config['amazon']['amazonRegion'],
));

    foreach (glob($tempFolder . "/*.gz") as $filename) {
        $result = $client->uploadArchive(
            array(
                'vaultName' => $config->amazon->amazonVault,
                'body'      => fopen($filename, 'r'),
            )
        );

        $archiveId = $result->get('archiveId');

        printf("Archive-ID: %s \nFileName: %s \nFileSize: %s MB\n\n------\n",
            $archiveId,
            $filename,
            (filesize($filename) / 1024)
        );

        $countFiles++;
    }


// Reference:
// ----------
// http://blogs.aws.amazon.com/php/post/Tx7PFHT4OJRJ42/Uploading-Archives-to-Amazon-Glacier-from-PHP
// Need to create a method to push files using multi part upload. It's recommended to files bigger than 100 MB


// To split the .gz file:
// ----------------------
// tar -cvvzf test.tar.gz video.avi | split -v 5M -d test.tar.gz video.avi
//
// To join the splitted file:
// --------------------------
// cat vid* > test.tar.gz





