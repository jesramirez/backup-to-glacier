<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'vendor/autoload.php';

use Aws\Glacier\GlacierClient;
use Symfony\Component\Yaml\Yaml;

$countFiles = 0;
$file = 'config/config.yml';
$date = new DateTime(null, new DateTimeZone('America/Toronto'));

    if ($file && file_exists($file)) {
        $config = Yaml::parse(file_get_contents($file));
    }

    $toUploadFolder     = $config['files']['toUploadFolder'];
    $extensionToUpload  = $config['files']['extensionToUpload'];

    $mysqlDump = sprintf('mysqldump -h %s -u %s -p%s %s | gzip > %s/%s.gz',
        $config['mysql']['host'],
        $config['mysql']['user'],
        $config['mysql']['pass'],
        $config['mysql']['db'],
        $toUploadFolder,
        $date->format('Y-m-d_H.i')
    );

//  system($mysqlDump);

    $client = GlacierClient::factory(array(
        'key'    => $config['amazon']['amazonKey'],
        'secret' => $config['amazon']['amazonSecret'],
        'region' => $config['amazon']['amazonRegion'],
    ));

/*
     try {
        $result = $client->initiateJob(array(
            'accountId' => '-',
            'vaultName' => $config['amazon']['amazonVault'],
            'Type' => 'inventory-retrieval'
        ));

//  var_dump($result);

    } catch (Exception $e) {
        echo 'ERROR: ' .$e->getMessage();
    }
 */

    // Getting info from my Vault:
    $result = $client->describeVault(array(
        'accountId' => '-',
        'vaultName' => $config['amazon']['amazonVault'],
    ))->toArray();

    printf("\nVault Name: %s \nLast Inventory Date: %s \nNumber of Archives: %d \nSize: %f MB \n\n",
        $result['VaultName'],
        date('Y-m-d H:i:s', strtotime($result['LastInventoryDate'])),
        $result['NumberOfArchives'],
        $result['SizeInBytes']/pow(10, 6)
    );

    die();


    foreach (glob($toUploadFolder . "/" . $extensionToUpload) as $filename) {
        $result = $client->uploadArchive(
            array(
                'vaultName' => $config['amazon']['amazonVault'],
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


