<?php
/**
 * Konfiguracja emaili
 */
$emails = [
    'mail@youremail.com',
];

/**
 * Lista IP do sprawdzania
 */
$ips = [
    '123.123.123.123',
    '124.1214.124.124',
   
];

/**
 * DNS Blacklisty
 */
$dnsbl = [
    'zen.spamhaus.org',
    'bl.spamcop.net',
    'dnsbl.sorbs.net',
    'b.barracudacentral.org',
    'dnsbl-1.uceprotect.net',
    'dnsbl-2.uceprotect.net',
    'dnsbl-3.uceprotect.net',
    'psbl.surriel.com',
    'ubl.unsubscore.com'
];

$date = date('Y-m-d H:i:s');

/**
 * Sprawdzanie DNSBL
 */
function checkBlacklist($ip, $dnsbl) {
    $reverseIp = implode('.', array_reverse(explode('.', $ip)));

    $listedOn = [];

    foreach ($dnsbl as $host) {
        if (checkdnsrr($reverseIp . '.' . $host, 'A')) {
            $listedOn[] = $host;
        }
    }

    return [
        'listed' => count($listedOn),
        'lists' => $listedOn
    ];
}

$report = "Raport blacklist IP\n";
$report .= "Data sprawdzenia: $date\n";
$report .= "====================================\n\n";

$totalListedIPs = 0;
$totalCleanIPs = 0;

foreach ($ips as $ip) {

    $result = checkBlacklist($ip, $dnsbl);

    if ($result['listed'] > 0) {
        $totalListedIPs++;
        $status = "⚠️ NA BLACKLIŚCIE";
    } else {
        $totalCleanIPs++;
        $status = "OK";
    }

    $report .= "IP: $ip [$status]\n";

    if ($result['listed'] > 0) {
        $report .= "Listed on:\n";
        foreach ($result['lists'] as $list) {
            $report .= " - $list\n";
        }
    } else {
        $report .= "Brak wpisów w DNSBL\n";
    }

    $report .= "------------------------------------\n";
}

/**
 * Podsumowanie
 */
$report .= "\n====================================\n";
$report .= "PODSUMOWANIE\n";
$report .= "====================================\n";
$report .= "Sprawdzonych IP: " . count($ips) . "\n";
$report .= "Na blacklist: $totalListedIPs\n";
$report .= "Czyste IP: $totalCleanIPs\n";

$subject = "Raport blacklist IP - $date";

if ($totalListedIPs > 0) {
    $subject = "🚨 BLACKLIST ALERT ($totalListedIPs) - $date";
}

$headers = "From: monitor@youremail.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if ($totalListedIPs > 0) {
    foreach ($emails as $email) {
        mail($email, $subject, $report, $headers);
    }
}