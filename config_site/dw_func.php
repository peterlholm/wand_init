<?php
//  220428  PLH first version

function get_hw_info()
{
    global $windows;
    if ($windows) return "Windows";
    $cmd = "cat /proc/device-tree/model";
    unset($output);
    $r = exec($cmd, $output, $result);
    return $output[0];
}

function battery_power_level() {
    return 88;
}

# internet

function internet_connection($server = 1)
{
    global $windows;
    switch ($server) {
        case 1:
            $addr = "8.8.8.8";
            break;
        case 2:
            $addr = "live.danbots.com";
            break;
        case 3:
            $addr = "dummy.holmnet.dk";
            break;
        default:
            $addr = "8.8.8.8";
    }
    if ($windows)
        $out = exec("ping -w 500 -n 1 $addr", $output, $res);
    else
        $out = exec("ping -w 1 -c 1 $addr 1>/dev/null 2>&1", $output, $res);
    #print ("res ".$res);
    #print $out;
    if ($res==0) return true;
    return false;
    return "OK";
    return $out;
}

# mode shift

function system_set_mode($val = "")
{
    switch ($val) {
        case "reboot":
            $cmd = "sudo systemctl reboot";
            break;
        case "config":
            $cmd = "sudo systemctl --no-block isolate config.target";
            break;
        case "halt":
            $cmd = "sudo systemctl halt";
            break;
        case "restart":
            $cmd = "sudo shutdown -r now";
            $cmd = "sudo systemctl reboot";
            break;
        case "shutdown":
            $cmd = "sudo shutdown -h now";
            break;
        default:
        echo "set_system_default";
            $cmd = "ls";
            break;
    }

    $r = exec($cmd, $output, $result);
    return;
}


# config file functions

function read_config_file($file, &$ssid, &$passphrase)
{
    $content = parse_ini_file($file);
    $ssid = $content['ssid'];
    $passphrase = $content['passphrase'];
    return;
}

function reset_danwand_config()
{
    echo "Reseting danWand Config<br>";
    copy('/etc/danwand.conf.org','/etc/danwand.conf');
    copy('/etc/wpa_supplicant/wpa_supplicant.conf.org', '/etc/wpa_supplicant/wpa_supplicant.conf');
    echo "result: $res $r <br>";
}
