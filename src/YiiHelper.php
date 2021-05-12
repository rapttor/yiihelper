<?php // ﷽‎
namespace RapTToR;

/**
 * @author rapttor
 *
 * require __DIR__ . '/protected/vendor/autoload.php';
 * define class Controller() when not using Yii framework
 */
if (defined("Yii")) {
    \Yii::import('application.components.*');
}
$RapTToR_HELPER = array();

class YiiHelper extends \RapTToR\Helper
{

    public static function header($title, $icon = null)
    {
        if (defined("Yii")) $title = \Yii::t("main", $title);
        return '<h1 class="pull-right"><i class="icons icon-' . $icon . ' pull-right"></i>
            ' . $title . ' &nbsp;</h1>';
    }

    public static function checkEmail($mail, $disposable = null)
    {
        $disposable_mail = array();
        if (is_null($disposable)) {
            $base = "";
            if (defined("Yii")) $base = \Yii::app()->getBasePath();
            $disposable_mail = file_get_contents($base . "/../protected/modules/email/disposable-email.csv");
            $disposable_mail = explode(",", $disposable_mail);
        }
        foreach ($disposable_mail as $disposable) {
            list(, $mail_domain) = explode('@', $mail);
            if (strcasecmp($mail_domain, $disposable) == 0) {
                return true;
            }
        }
        return false;
    }

    public static function imgurl($cat = null, $id = null)
    {
        $base = "";
        if (defined("Yii")) $base = \Yii::app()->baseUrl;
        return $base . "/uploads/" . $cat . "/" . $id . ".jpg";
    }

    public static function img($cat = null, $id = null, $class = "img-responsive")
    {
        return "<img src='" . self::imgurl($cat, $id) . "'  class='$class'>";
    }

    public static function uploadDir($cat = null)
    {
        $base = "";
        if (isset($_SERVER["HTTP_HOST"])) {
            $base = $_SERVER['DOCUMENT_ROOT'] . "/" . (defined("Yii") ? \Yii::app()->baseUrl : "");
        } else {
            if (defined("Yii")) $base = \Yii::getPathOfAlias('application');
        }
        return $base . "/uploads/" . (is_null($cat) ? "" : $cat . '/');
    }



    public static function rand_date($min_date = "01-01-2016", $max_date = "31-12-2016")
    {
        /* Gets 2 dates as string, earlier and later date.
           Returns date in between them.
        */

        $min_epoch = strtotime($min_date);
        $max_epoch = strtotime($max_date);

        $rand_epoch = rand($min_epoch, $max_epoch);

        return date('Y-m-d H:i:s', $rand_epoch);
    }

    public static function domain($str, $dom = "")
    {
        return (strpos($str, "http") === false) ? "http://" . $str : $str;
    }

    public static function link($url, $text = null, $options = 'target="_blank"')
    {
        if (is_null($text)) $text = $url;
        $link = self::domain($url);
        return "<a href='$link' $options>$text</a>";
    }

    public static function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime, new DateTimeZone(date_default_timezone_get()));
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public static function ago($tm, $rcs = 0)
    {
        if (is_string($tm)) $tm = strtotime($tm);
        $cur_tm = time();
        $dif = $cur_tm - $tm;
        $pds = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
        $lngh = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

        for ($v = sizeof($lngh) - 1; ($v >= 0) && (($no = $dif / $lngh[$v]) <= 1); $v--);
        if ($v < 0) $v = 0;
        $_tm = $cur_tm - ($dif % $lngh[$v]);
        $no = floor($no);
        if ($no <> 1)
            $pds[$v] .= 's';
        $x = sprintf("%d %s ", $no, $pds[$v]);
        if (($rcs == 1) && ($v >= 1) && (($cur_tm - $_tm) > 0))
            $x .= self::ago($_tm);
        return $x;
    }

    public static function more($str, $length = 200, $more = "<!-- more -->")
    {
        if (strlen($str) < $length)
            return $str;

        $id = "SH" . sha1($str);
        $length = (strpos($str, $more) !== false) ? strpos($str, $more) : $length;

        return "<div id='$id'><div class='excerpt'>" . substr($str, 0, $length) . "</div><div style='display:none;' class='more'>" . substr($str, $length, strlen($str)) . "</div>
        </div><a href='javascript:;' title='$length / " . strlen($str) . "' style='cursor:pointer;' onclick='$(\"#$id .more\").toggle();'>[...]</a>";
    }

    /** Yii helper migrate */
    public static function migrate($def, $db)
    {
        foreach ($def as $n => $t) {
            $n = trim($n);
            if (!isset($t["fields"]) && !isset($t["index"]) && !isset($t["values"]))
                $t["fields"] = $t; // set up fields from whole array
            if (isset($t["fields"]))
                @$db->createTable(trim($n), $t["fields"]); // creating a table
            if (isset($t["index"])) foreach ($t["index"] as $u => $f)
                if (!is_null($f)) foreach ($f as $field)
                    @$db->createIndex(trim($n) . $field, trim($n), $field, $u); // create indexe's

            //if (defined("DEVELOPMENT") && DEVELOPMENT)
            if (isset($t["values"])) foreach ($t["values"] as $u => $f) {
                if (is_numeric($u)) $db->insert(trim($n), $f); // create initial values
            }
            if (isset($t["titlevalues"])) foreach ($t["titlevalues"] as $u => $f) {
                $i = array("title" => $u);
                @$db->insert($n, $i);
                $pid = \Yii::app()->db->getLastInsertId();
                foreach ($f as $i) {
                    $tv = array("parent_id" => $pid, "title" => trim($i));
                    @$db->insert($n, $tv);
                }
            }
            if (isset($t["singlefield"])) foreach ($t["singlefield"] as $u => $f) {
                if (is_numeric($u)) $u = "title";
                foreach ($f as $i) {
                    $tv = array($u => trim($i));
                    @$db->insert($n, $tv);
                }
            }
        }
    }

    /** Yii helper migrate */
    public static function migrateDrop($def, $db)
    {
        $done = true;
        foreach ($def as $n => $t) {
            if (!$db->dropTable($n)) $done = false;
        }
        return $done;
    }

    public static function IconMenu($menu)
    {
        $result = "";
        foreach ($menu as $m) $result .= self::Icon($m);
        return $result;
    }

    public static function Icon($i)
    {
        $result = "";
        if (!isset($i["value"]) && isset($i["url"])) $i["value"] = $i["url"];
        if (isset($i["value"]) && isset($i["ion"]) && isset($i["title"])) $result = "<div class='icontext' onclick='window.location.href=\"" .
            (defined("Yii") ? \Yii::app()->createUrl($i["value"]) : $i["value"]) . "\"'>
        <i class='{$i["ion"]}'></i>
        <small>{$i["title"]}</small>
        </div>";

        return $result;
    }

    public static function aVal($a, $k, $d = "")
    {
        if (is_object($a)) $a = (array)$a;
        return (is_array($a) && isset($a[$k])) ? $a[$k] : $d;
    }

    public static function aFind($a, $k, $v)
    {
        if (is_array($a)) foreach ($a as $item) {
            if (is_array($item) && isset($item[$k]) && $item[$k] == $v) return $item;
            if (is_object($item)) foreach ($item as $key => $value)
                if ($k == $key && $v == $value) return $item;
        }
        return null;
    }

    public static function back($title = "Back")
    {
        if (defined("Yii")) $title = \Yii::t("main", $title);
        return "<div class='clearfix'></div><a style='clear:both;margin:10px 0;' class='btn btn-primary' onclick='history.go(-1);'><i class='fa fa-caret-left'></i> " .
            $title . "</a><div class='clearfix'></div>";
    }

    public static function is_json($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string, true))))) ? true : false;
    }

    public static function json_validate($string)
    {
        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
                // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            // throw the Exception or exit // or whatever :)
            exit($error);
        }

        // everything is OK
        return $result;
    }

    public static function jsonValue($arr, $key, $def)
    {
        $value = $def;
        if (isset($arr[$key]) && strlen(trim(strip_tags($arr[$key]))) > 2) $value = json_decode($arr[$key]);
        return $value;
    }

    public static function jsonError()
    {
        $error = null;
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
                // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }
        return $error;
    }

    public static function send($data, $cache = false, $die = true, $convert = true)
    {
        if ($convert) {
            if (is_array($data))
                $data = json_encode($data);
            $json = (!self::is_json($data)) ? json_encode($data, JSON_PRETTY_PRINT) : $data;
        }
        if (!$cache) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 1 Avg 1999 05:00:00 GMT');
        }
        header('Content-Type: application/json');
        if (defined("YII_PATH")) self::disableLogRoutes();
        echo $json;
        if ($die) {
            if (defined("Yii")) \Yii::app()->end();
            die;
        }
    }

    public static function disableLogRoutes()
    {
        foreach (\Yii::app()->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false; // disable any weblogroutes
            }
        }
    }

    public static function map($str, $params)
    {
        foreach ($params as $key => $value)
            $str = str_replace($key, $value, $str);
        return $str;
    }

    public static function receive($url, $params = null, $cache = true)
    { // receive json
        if (!is_null($params)) $url = self::map($url, $params);
        $data = false;
        if (defined("Yii") && $cache) {
            $data = \Yii::app()->cache->get($url);
        }
        if ($data === false || isset($_GET["nocache"])) {
            $data = self::curl($url);
            if (isset($_GET["debug"])) {
                $size = strlen($data);
                echo "from scratch: $url $size <br/>";
            }
        }
        if (defined("Yii") && $cache && $data && strlen($data) > 0) {
            \Yii::app()->cache->set($url, $data, CACHETIME);
        }
        return $data;
    }

    public static function curl($url)
    {
        if (!function_exists('curl_version')) {
            exit("Enable cURL in PHP");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $data = curl_exec($ch);
        $error = "";
        if (isset($_GET["debug"]))
            $error = 'Curl error: ' . curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300 && $data) {
            return $data;
        } else {
            error_log($error);
            return $error;
        }
    }

    public static function exportModelAsJson($data)
    {
        return $json = (!self::is_json($data)) ? json_encode($data) : $data;
    }

    public static function ellipsis($text, $length)
    {
        return (mb_strlen($text) > $length) ? mb_substr($text, 0, $length) . '... ' : $text;
    }

    public static function replaceAll($what, $with, $str)
    {
        while (stripos($str, $what)) $str = str_ireplace($what, $with, $str);
        return $str;
    }

    public static function urlText($str)
    {
        return self::replaceAll('__', '_', preg_replace('/[^\w]/', '_', $str));
    }

    public static function cors()
    {

        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }

    /**
     * Sort array or arrays items on key
     * @param $array
     * @param $key
     * @param bool $desc
     * @return array
     */
    public static function sortItems($array, $key, $desc = false)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        if ($desc) $sorter = array_reverse($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;

        return $array;
    }

    /**
     * @param $str
     * @param string $delimiter
     * @return array
     */
    public static function str2arr($str, $delimiter = ',')
    {
        $arr = array();
        if (is_string($str)) $arr = explode($delimiter, $str);
        foreach ($arr as $key => $value) $arr[$key] = trim($value);
        $arr = array_unique($arr);
        return $arr;
    }

    public static function vardumper($object)
    {
        echo "<pre>";
        var_dump($object);
        die;
    }

    /**
     * Execute Yii1 SQL
     * @param $sql
     * @param string $action
     * @return mixed
     */
    public static function SQL($sql, $action = "all" /* row/all */)
    {
        switch (strtoupper($action)) {
            case "ALL":
                return \Yii::app()->db->createCommand($sql)->queryAll();
            case "ROW":
                return \Yii::app()->db->createCommand($sql)->queryRow();
            default:
                return \Yii::app()->db->createCommand($sql)->queryColumn();
        }
    }

    /**
     * Export the sql to a file
     *
     * @param bool $withData : self explainable
     * @param bool $dropTable : Add to drop the table or not
     * @param string $saveName : the saved file name
     * @param string $savePath
     *
     * @return mixed
     */
    public static function DBExport($withData = true, $dropTable = false, $saveName = null, $savePath = false)
    {
        $pdo = \Yii::app()->db->pdoInstance;
        $mysql = '';
        $tables = $pdo->query("show tables");
        foreach ($tables as $value) {
            $tableName = $value[0];
            if ($dropTable === true) {
                $mysql .= "DROP TABLE IF EXISTS `$tableName`;\n";
            }
            $tableQuery = $pdo->query("show create table `$tableName`");
            $createSql = $tableQuery->fetch();
            $mysql .= $createSql['Create Table'] . ";\r\n\r\n";
            if ($withData != 0) {
                $itemsQuery = $pdo->query("select * from `$tableName`");
                $values = "";
                $items = "";
                while ($itemQuery = $itemsQuery->fetch(PDO::FETCH_ASSOC)) {
                    $itemNames = array_keys($itemQuery);
                    $itemNames = array_map("addslashes", $itemNames);
                    $items = join('`,`', $itemNames);
                    $itemValues = array_values($itemQuery);
                    $itemValues = array_map("addslashes", $itemValues);
                    $valueString = join("','", $itemValues);
                    $valueString = "('" . $valueString . "'),";
                    $values .= "\n" . $valueString;
                }
                if ($values != "") {
                    $insertSql = "INSERT INTO `$tableName` (`$items`) VALUES" . rtrim($values, ",") . ";\n\r";
                    $mysql .= $insertSql;
                }
            }
        }

        ob_start();
        echo $mysql;
        $content = ob_get_contents();
        ob_end_clean();
        $content = gzencode($content, 9);

        if (is_null($saveName)) {
            $saveName = urlencode(\Yii::app()->name) . date('YmdHms') . ".sql.gz";
        }

        if ($savePath === false) {
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Description: Download SQL Export");
            header('Content-Disposition: attachment; filename=' . $saveName);
            echo $content;
        } else {
            $filePath = $savePath ? $savePath . '/' . $saveName : $saveName;
            file_put_contents($filePath, $content);
        }
    }

    /**
     * import sql from a *.sql file
     * @param string $file : with the path and the file name
     * @return mixed
     */
    public static function DBimport($file = '')
    {
        $pdo = \Yii::app()->db->pdoInstance;
        try {
            if (file_exists($file)) {
                $file = file_get_contents($file);
                $file = rtrim($file);
                $newStream = preg_replace_callback("/\((.*)\)/", create_function('$matches', 'return str_replace(";"," $$$ ",$matches[0]);'), $file);
                $sqlArray = explode(";", $newStream);
                foreach ($sqlArray as $value) {
                    if (!empty($value)) {
                        $sql = str_replace(" $$$ ", ";", $value) . ";";
                        $pdo->exec($sql);
                    }
                }
                return true;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
            exit;
        }
    }

    /**
     * @param $app
     * @param string $tables
     */
    public static function BackupTables($tables = '*')
    {
        //get all of the tables
        if ($tables == '*') {
            //$tables = \Yii::app()->db->createCommand('SHOW TABLES')->queryColumn();
            $tables = \RapTToR\Helper::SQL("SHOW TABLES", "columns");
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        //cycle through
        foreach ($tables as $table) {
            $result = \RapTToR\Helper::SQL('SELECT * FROM ' . $table);
            var_dump($result);
            die;
            $num_fields = mysql_num_fields($result);

            $return .= 'DROP TABLE ' . $table . ';';
            $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . $row2[1] . ";\n\n";

            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = mysql_fetch_row($result)) {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }
            $return .= "\n\n\n";
        }

        //save file
        $handle = fopen('db-backup-' . time() . '-' . (md5(implode(',', $tables))) . '.sql', 'w+');
        fwrite($handle, $return);
        fclose($handle);
    }


    /**
     * @param array $crons
     * @return array - with same keys as $cron =false/no error exception/error
     */
    public static function CronJobs($crons = array())
    {
        /*
            $cron=array(
                every   :   (mins),
                command :   (string),
                notify  :   (email),
                debug   :   (boolean),
            );
        */
        $error = array();
        foreach ($crons as $k => $cron) {
            $error[$k] = null;
            $minuteofday = date('H') * 60 + date('m');
            if (isset($cron["every"]) && $cron["every"] % $minuteofday == 0)
                try {
                    \Yii::app()->runController($cron["command"]);
                } catch (Exception  $e) {
                    $error[$k] = $e;
                    if (isset($cron["notify"]))
                        mail($cron["notify"], \Yii::app()->name . ' Exception', var_export($e));
                }
        }
        return $error;
    }

    /*
    * Yii1 run migrations from code
    */
    public static function runMigrationTool($action = "migrate", $param = "")
    {
        //$action = (isset($_GET["action"])) ? htmlspecialchars($_GET["action"], ENT_QUOTES) : "migrate";
        // $param = (isset($_GET["param"])) ? htmlspecialchars($_GET["param"], ENT_QUOTES) : "--interactive=0";

        ini_set('memory_limit', '-1');
        $commandPath = \Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
        $runner = new \CConsoleCommandRunner();
        $runner->addCommands($commandPath);
        $commandPath = \Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'commands';
        $runner->addCommands($commandPath);
        $args = array('yiic', $action, $param);
        ob_start();
        $runner->run($args);
        echo htmlentities(ob_get_clean(), null, \Yii::app()->charset);
    }

    public function actionYiic()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(1500);
        $action = (isset($_GET["action"])) ? htmlspecialchars($_GET["action"], ENT_QUOTES) : "migrate";
        \Yii::import('application.commands.*');
        $cmd = $action . "Command";
        $command = new $cmd('admin', 'admin');
        ob_start();
        echo "<pre>";
        $command->run(array());
        echo htmlentities(ob_get_clean(), null, \Yii::app()->charset);
        //$this->render('index');
    }

    /**
     * Yii1 ActiveRecord DbSchema
     * @param $tableName
     * @param $tableField
     * @return bool
     */
    public function fieldExists($tableName, $tableField)
    {
        $table = \Yii::app()->db->schema->getTable($tableName);
        return (isset($table->columns[$tableField]));
    }

    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    public static function validateTime($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    public function canEdit($status = 20)
    {
        return (!(Yii::app()->user->isGuest) && Yii::app()->user->status >= $status);
    }

    public function btnEdit($obj, $params = array())
    {
        $objClass = get_class($obj);
        $class = (isset($params["class"])) ? $params["class"] : "pull-right";
        $check = (isset($params["check"])) ? $params["check"] : true;
        $showTitle = (isset($params["btnTitle"])) ? true : false;
        $canEdit = true;
        if ($check) $canEdit = $this->canEdit();
        if ($canEdit) {
            $title = false;
            $url = $this->linkEdit($obj);
            if (is_object($obj)) {
                $title = ((property_exists($obj, "title")) ? $obj->title : $objClass);
            } else if (is_array($obj) && isset($obj["class"])) {
                $title = $obj["class"];
            } else if (is_string($obj)) $title = $obj;
            $title = (isset($params["btnTitle"])) ? $params["btnTitle"] : $title;
            if (!$showTitle) $title = "";
            return '<a class="btn btn-edit btn-link ' . $class . '" href="' . $url . '">
                    <i class="la la-pencil"></i> 
                    <span class="title">' . $title . '</span>
                </a>';
        }
        return "";
    }

    public function btnView($obj, $class = "pull-right", $check = true)
    {
        $url = $this->linkView($obj);
        $title = (property_exists($obj, "title")) ? $obj->title : get_class($obj);
        return '<a class="btn btn-edit btn-link ' . $class . '" href="' . $url . '">
                    <i class="la la-pencil"></i> 
                    <span class="title">' . $title . '</span>
                </a>';
    }

    public function btnAdd($type, $params = array())
    {
        if (is_object($type))
            $type = strtolower(get_class($type));

        $class = (isset($params["class"])) ? $params["class"] : "pull-right";
        $check = (isset($params["check"])) ? $params["check"] : true;
        $title = (isset($params["btnTitle"])) ? $params["btnTitle"] : 'Add ' . $type;
        $canEdit = true;
        if ($check) $canEdit = $this->canEdit();
        if ($canEdit) {
            $url = $this->linkAdd($type, $params);
            return '<a title="' . $title . '" class="btn btn-edit btn-link ' . $class . '" href="' . $url . '">
                    <i class="la la-plus"></i> 
                    <span class="title">' . $title . '</span>
                </a>';
        }
        return "";
    }

    public function linkEdit($obj)
    {
        if (is_array($obj) && isset($obj["class"]) && isset($obj["id"])) {
            $section = $obj["class"];
            $id = $obj["id"];
        } else {
            $section = strtolower(get_class($obj));
            $id = $obj->id;
        }
        if (substr($section, strlen($section) - 1, 1) == "2")
            $section = substr($section, 0, strlen($section) - 1);
        $url = Yii::app()->createUrl($section . '/update', array("id" => $id));
        return $url;
    }

    public function linkAdd($type, $params = array())
    {
        if (is_object($type))
            $type = strtolower(get_class($type));

        $url = Yii::app()->createUrl($type . '/create', $params);
        return $url;
    }

    public function linkView($obj)
    {
        $section = strtolower(get_class($obj));
        if (substr($section, strlen($section) - 1, 1) == "2")
            $section = substr($section, 0, strlen($section) - 1);
        $url = Yii::app()->createUrl($section . '/view', array("id" => $obj->id));
        return $url;
    }

    public static function debug($message, $type = "info", $value = null)
    {
        global $RapTToR_HELPER;
        $debug = array("message" => $message, "type" => $type, "value" => $value);
        $RapTToR_HELPER["debug"][] = $debug;
        error_log(json_encode($debug));
    }

    /**
     * @param $strViewFile
     * @param array $arVariables
     * @param bool $return false
     * @param null $sendthis null - pass $this inside object methods, accessible in template
     * @return bool|string
     */
    public static function template($strViewFile, $arVariables = [], $return = false, $sendthis = null)
    {
        $strTemplate = $strViewFile . ".php";
        $strResult = "";
        if (file_exists($strTemplate)) {
            if (is_object($arVariables)) $arVariables = (array)$arVariables;
            if (!is_null($sendthis)) $arVariables["this"] = $sendthis;
            extract($arVariables);
            if ($return) ob_start();
            include($strTemplate);
            if ($return) $strResult = ob_get_clean();
            if ($return) {
                return $strResult;
            } else {
                echo $strResult;
                return true;
            }
        } else return "Template not found $strViewFile";
    }

    /**
     * @param $filehandle = fopen("{$filename}", "r")
     * @param $callback function($data);
     * @param int $rows 1000
     * @param string $delimiter ,
     * @param bool $close true
     */
    public function processLargeCSV($filehandle, $callback, $rows = 1000, $delimiter = ",", $close = true)
    {
        while (($data = fgetcsv($filehandle, $rows, $delimiter)) !== FALSE) {
            call_user_func($callback, $data);
        }
        if ($close) fclose($filehandle);
    }


    /**
     * Get data from table by kv array criteria and set new one with kv+attributes if not exists;
     * @param $table
     * @param $kv
     * @param null $attributes
     * @return array|CActiveRecord|mixed|null
     */
    public static function getset($table, $kv, $attributes = null)
    {
        /** @var CActiveRecord $class */
        $class = ucfirst($table);
        $d = $class::model()->findByAttributes($kv);
        if (!$d && !is_null($attributes)) {
            $d = new $class();

            foreach ($kv as $k => $v)
                $d->$k = $v;
            foreach ($attributes as $k => $v)
                $d->$k = $v;
            try {
                $d->save();
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
        return $d;
    }

    public static function setLanguage($language)
    {
        $userdata = Yii::app()->user->getState("userdata");
        $userdata["language"] = $language;
        Yii::app()->user->setState("userdata", $userdata);

        Yii::app()->language = $language;
    }



    /**
     * Creates and executes an INSERT SQL statement for several rows.
     *
     * Usage:
     * $rows = array(
     *      array('id' => 1, 'name' => 'John'),
     *      array('id' => 2, 'name' => 'Mark')
     * );
     * GeneralRepository::insertSeveral(User::model()->tableName(), $rows);
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $array_columns the array of column datas array(array(name=>value,...),...) to be inserted into the table.
     * @return integer number of rows affected by the execution.
     */
    public static function insertSeveral($table, $array_columns, $uniquekey = null)
    {
        $connection = Yii::app()->db;
        $sql = '';
        $params = array();
        $i = 0;
        foreach ($array_columns as $columns) {
            $names = array();
            $placeholders = array();
            foreach ($columns as $name => $value) {
                if (!$i) {
                    $names[] = $connection->quoteColumnName($name);
                }
                if ($value instanceof CDbExpression) {
                    $placeholders[] = $value->expression;
                    foreach ($value->params as $n => $v)
                        $params[$n] = $v;
                } else {
                    $placeholders[] = ':' . $name . $i;
                    $params[':' . $name . $i] = $value;
                }
            }
            $tablename = $connection->quoteTableName($table);
            if (!$i) {
                $sql = '';
                if (!is_null($uniquekey) && isset($array_columns[$uniquekey])) {
                    $uniquevalue = $array_columns[$uniquekey];
                    $uniquevalue = $connection->quoteColumnName($uniquevalue);
                    $sql .= 'if (EXISTS(select * from ' . $tablename . ' where ' . $uniquekey . '="' . $uniquevalue . '")) ';
                }
                $sql .= 'INSERT IGNORE INTO ' . $tablename
                    . ' (' . implode(', ', $names) . ') VALUES ('
                    . implode(', ', $placeholders) . ')';
            } else {
                $sql .= ',(' . implode(', ', $placeholders) . ')';
            }
            $i++;
        }
        $command = Yii::app()->db->createCommand($sql);
        return $command->execute($params);
        //return $connection->affected_rows();
    }
}
