<?php

ini_set('memory_limit', '-1');
error_reporting(0);
ini_set('display_errors', "0");
set_time_limit(0);


function get($url, $header_get)
{
    // create curl resource
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //enable headers
    curl_setopt($ch, CURLOPT_HEADER, 1);
    //set timeout 30 sec
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    //get only headers
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);

    $headers = [];
    $output = rtrim($output);
    $data = explode("\n", $output);
    $headers['status'] = explode(" ", $data[0])[1];
    array_shift($data);

    foreach ($data as $part) {

        //some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
        $middle = explode(":", $part, 2);

        //Supress warning message if $middle[1] does not exist, Thanks to @crayons
        if (!isset($middle[1])) {
            $middle[1] = null;
        }

        $headers[trim($middle[0])] = trim($middle[1]);
    }



    return (object) ["select_status" => (isset($headers[$header_get]) ? 1 : 0), "select_value" => $headers[$header_get], "raw" => $headers];
}


function karakter($kata, $karakter, $posisi = STR_PAD_BOTH)
{

    $jml = strlen($kata);

    if ($jml < $karakter) {
        $return = str_pad($kata, $karakter, " ", $posisi);
    } elseif ($jml > $karakter) {
        $return = str_pad(substr($kata, 0, $karakter), $karakter, " ", $posisi);
    } else {
        $return = $kata;
    }
    return $return;
}

function Savedata($file, $text)
{
    $fp = fopen($file, 'a');
    fwrite($fp, $text . PHP_EOL);
    fclose($fp);
}

function formatOut($list, $select)
{

    echo "+----------------------+----------+" . PHP_EOL;
    echo "| " . karakter("URLs", 20, STR_PAD_RIGHT) . " | " . karakter($select, 8) . " |" . PHP_EOL;
    echo "+----------------------+----------+" . PHP_EOL;
    $no = 0;
    foreach ($list as $link) {
        $hasil = get($link, $select)->select_status;
        if ($hasil === 1) {
            Savedata('valid.txt', $link);
            $no++;
        } else {
            Savedata('notvalid.txt', $link);
        }
        // Print all headers as array 
        echo "| " . karakter($link, 20, STR_PAD_RIGHT) . " | " . karakter($hasil, 8) . " |" . PHP_EOL;
        echo "+----------------------+----------+" . PHP_EOL;
    }
    echo "Memulai Kalkulasi..." . PHP_EOL;
    sleep(3);
    echo "Hanya terdapat {$no} Url Yang terdapat [ {$select} ]" . PHP_EOL;
}

if (!empty($argv[1]) && file_exists($argv[1])) {

    $list_file = $argv[1];
    $select = $argv[2];
    //get list

    $list = file($list_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // filter list 
    $array_host = [];

    //header
    echo PHP_EOL;
    echo "+--------------------------------------------------+" . PHP_EOL;
    echo "| Tool   : Get Server Response                     |" . PHP_EOL;
    echo "| Author : X'1n73ct                                |" . PHP_EOL;
    echo "| Runing : php scan.php list.txt ResponHeader      |" . PHP_EOL;
    echo '| example: php scan.php list.txt "X-Frame-Options" |' . PHP_EOL;
    echo "+--------------------------------------------------+" . PHP_EOL;
    echo PHP_EOL;


    foreach ($list as $key => $value) {
        $array_host[] = parse_url($value, PHP_URL_HOST);
    }

    echo "[+] Memulai Penyortiran Url" . PHP_EOL;
    sleep(3);
    $hasil_host = array_unique($array_host);
    echo "[+] Hasil Sortir terdapat " . count($hasil_host) . " Url" . PHP_EOL;
    sleep(3);
    echo '[+] Memulai Membaca request "' . $select . '"' . PHP_EOL;
    sleep(3);
    formatOut($hasil_host, $select);
} else {

    echo "[+] Tak ada list yang di scan" . PHP_EOL;
}
