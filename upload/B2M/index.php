<?php

class BEncode
{

    //public $announce = 'http://example.com/announce';
    protected static $announce = null;
    // Torrent Comment
    protected static $comment = null;
    // Created by Program
    protected static $created_by = null;

    /**
     * Data Setter
     * @param array $data [array of public variables]
     * eg:
     *  $bcoder = new \Bhutanio\BEncode;
     *    $bcoder->init([
     *        'announce'=>'http://www.example.com',
     *        'comment'=>'Downloaded from example.com',
     *        'created_by'=>'TorrentSite v1.0'
     *    ]);
     */
    public static function init($data = array())
    {
        if (is_array($data)) {
            if (isset($data['announce'])) {
                self::$announce = $data['announce'];
            }
            if (isset($data['comment'])) {
                self::$comment = $data['comment'];
            }
            if (isset($data['comment'])) {
                self::$comment = $data['comment'];
            }
            if (isset($data['created_by'])) {
                self::$created_by = $data['created_by'];
            }
        }
    }

    /**
     * Decode a torrent file into Bencoded data
     * @param  string $s [link to torrent file]
     * @param  integer $pos [file position pointer]
     * @return array/null    [Array of Bencoded data]
     * eg:
     */
    public static function dec($s, &$pos = 0)
    {
        if ($pos >= strlen($s)) {
            return null;
        }
        try {
            switch ($s[$pos]) {
                case 'd':
                    $pos++;
                    $retval = array();
                    while ($s[$pos] != 'e') {
                        $key = self::dec($s, $pos);
                        $val = self::dec($s, $pos);
                        //var_dump($key);
                        if ($key === null || $val === null) {
                            throw new \Exception('key or val is null', 1);
                            //break;
                        }
                        $retval[$key] = $val;
                    }
                    $retval["isDct"] = true;
                    $pos++;
                    return $retval;

                case 'l':
                    $pos++;
                    $retval = array();
                    while ($s[$pos] != 'e') {
                        $val = self::dec($s, $pos);
                        if ($val === null) {
                            throw new \Exception('val is null', 2);
                            //break;
                        }
                        $retval[] = $val;
                    }
                    $pos++;
                    return $retval;

                case 'i':
                    $pos++;
                    $digits = strpos($s, 'e', $pos) - $pos;
                    $val = round((float)substr($s, $pos, $digits));
                    $pos += $digits + 1;
                    return $val;
                default:
                    $digits = strpos($s, ':', $pos) - $pos;
                    if ($digits < 0 || $digits > 20) {
                        return null;
                    }
                    $len = (int)substr($s, $pos, $digits);
                    $pos += $digits + 1;
                    if ($pos + $len >= strlen($s)) {
                        throw new \Exception('The array index is exceeded', 0);
                    }
                    $str = substr($s, $pos, $len);
                    $pos += $len;
                    return (string)$str;
            }
        } catch (\Exception $ex) {
            //if ($ex->getCode() != 0) {
            //throw $ex;
            //}
            return null;
        }
        return null;
    }

    /**
     * Created Torrent file from Bencoded data
     * @param  array $d [array data of a decoded torrent file]
     * @return string    [data can be downloaded as torrent]
     */
    public static function enc(&$d)
    {
        if (is_array($d)) {
            $ret = "l";
            $isDict = false;
            if (isset($d["isDct"]) && $d["isDct"] === true) {
                $isDict = 1;
                $ret = "d";
                // this is required by the specs, and BitTornado actualy chokes on unsorted dictionaries
                ksort($d, SORT_STRING);
            }
            foreach ($d as $key => $value) {
                if ($isDict) {
                    // skip the isDct element, only if it's set by us
                    if ($key == "isDct" and is_bool($value)) continue;
                    $ret .= strlen($key) . ":" . $key;
                }
                if (is_int($value) || is_float($value)) {
                    $ret .= "i${value}e";
                } else if (is_string($value)) {
                    $ret .= strlen($value) . ":" . $value;
                } else {
                    $ret .= self::enc($value);
                }
            }
            return $ret . "e";
        } elseif (is_string($d)) // fallback if we're given a single bencoded string or int
            return strlen($d) . ":" . $d;
        elseif (is_int($d) || is_float($d))
            return "i${d}e";
        else
            return null;
    }


    /**
     * Decode a torrent file into Bencoded data
     * @param  string $filename [File Path]
     * @return array/null            [Array of Bencoded data]
     */
    public static function dec_file($filename)
    {
        if (is_file($filename)) {
            $f = file_get_contents($filename, FILE_BINARY);
            return self::dec($f);
        }
        return null;
    }

    /**
     * Decode a torrent file into Bencoded data
     * @param  string $filename [File Path]
     * @return array/null            [Array of Bencoded data]
     */
    public static function dec_fileinfo($filename)
    {
        $torrent = self::dec_file($filename);
        if (!empty($torrent)) {
            $torrent['hash_info'] = sha1(self::enc($torrent['info']));
            $torrent['info_hash'] = pack("H*", $torrent['hash_info']);
            $torrent['file_info'] = self::filelist($torrent);
            return $torrent;
        }
        return null;
    }

    /**
     * Decode a torrent file into Bencoded data
     * @param  string $filename [File Path]
     * @return array/null            [Array of Bencoded data]
     */
    public static function dec_info($data)
    {
        $torrent = self::dec($data);
        if (!empty($torrent)) {
            //字符串
            $torrent['hash_info'] = sha1(self::enc($torrent['info']));
            //TK
            $torrent['info_hash'] = pack("H*", $torrent['hash_info']);
            $torrent['file_info'] = self::filelist($torrent);
            return $torrent;
        }
        return null;
    }

    /**
     * Generate list of files in a torrent
     * @param  array $data [array data of a decoded torrent file]
     * @return array        [list of files in an array]
     */
    public static function filelist($data)
    {
        $FileCount = 0;
        $FileList = array();
        if (!isset($data['info']['files'])) // Single file mode
        {
            $FileCount = 1;
            $TotalSize = $data['info']['length'];
            $FileList[] = array($data['info']['length'], $data['info']['name']);
        } else { // Multiple file mode
            $FileNames = array();
            $TotalSize = 0;
            $Files = $data['info']['files'];
            foreach ($Files as $File) {
                $FileCount++;
                $TotalSize += $File['length'];
                $FileSize = $File['length'];

                $FileName = ltrim(implode('/', $File['path']), '/');

                $FileList[] = array('size' => $FileSize, 'name' => $FileName);
                $FileNames[] = $FileName;
            }
            array_multisort($FileNames, $FileList);
        }
        return array('file_count' => $FileCount, 'total_size' => $TotalSize, 'files' => $FileList);
    }

    /**
     * Replace array data on Decoded torrent data so that it can be bencoded into a private torrent file.
     *
     * @param  array $data [array data of a decoded torrent file]
     * @return array        [array data for torrent file]
     */
    public static function make_private($data)
    {
        // Remove announce
        // announce-list is an unofficial extension to the protocol that allows for multiple trackers per torrent
        unset($data['announce']);
        unset($data['announce-list']);

        // Bitcomet & Azureus cache peers in here
        unset($data['nodes']);

        // Azureus stores the dht_backup_enable flag here
        unset($data['azureus_properties']);

        // Remove web-seeds
        unset($data['url-list']);

        // Remove libtorrent resume info
        unset($data['libtorrent_resume']);

        // Remove profiles / Media Infos
        unset($data['info']['profiles']);
        unset($data['info']['file-duration']);
        unset($data['info']['file-media']);

        // Add Announce URL
        if (is_array(self::$announce)) {
            $data['announce'] = reset(self::$announce);
            $data["announce-list"] = array();
            $announce_list = array();
            foreach (self::$announce as $announceUri) {
                $announce_list[] = $announceUri;
            }
            $data["announce-list"] = $announce_list;
        } else {
            $data['announce'] = self::$announce;
        }
        // Add Comment
        if (!empty(self::$comment)) {
            $data['comment'] = self::$comment;
        } else {
            $data['comment'] = '';
        }
        // Created by and Created on
        if (!empty(self::$created_by)) {
            $data['created by'] = self::$created_by;
        }
        $data['creation date'] = time();

        // Make Private
        $data['info']['private'] = 1;

        // Sort by key to respect spec
        ksort($data['info']);
        ksort($data);

        return $data;
    }

    public static function dec_req_tk($data = null)
    {
        if (empty($data)) return [];
        $result = self::dec($data);
        if (isset($result['failure reason'])) {
//            Log::write("failure reason：" . $result['failure reason'], 'notice');
            //echo "failure reason：" . $result['failure reason'];
        } else if (isset($result['peers'])) {
            $peerArr = array();
            $peers = $result['peers'];

            if (is_array($peers)) {
                foreach ($peers as $peer) {
                    $peerArr[] = array('ip' => $peer['ip'], 'port' => $peer['port']);
                }
            } else {
                for ($i = 0; $i < strlen($peers); $i += 6) {
                    $peer = $peers[$i] . $peers[$i + 1] . $peers[$i + 2] . $peers[$i + 3] . $peers[$i + 4] . $peers[$i + 5];
                    $peer = unpack("Nip/nport", $peer);
                    $peerArr[] = array('ip' => long2ip($peer['ip']), 'port' => $peer['port']);
                }
            }
            $result['peers'] = $peerArr;
            unset($peerArr);
        }
        return $result;
    }

    public static function enc_ip_resp($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            return ip2long($ip);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
            return false;
        if (($ip_n = inet_pton($ip)) === false) return false;
        $bits = 15; // 16 x 2 bit = 32bit hex (ipv6)
        $ipbin = null;
        while ($bits >= 0) {
            $bin = sprintf("%02x", (ord($ip_n[$bits])));
            $ipbin = $bin . $ipbin;
            $bits--;
        }
        //ipv6 2 hex
        return $ipbin;
    }

    public static function dec_ip_resp($bin)
    {
        if (strlen($bin) <= 11) // long (ipv4)
            return long2ip($bin);
        if (strlen($bin) > 32) // ck ipv6
            return false;
        $pad = 32 - strlen($bin);
        for ($i = 1; $i <= $pad; $i++) {
            $bin = "0" . $bin;
        }
        $bits = 0;// 16 x 2 bit = 32bit hex(ipv6)
        $ipv6 = null;
        while ($bits <= 15) {
            $bin_part = substr($bin, ($bits * 2), 2);
            $ipv6 .= sprintf("%02x", hexdec($bin_part)) . (($bits + 1) % 2 == 0 ? ':' : '');
            $bits++;
        }
        return inet_ntop(inet_pton(substr($ipv6, 0, -1)));
    }

    public static function enc_resp($d)
    {
        $merge = array_merge($d, ['isDct' => true]);
        $res = self::enc($merge);
        unset($merge);
        return $res;
    }

    public static function enc_resp_tk($interval = 900, array $peers = array())
    {
        for ($i = 0; $i < count($peers); $i++) {
            $peers[$i] = array_merge($peers[$i], ['isDct' => true]);
        }
        return self::enc_resp(
            [
                'interval' => $interval,
                'min interval' => 60,
                'peers' => $peers
            ]
        );
    }

    public static function enc_resp_err($msg)
    {
        return self::enc_resp(
            [
                "failure reason" => $msg
            ]
        );
    }
}

?>
<html>
<head>

</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {

        $btfile = fopen($_FILES['file']['tmp_name'], "r") or die("Unable to open file!");
        $btStrem = fread($btfile, $_FILES['file']['size']);
        fclose($btfile);
        echo '<pre>';
        print "\r";
        try {
            if (!BEncode::dec($btStrem)) {
                throw new \Exception();
            }
            $btStremDec = BEncode::dec_info($btStrem);
            $hashInfo = $btStremDec['hash_info'];
            $fileName = $_FILES['file']['name'];
            print "文件名：$fileName\r";
            print "磁力链接：magnet:?xt=urn:btih:$hashInfo\r";
        } catch (Exception $e) {
            print "什么玩意，看不懂！\r";
        }
        echo "</pre>";
    }
}
?>
<form action="#" method="post" enctype="multipart/form-data">
    <p>
        文件:<input type="file" name="file"/> <input type="submit" value="Send"/>
    </p>
</form>
</body>
</html>