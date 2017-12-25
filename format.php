<?php
function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
            $color[2].$color[3],
            $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return FALSE;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return (($b<<16) | ($g<<8) | $r);
}

function rgb2html($rgb)
{
    $r = ($rgb & 0xff);
    $g = (($rgb>>8) & 0xff);
    $b = (($rgb>>16) & 0xff);

    $r = dechex($r);
    $g = dechex($g);
    $b = dechex($b);

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}

function pack_str($str, $len)
{
    $sl = strlen($str);
    $ret = "";
    for ($i = 0; $i < $len; $i ++) {
        if ($i < $sl) $ret .= pack("C", ord(substr($str, $i, 1)));
        else $ret .= pack("x");
    }
    return $ret;
}

function parse_int_list($list)
{
    if (!$list) return array();
    $list = str_replace("，", ",", $list);
    $list = str_replace(" ", "", $list);
    $ret = array();
    $arr = explode(",", $list);
    foreach ($arr as $str) {
        $ret[] = intval($str);
    }
    return $ret;
}

function parse_positive_int_list($list)
{
    if (!$list) return array();
    $list = str_replace("，", ",", $list);
    $list = str_replace(" ", "", $list);
    $ret = array();
    $arr = explode(",", $list);
    foreach ($arr as $str) {
        $val = intval($str);
        if ($val <= 0) break;
        $ret[] = $val;
    }
    return $ret;
}

function gen_int_list($array)
{
    if (count($array) == 0) return "0";
    $list = "";
    $first = TRUE;
    foreach ($array as $v) {
        if (!$first) $list .= ",";
        $first = FALSE;
        $list .= $v;
    }
    return $list;
}

function gen_positive_int_list($array)
{
    if (count($array) == 0 || $array[0] <= 0) return "0";
    $list = "";
    $first = TRUE;
    foreach ($array as $v) {
        if ($v <= 0) break;
        if (!$first) $list .= ",";
        $first = FALSE;
        $list .= $v;
    }
    return $list;
}

function trim_quotes(&$str)
{
    if (strlen($str) < 2) return;
    $hc = substr($str, 0, 1);
    if (($hc == "'" || $hc == "\"") && (substr($str, -1, 1) == $hc))
        $str = substr($str, 1, -1);
}

function get_config($cfg, $key, $default_val = "")
{
    $result = exec("/bin/get_config $cfg $key $default_val", $output, $res);
    if ($res == 0) return trim($result);
    return $default_val;
}

function set_config($cfg, $key, $val)
{
    exec("/bin/set_config $cfg $key \"$val\"", $output, $res);
    if ($res == 0) return TRUE;
    else return FALSE;
}

function parse_date($date_str)
{
    if (!$date_str) return 0;
    $date_str = str_replace("－", "-", $date_str);
    $arr = explode("-", trim($date_str));
    return sprintf("%d%02d%02d", $arr[0], $arr[1], $arr[2]);
}

function date_str($date)
{
    $y = intval($date / 10000);
    $m = intval($date % 10000 / 100);
    $d = intval($date % 100);
    return sprintf("%d-%02d-%02d", $y, $m, $d);
}

function todate()
{
    $now = time();
    $y = date('Y', $now);
    $m = date('m', $now);
    $d = date('d', $now);
    return sprintf("%d-%02d-%02d", $y, $m, $d);
}

/* return 0=SUN, ..., 6=SAT */
function get_weekday($date)
{
    $y = intval($date / 10000);
    $m = intval($date % 10000 / 100);
    $d = intval($date % 100);
    return date('w', mktime(0, 0, 0, $m, $d, $y, 0));
}

function parse_time($time_str)
{
    if (!$time_str) return 0;
    $time_str = str_replace("：", ":", $time_str);
    $arr = explode(":", trim($time_str));
    return sprintf("%02d%02d%02d", $arr[0], $arr[1], $arr[2]);
}

function time_str($time, $want_sec = true)
{
    $h = intval($time/10000);
    $m = intval($time%10000/100);
    if ($want_sec) {
        $s = intval($time%100);
        return sprintf("%02d:%02d:%02d", $h, $m, $s);
    } else {
        return sprintf("%02d:%02d", $h, $m);
    }
}

function ftp_put_file($ip, $local_f, $remote_f)
{
    $cmd = "/usr/bin/ftpput -u ftp -p SecretftP $ip $remote_f $local_f";
    $result = exec($cmd, $output, $res);
    if ($res == 0) TRUE;
    return FALSE;
}

function dbg_log($msg)
{
    openlog("webadm", LOG_NDELAY, LOG_LOCAL0);
    syslog(LOG_INFO, $msg);
    closelog();
}

function dir_list($dir)
{
    $results = array();
    $handler = opendir($dir);
    if (!$handler) return $results;
    while ($file = readdir($handler)) {
        if ($file != "." && $file != "..") {
            $results[] = $file;
        }
    }
    closedir($handler);
    return $results;
}

function get_output_nic()
{
    $bonding_mode = get_config("/data/config/network.cfg", "bonding_mode", "enabled");
    if ($bonding_mode == "enabled") return "bond0";
    else return get_config("/data/config/network.cfg", "output_nic", "eth1");
}

# we assume bond0 or eth0 as admin nic
function get_admin_nic()
{
    $bonding_mode = get_config("/data/config/network.cfg", "bonding_mode", "enabled");
    if ($bonding_mode == "enabled") return "bond0";
    else return "eth0";
}

function get_nic_ip($nic)
{
    return exec("ifconfig $nic|grep 'inet addr:'|awk -F':' '{print $2}'|awk '{print $1}'");
}

function get_output_ip()
{
    return get_nic_ip(get_output_nic());
}

function get_admin_ip()
{
    return get_nic_ip(get_admin_nic());
}

function get_client_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}

function get_image_type()
{
    $type = exec("grep -w 'image_type:' /etc/product.info|awk '{print $2}'");
    if ($type == "") $type = "DVB";
    return $type;
}

function get_file_count($dir_path)
{
    $n = 0;
    if (!is_dir($dir_path)) return $n;
    $dir = new DirectoryIterator($dir_path);
    foreach ($dir as $file) {
        if (!$file->isFile()) continue;
        $n ++;
    }
    return $n;
}

function normalize_string(&$str, $repchar = "-")
{
    if (!$str) return;
    $badchars = array(" ", "\t", "\r", "\n", "&", "<", ">", "\"", "'", "!", "$", "`", "\\");
    $str = str_replace($badchars, $repchar, $str);
}

?>
