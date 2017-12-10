<?php
/**
 * Created by PhpStorm.
 * User: Palash AHmed
 * Date: 1/12/2017
 * Time: 4:46 PM
 */
require '/var/gen/init.v2.php';
require __DIR__ . '/vendor/autoload.php';

use Mpociot\BotMan\BotManFactory;
use React\EventLoop\Factory;
use Mpociot\BotMan\Messages\Message;

error_reporting('E_ALL');

$allowedUser = ["U3S1P8XTK", "U3PQ7NQHX", "U3SQ87WKX"];
function bold($str)
{
    return "*" . $str . "*";
}

function itlaic($str)
{
    return "_" . $str . "_";
}

function strike($str)
{
    return "~" . $str . "~";
}

function pre($str)
{
    return "`" . $str . "`";
}

function code($str)
{
    return "```" . $str . "```";
}
function parseBitpay($data){
    $invoices = explode('{ u',$data);
    $return = array();
    if(!empty($invoices)){
        unset($invoices[0]);
        foreach($invoices as $invoice){
            $arr = array();
            $arr['url'] = find($invoice, "rl: '","'");
            $arr['status'] = find($invoice, "status: '","'");
            $arr['posData'] = find($invoice, "posData: '","'");
            $posSplit = explode("-",$arr['posData']);
            $arr['package'] = $posSplit[1];
            $arr['userid'] = $posSplit[2];
            $arr['ip'] = $posSplit[3];
            $arr['coupon'] = $posSplit[4];
            $arr['price'] = find($invoice, "price: ",","); //confirmations:
            $arr['confirmation'] = find($invoice, "confirmations: ",",");
            $return[] = $arr;
        }
    }
    return $return;
}
function ReqPage($url, $post = false, $httpAuth = false, $ref = false, $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_NOBODY, false); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    if ($ref) {
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    }
    if ($timeout) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    }
    if ($httpAuth) {
        curl_setopt($ch, CURLOPT_USERPWD, $httpAuth);
    }
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    $urlPage = curl_exec($ch);
    if (curl_errno($ch)) {
        return "error";
    }
    curl_close($ch);

    return ($urlPage);
}


function getFile($str)
{
    $filename = 'NULL';
    if (preg_match('/.*filename=[\'\"]([^\'\"]+)/', $str, $matches)) {
        $filename = $matches[1];
    }
    return $filename;
}

function getFileName($url, $cookie, $body = true, $ref = false, $timeout = 2, $dead = true)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, $body); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    if ($ref) {
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    }
    if ($timeout) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    }
    $urlPage = curl_exec($ch);
    if (curl_errno($ch)) {
        return "updating";
    }
    curl_close($ch);

    return ($urlPage);
}

function cleanUsername($username)
{
    $username = str_replace("<", "", $username);
    $username = str_replace(">", "", $username);
    $username = str_replace("@", "", $username);
    return $username;
}

function checkPermission($slackid)
{
    global $allowedUser;
    if (!in_array($slackid, $allowedUser)) return false;
    else return true;
}

function authUser($username, $permission = 3)
{
    global $LSP;
    $botUser = $LSP->fetch("SELECT tg_level  FROM `tg_users` WHERE `tg_slack` = '" . $LSP->escape($username) . "' LIMIT 1");
    $botUser = $botUser[0];
    if (!empty($botUser)) {
        $adminLevel = $botUser['tg_level'];
        if ($adminLevel < $permission) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function adminLevel($username)
{
    global $LSP;
    $botUser = $LSP->fetch("SELECT tg_level  FROM `tg_users` WHERE `tg_slack` = '" . $LSP->escape($username) . "' LIMIT 1");
    $botUser = $botUser[0];
    if (!empty($botUser)) {
        $adminLevel = $botUser['tg_level'];
        return $adminLevel;
    }
}

function getTGuser($username)
{
    global $LSP;
    $posterInfo = $LSP->fetch("SELECT tg_id FROM `tg_users` WHERE tg_slack ='" . $username . "'");
    return $posterInfo[0]['tg_id'];
}

function alexa_rank($page)
{
    if (strpos($page, 'REACH RANK="') !== false) {
        $worldrank = find($page, 'REACH RANK="', '"');
        $countrypart = find($page, '<COUNTRY', '/>');
        $country = find($countrypart, 'NAME="', '"');
        $countryRank = find($countrypart, 'RANK="', '"');
        return array("world" => $worldrank,
            "country" => $country, "countryrank" => $countryRank);
    } else {
        return false;
    }
}

$LSP = new LSP;

$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack_token' => 'token here'
], $loop);

$botman->hears('task help', function ($bot) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }

    $responseText = '1. Add task:  task for {user|me} {tasktext}' . Chr(10);
    $responseText .= '2. View Own Task:   task show mine' . Chr(10);
    $responseText .= '3. View Users Task:   task show {username}' . Chr(10);
    $responseText .= '4. Complete task:   task done {taskid}' . Chr(10);
    $responseText .= '5. Make task pending:   task pending {task_id}' . Chr(10);
    $responseText .= '6. Change priority:   task change priority {task_id} {high|normal|moderate}' . Chr(10);
    $responseText .= '7. Delete Task:   task delete {task_id}' . Chr(10);
    $responseText .= '8. Comment:   task comment {taskid} {comment}' . Chr(10);
    $responseText .= '9. View Task:   task view {taskid}' . Chr(10);
    $bot->reply("<@$poster_id>: \n$responseText");
});



//bug
$botman->hears('new bug (.*)', function ($bot, $bugnote) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }
    $messageFrom = getTGuser($poster_id);
    $sql = "INSERT INTO `tg_bugs` (`msg`, `created_by`) VALUES ('" . $LSP->escape($bugnote) . "', '" . $messageFrom . "');";
    $LSP->query($sql);
    $bugid = mysql_insert_id();
    $bot->reply("<@$poster_id>: \nAdded new bug " . pre("[#$bugid]") . "\n" . code($bugnote));
});

$botman->hears('(fixed|remove|pending|view) bug (\d+)', function ($bot, $option, $bugid) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }


    switch ($option) {
        case 'fixed':
            $LSP->query("UPDATE tg_bugs SET bug_status = 1, completed_on = CURRENT_TIMESTAMP WHERE bug_id = " . $bugid);
            $bot->reply("<@$poster_id>: \nSuccessfully fixed bug " . pre("[#$bugid]"));
            break;
        case 'remove':
            $adminLevel = adminLevel($poster_id);
            if ($adminLevel < 3) {
                $bot->reply("<@$poster_id>: \nYou can not delete bug reports");
                return 0;
            }
            $LSP->query("DELETE FROM tg_bugs WHERE bug_id = " . $bugid);
            $bot->reply("<@$poster_id>: \nSuccessfully deleted bug " . pre("[#$bugid]"));
            break;

        case 'pending':
            $LSP->query("UPDATE tg_bugs SET bug_status = 0, completed_on = NULL WHERE bug_id = " . $bugid);
            $bot->reply("<@$poster_id>: \nStatus changed to pending for bug " . pre("[#$bugid]"));
            break;
    }


});

$botman->hears('show bugs\s?(completed|all)?', function ($bot, $status = null) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }
    $whereSql = " WHERE tb.bug_status = 0 ";
    if ($status == "completed") $whereSql = " WHERE tb.bug_status = 1 ";
    if ($status == "all") $whereSql = " ";
    $availBugs = $LSP->fetch("SELECT tb.*, tu.tg_slack FROM `tg_bugs` as tb JOIN tg_users as tu ON tu.tg_id = tb.created_by $whereSql ORDER BY tb.bug_status ASC, tb.bug_id DESC");

    if (empty($availBugs)) {
        $bot->reply("<@$poster_id>: \n No bugs available!");
        return 0;
    }

    $bugResp = "";
    $bugCount = 0;
    foreach ($availBugs as $bug) {
        $bugCount++;
        $bugText = $bug['msg'];
        if ($bug['bug_status'] == 0) {
            $bugText = bold($bugText) . " <@$bug[tg_slack]>" . " \n";
        }
        if ($bug['bug_status'] == 1) {
            $bugText = strike($bugText) . " <@$bug[tg_slack]>" . " \n";
        }
        $bugResp .= "[#" . $bug['bug_id'] . "] " . $bugText;
    }

    $bot->reply("<@$poster_id>: \n Total Bugs [#$bugCount]\n" . $bugResp);
});


//hitrate
$botman->hears('hitrate of (uploaded|rapidgator)', function ($bot, $host) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 3)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }
    $cacheType = 'U';
    if ($host == 'rapidgator') $cacheType = 'R';
    $totalCount = $LSP->num_rows("SELECT userid as totalDL FROM `plugins_cache_usage` WHERE `CacheType` = '$cacheType'");
    $totalHit = $LSP->num_rows("SELECT userid as totalDL FROM `plugins_cache_usage` WHERE `CacheType` = '$cacheType' AND `API` = 1");
    $hitrate = round(($totalHit * 100) / $totalCount, 2);
    $bot->reply("<@$poster_id>: \n" . code(ucfirst($host) . " Hitrate: $hitrate%"));
});

$botman->hears('income (\w+)\s?(\d{4}-\d{2}-\d{2} to \d{4}-\d{2}-\d{2}|day)?', function ($bot, $option, $range = null) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 3)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }
    $today = new DateTime();
    $x = $currencyBases = Page('https://api.fixer.io/latest?base=USD', 'GET', '', false, false);
    $currencyBases = json_decode($currencyBases, true);
    $gbp2usd = $currencyBases['rates']['GBP'];
    $eur2usd = $currencyBases['rates']['EUR'];
    $respText = "";

    switch ($option) {
        case 'today':
            $respText = listIncomeData("time >= '" . $today->format("Y-m-d") . "'",
                " DATE(FROM_UNIXTIME(v.date)) = '" . $today->format("Y-m-d") . "'",
                "Today",
                $eur2usd, $gbp2usd);
            //$respText .= $x;
            break;
        case 'yesterday':
            $thisDay = $today->format("Y-m-d");
            $today->modify('-1 day');
            $respText = listIncomeData("time < '" . $thisDay . "' AND time >= '" . $today->format("Y-m-d") . "'",
                " DATE(FROM_UNIXTIME(v.date)) < '" . $thisDay . "' AND DATE(FROM_UNIXTIME(v.date)) >= '" . $today->format("Y-m-d") . "'",
                "Yesterday",
                $eur2usd, $gbp2usd);
            break;
        case 'thisweek':
            $today->setTimestamp(time() - (86400 * 7));
            $respText = listIncomeData("time >= '" . $today->format("Y-m-d") . "'",
                " DATE(FROM_UNIXTIME(v.date)) >= '" . $today->format("Y-m-d") . "'",
                "This Week",
                $eur2usd, $gbp2usd);
            break;
        case 'thismonth':
            $thisMonth = $today->format("Y-m") . "-01";
            $respText = listIncomeData("time >= '" . $thisMonth . "'",
                " DATE(FROM_UNIXTIME(v.date)) >= '" . $thisMonth . "'",
                "Current Month",
                $eur2usd, $gbp2usd);
            break;
        case 'lastmonth':
            $thisMonth = $today->format("Y-m") . "-01";
            $today->modify('first day of previous month');
            $respText = listIncomeData("time < '" . $thisMonth . "' AND time >= '" . $today->format("Y-m-d") . "'",
                " DATE(FROM_UNIXTIME(v.date)) < '" . $thisMonth . "' AND DATE(FROM_UNIXTIME(v.date)) >= '" . $today->format("Y-m-d") . "'",
                "Last Month",
                $eur2usd, $gbp2usd);
            break;
        case 'range':
            if ($range == null) {
                $bot->reply("<@$poster_id>: \nEmpty range given");
                break;
            }
            $fromTo = explode(" to ", $range);
            $argIncome['from'] = $fromTo[0];
            $argIncome['to'] = $fromTo[1];
            $whereSql = "";
            if ($argIncome['from'] != ""){
                $wSql[] = " time >= '" . $argIncome['from'] . "' ";
                $wrSql[] = " DATE(FROM_UNIXTIME(v.date)) >= '" . $argIncome['from'] . "' ";
            }
            if ($argIncome['to'] != ""){
                $wSql[] = " time <= '" . $argIncome['to'] . "' ";
                $wrSql[] = " DATE(FROM_UNIXTIME(v.date)) <= '" . $argIncome['to'] . "' ";
            }

            $respText = listIncomeData(implode("AND", $wSql),
                implode("AND", $wrSql),
                "From " . $argIncome['from'] . " to " . $argIncome['to'],
                $eur2usd, $gbp2usd);
            break;
        default:
            if (is_numeric($option) && in_array($range, ['day'])) {
                for ($i = 0; $i < $option; $i++) {
                    $today = new DateTime();
                    if ($i > 0) {
                        $today->modify('-' . $i . ' ' . $range);
                    }
                    $incomeData = $LSP->fetch("SELECT SUM(case when currency = 'USD' then amount end) as earned_usd, SUM(case when currency = 'EUR' then amount end) as earned_eur, SUM(case when currency = 'GBP' then amount end) as earned_gbp, COUNT(id) as sale FROM payments WHERE paid = 'y' AND time = '" . $today->format("Y-m-d") . "'");
                    if (!empty($incomeData)) {
                        $rIncomeData = $LSP->fetch("SELECT v.package, r.discount, r.username FROM `vouchers` v JOIN resellers r ON r.id = v.resellerid WHERE status = 'used' AND resellerid > 0 AND resellerid NOT IN (2,8) AND DATE(FROM_UNIXTIME(v.date)) = '" . $today->format("Y-m-d") . "'");
                        $money = 0;
                        $resellerCount = 0;
                        if (!empty($rIncomeData)) {
                            foreach ($rIncomeData as $income) {
                                $resellerCount++;
                                $packageCost = $LSP->PackagePrice($income['package'], false, 'USD');
                                $discount = round((($packageCost / 100) * $income['discount']), 2);
                                $money += $packageCost - $discount;
                                $incomeData[0]['sale']++;
                            }
                            $incomeData[0]['earned_usd'] += $money;
                        }
                        $totalUsdIncome = $incomeData[0]['earned_usd'];
                        if ($incomeData[0]['earned_eur'] != 0) {
                            $totalUsdIncome += $incomeData[0]['earned_eur'] / $eur2usd;
                        }
                        if ($incomeData[0]['earned_gbp'] != 0) {
                            $totalUsdIncome += $incomeData[0]['earned_gbp'] / $gbp2usd;
                        }
                        $respText .= ">" . pre($today->format("d M, Y") . ":") . "  " . round($totalUsdIncome, 2) . "$  /  Sold Accs: " . $incomeData[0]['sale'] . "\n\n";
                    } else {
                        $respText .= ">" . pre($today->format("d M, Y") . ":") . "  0$\n\n";
                    }
                }
                $bot->reply("<@$poster_id>: Sales in last $option $range\n\n" . $respText);
                return 0;
            }
            break;
    }
    if ($respText != "") {
        $bot->reply("<@$poster_id>: \n" . code($respText));
    }
});

//link
$botman->hears('link <[^( |"|>|<|\r\n\|\n|$)]+\|([^( |"|>|<|\r\n\|\n|$)]+)>\s?(\d+)?', function ($bot, $filehost, $linkcount = 1) {
    global $LSP;

    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }

    $filehost = $LSP->escape($filehost);
    $count = 1;
    if (isset($linkcount)) {
        $count = $linkcount;
    }
    $links = $LSP->fetch("SELECT fileurl FROM `links` WHERE `type` = '" . $filehost . "' GROUP BY fileurl ORDER BY RAND() LIMIT 0," . $count);
    if (!empty($links)) {
        $linkData = "";
        foreach ($links as $link) {
            $linkData .= $link['fileurl'] . "\n";
        }
        $bot->reply("<@$poster_id>: \n$linkData");
    } else {
        $bot->reply("<@$poster_id>: \nCant find any link for " . $filehost . ", may be you mispelled it?");
    }

});

$botman->hears('ip (\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})', function ($bot, $ip) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    $bot->reply("<@$poster_id>: \n" . file_get_contents("http://webresolver.nl/api.php?key=WRD0J-CQN2R-7P810-ZWUZQ&action=geoip&string=" . $ip . "&html=0"));

});

$botman->hears('create (\d+) day\w? voucher', function ($bot, $voucherday) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    if (!authUser($poster_id, 0)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }

    $adminLevel = adminLevel($poster_id);

    if (is_numeric($voucherday)) {
        $resellerid = 8;
        if ($adminLevel == 0 && $voucherday > 1) {
            $bot->reply("<@$poster_id>: \n you can not generate more than 1 day voucher!");
            return 0;
        } elseif ($adminLevel == 1 && $voucherday > 7) {
            $bot->reply("<@$poster_id>: \n you can not generate more than 7 days voucher!");
            return 0;
        }

        if ($adminLevel == 0) {
            $resellerid = 2;
        }

        $vouchercode = $LSP->genVoucher($voucherday, 1, null, null, null, $resellerid);
        $bot->reply("<@$poster_id>: \nHere is your voucher for $voucherday days\n Voucher Code: " . code($vouchercode));
    } else {
        $bot->reply("<@$poster_id>: \nInvalid days");
        return 0;
    }
});

$botman->hears('show alexa of <[^( |"|>|<|\r\n\|\n|$)]+\|([^( |"|>|<|\r\n\|\n|$)]+)>', function ($bot, $link) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    $alexaurl = "http://data.alexa.com/data?cli=10&url=" . $link;

    $page = Page($alexaurl);

    $alexaRanking = alexa_rank($page);

    if (!$alexaRanking) {
        $bot->reply("<@$poster_id>: $link has no alexa ranking\n");
        return 0;
    }
    $respText = "Domain: $link\n";
    $respText .= "World Rank: $alexaRanking[world]\n";
    $respText .= "Country: $alexaRanking[country]\n";
    $respText .= "Country Rank: $alexaRanking[countryrank]\n";
    $respText .= "URL: http://www.alexa.com/siteinfo/$link\n";

    $respText = code($respText);

    $bot->reply("<@$poster_id>: $respText");

});

$botman->hears('search (userid|username|email) (.*)', function ($bot, $type, $value) {
    global $LSP;
    //<mailto:123@gmail.com|123@gmail.com>
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();

    if(preg_match("#\W#",$value)){
        if(strpos($value,'<mailto:') !== FALSE){
            $emailSplit = explode("|",$value);
            $value = $emailSplit[1];
            $value = str_replace('>','',$value);
        }else{
            $value = "";
        }
    }

    switch ($type) {
        case 'username':
            $whereSql = " WHERE users.`username` = '" . $LSP->escape($value) . "' ";
            break;
        case 'userid':
            if (!is_numeric($value)) {
                $bot->reply("<@$poster_id>: Invalid userid");
                return;
            }
            $whereSql = " WHERE users.`id` = '" . $LSP->escape($value) . "' ";
            break;

        case 'email':
            if (!$LSP->isEmail($value)) {
                $bot->reply("<@$poster_id>: $value Invalid Email Address");
                return;
            }
            $whereSql = " WHERE users.`email` = '" . $LSP->escape($value) . "' ";
            break;
    }

    if (!empty($whereSql)) {
        $query = "SELECT * FROM `users` $whereSql LIMIT 1";
        $userData = $LSP->fetch($query);
        $userData = $userData[0];
        if (empty($userData)) {
            $bot->reply("<@$poster_id>: No user found!");
            return;
        } else {
            $respText = "Found User!\n";
            $respText .= "UserID: " . $userData['id'] . "\n";
            $respText .= "Username: " . $userData['username'] . "\n";
            if ($userData['package'] == "free") {
                $respText .= "Status: Free Trial User\n";
            } elseif ($userData['expire'] < time()) {
                $respText .= "Status: Free User\n";
            } else {
                $respText .= "Status: Elite\n";
                $respText .= "Expire: " . date("d M, Y h:i:s", $userData['expire']) . "\n";
            }
        }
    } else {

        return;
    }

    $bot->reply("<@$poster_id>: " . code($respText));

});


//account renew
$botman->hears('renew list\s?(\d+)?\s?\w?\w?\w?\w?', function ($bot, $days = 7) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    if (!authUser($poster_id, 1)) {
        $bot->reply("<@$poster_id>: \n  You dont have access here chutiyaa!");
        return 0;
    }

    $sql = "SELECT type, accExp, login FROM plugins_accounts WHERE accExp > 0";
    $expAccs = $LSP->fetch($sql);
    $aboutToExp = array();
    if (!empty($expAccs)) {
        foreach ($expAccs as $acc) {
            if(is_numeric($acc['accExp']) && $acc['accExp'] > 0){
                $exp = new DateTime();
                $exp->setTimestamp($acc['accExp']);
                $acc['accExp'] = $exp->format("d-m-Y h:i:s");
                try {
                    $today = new DateTime();
                    $accExp = new DateTime($acc['accExp']);
                    $dayLeft = $today->diff($accExp)->days;
                    if ($dayLeft < $days) {
                        $aboutToExp[$acc['type']] += 1;
                    }
                } catch (Exception $e) {

                }
            }
        }
    }
    $resp = "";
    foreach ($aboutToExp as $host => $numbers) {
        $resp .= $host . ": " . $numbers . "\n";
    }

    $bot->reply("<@$poster_id>: \n  Accounts will expire in $days days!\n" . code($resp));
});

$botman->hears('show slack userid', function ($bot) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    $bot->reply("<@$poster_id>: \nUserid: " . $poster_id);
});

$botman->hears('remind sufy', function ($bot) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    $respText = "> Do the tasks from our messages here
> Report bugs with exact details
> Renew expired accs
> Keep excel file updated
> Do check helpdesk
> Check bugs
> Add more acc if all acc goes down & ask for fund if you run out of money
> Dont add any fucking accounts on the blue ips, just replace using view accounts page and recheck
> I told you to send screenshots of fucking all tickets you answer from now on
> All the docs you send on telegram should be on goolge drive as well and vice versa
> accs n chat summary or receipts, grammar correction center, check bug reports
> before posting responses, use your fucking brain, dont leave customer waiting for 2 days without response, read what I fucking say in telegram and make a note for it, show me a screenshot of ticket before posting";

    $bot->reply($respText);
});

$botman->hears('!say (.*)', function ($bot, $say) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    if (!authUser($poster_id, 3)) {
        $bot->reply("<@$poster_id>: \n  You dont have access here chutiyaa!");
        return 0;
    }

    $bot->reply($say);
});

$botman->hears('clear iplimits', function ($bot, $plugin) {
    global $LSP;
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    if (!authUser($poster_id, 3)) {
        $bot->reply("<@$poster_id>: \n you dont have permission for this command!");
        return 0;
    }
    $LSP->query("TRUNCATE ip_downloadHistory");

    $bot->reply("<@$poster_id>: Cleared ip limits");
});

$botman->hears('channel info', function ($bot) {
    $poster_id = $bot->getMessage()->getUser();
    $channel_id = $bot->getMessage()->getChannel();
    $bot->reply($channel_id);
});


$loop->run();

?>
