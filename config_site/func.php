<?php

//  220428  PLH First version
$debug = 0;

$windows = 0;
if (PHP_OS == 'WINNT') {
    $windows = 1;
    $config_dir = "config";
}

# get networks information

function get_ip_address()
{
    global $windows;
    if ($windows) return ("1.2.3.4");
    return ($_SERVER['SERVER_ADDR']);
}

function get_eth_mac()
{
    global $windows;
    if ($windows) return ("11:22:33:44:55");
    $out = exec('ifconfig eth0 | grep ether | tr -s " " | cut -d " " -f 3');
    return $out;
}

function get_wifi_mac()
{
    global $windows;
    if ($windows) return "11:22:33:44:55";
    $out =  exec('ifconfig wlan0 | grep ether | tr -s " " | cut -d " " -f 3');
    if ($out =="")
        $out =  exec('ifconfig wlp3s0 | grep ether | tr -s " " | cut -d " " -f 3');
    return ($out);
}

#
# get wifi information
function get_current_ssid()
{
    return "Unknown";
}

function wifi_ssid()
{
    $cmd = "iwconfig wlan0 | sed -n -e '/ESSID/s/^.*ESSID:\"\(.*\)\".*/\\1/p'";
    $r =  exec($cmd, $output, $result);
    return $r;
}

function wifi_signal_level()
{
    $cmd = "iwconfig wlan0 | sed -n -e '/Signal/s/^.*level=\(.*\) dBm.*/\\1/p'";
    $r =  exec($cmd, $output, $result);
    return $r;
}

function get_ap_device() {
    $cmd = "iw dev | sed -n -e '/Interface/s/^.*Interface //p'";
    $r =  exec($cmd, $output, $result);
    #echo "Result: $result r: x$r\n";
    return $r;
}

function get_ap_list()
{
    $if = get_ap_device();
    unset($output);
    // $cmd = 'wpa_cli scan_result | cut -f5';
    // $cmd = '/sbin/wpa_cli scan_result  ';
    //$cmd = "sudo iwlist $if scan | sed -n -e '/ESSID/s/" . '.*ESSID:"\(.*\)".*/\1/p;/Qual/p' . "'";
    $cmd = "sudo iwlist $if scan | sed -n -e '/ESSID/s/" . '.*ESSID:"\(.*\)".*/\1/p' . "'";
    //echo "$cmd <br>";
    $r =  exec($cmd, $output, $result);
    //echo "Result: $result r: $r\n";
    //print_r($output);
    return array_unique($output);
}

function get_wifi_list()
{
    $cmd = 'sudo iwlist wlan0 scan | egrep "Cell|ESSID|Signal|Rates"';
    $res = exec($cmd, $output, $result);
    if ($res=="") {
        $cmd = 'sudo iwlist wlp3s0 scan | egrep "Cell|ESSID|Signal|Rates"';
        $res = exec($cmd, $output, $result);
        echo "RES $result";
        print_r($output);
    }
    $out = implode("<br>\n", $output);
    return $out;
}

function add_wpa_config($ssid, $passphrase)
{
    global $windows;
    if ($windows) {
        return ("adding wpa config");
    }
    $path = "/etc/wpa_supplicant/wpa_supplicant.conf";
    $config_content = "network={\nssid=\"$ssid\"\npsk=\"$passphrase\"\nkey_mgmt=WPA-PSK\n}\n";
    //echo $config_content;
    if (!file_put_contents("/tmp/wpa_config", $config_content))
        echo "file_put error";
    $cmd = "sudo sh -c 'cat /tmp/wpa_config >>$path '";
    //echo $cmd;
    $r = exec($cmd, $output, $result);
    //echo "Result: $result r: $r\n";
    //print_r($output);

    // if (!file_put_contents($path, $config_content, FILE_APPEND))
    //     echo "wpa_file_put_content went wrong";
    return;
}

function get_wifi_config_list()
{
    $cmd = "wpa_cli list_networks";
    $res = exec($cmd, $out, $result);
    echo "res: " . $res . ' result: '. $result;
    print_r($out);
    for ($i=2; $i<count($out); $i++) {
        echo $out[$i];
        $val = explode(' ', $out[$i]);
    }
}

function remove_wifi_ssid($name) {
    $cmd = "sudo wpa_cli list_networks";
    $res = exec($cmd, $out, $result);
    //echo "res: " . $res . ' result: '. $result;
    //print_r($out);
    $found = False;
    for ($i=2; $i<count($out); $i++) {  
        //echo "Line: ".$out[$i];
        $val = explode("\t", $out[$i]);
        //print_r($val);
        if ($val[1]==$name) {
            $found = True;
            echo "<!-- Removing " . $cmd . "<br>";
            $r = exec("sudo wpa_cli remove_network $val[0]", $output, $result);
            echo "result remove: $result $r <br>";
            print_r($output);
            $r = exec("sudo wpa_cli save_config", $output, $result);
            echo "result save: $result $r <br>";
            print_r($output);
            echo "-->";
        }
    }
    return $found;
}

function get_wifi_status()
{
    unset($output);
    $cmd = 'iwconfig wlan0';
    $r =    exec($cmd, $output, $result);
    //echo "Result: $result r: $r\n";
    //print_r($output);
    return "<pre>" . implode("\n", $output) . "</pre>";
}

function change_host_name($name)
{
    echo "Changing hostname to $name <br>";
    if (strlen($name)<3) {
        echo "<h1>Hostname $name to short</h1>";
        return false;
    }
    $r  = exec("sudo hostnamectl set-hostname $name", $out, $res);
    if ($debug) {
        echo "result: $res $r <br>";
    }
    # the folowwing line contains tabs
    $str = "/127.0.1.1/s/127.0.1.1.*/127.0.1.1	"."$name/";
    $cmd = "sudo sed -i /etc/hosts -e '" . $str . "'";
    $r = exec($cmd, $out, $res);
    if ($debug) {
        echo $cmd . "<br>";
	    echo "result: $res $r <br>";
    }
    #print_r($out);
}


// get_wifi_config_list();

// remove_wifi_ssid("dummy");
