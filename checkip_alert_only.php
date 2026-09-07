<?php
/**
 * Konfiguracja emaili
 */
$emails = [
    'obsluga@cba.pl',
];

/**
 * Lista IP do sprawdzania
 */
$ips = [
    '95.211.144.65',
    '95.211.144.66',
    '95.211.144.67',
    '95.211.144.68',
    '95.211.144.69',
    '77.79.221.129',
    '77.79.221.161',
    '95.211.187.137',
    '95.211.187.139',
    '95.211.187.141',
    '95.211.206.228',
    '85.17.26.67',
    '85.17.26.66',
    '85.17.26.65',
    '82.192.84.123',
    '81.171.31.230',
    '81.171.31.232',
    '37.48.70.198',
    '37.48.70.196',
    '37.48.70.202',
    '95.211.185.109',
    '95.211.95.227',
    '95.211.191.52',
    '82.192.84.102',
    '37.48.70.83',
    '37.48.121.82',
    '212.32.255.13',
    '212.32.255.41',
    '212.32.255.139',
    '212.32.255.164',
    '5.79.66.145',
    '37.48.72.4',
    '37.48.72.5',
    '37.48.72.6',
    '37.48.72.7',
    '95.211.16.66',
    '95.211.16.67',
    '95.211.16.71'
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

$headers = "From: monitor@buz.cba.pl\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if ($totalListedIPs > 0) {
    foreach ($emails as $email) {
        mail($email, $subject, $report, $headers);
    }
}