<?php

require 'vendor/autoload.php';
require_once 'src/Entities/Account.php';
require_once 'src/Repositories/AccountsRepository.php';
require_once 'src/Bots/AccountsBot.php';
require_once 'src/Bots/HashtagBot.php';
require_once 'src/Bots/GeotagBot.php';
require_once 'src/Repositories/UsersRepository.php';

const MAX_PROCESSES_COUNT = 5;


function createNewProcess(){
    global $processes;
    global $accounts;
    global $proxies;

    if(count($accounts) > 0) {
        $account = array_shift($accounts);

        $proxyArray = [];
        foreach ($proxies as &$proxy)
            if(!$proxy['isUsed']){
                $proxyArray = $proxy;
                $proxy['isUsed'] = true;
                break;
            }

        array_push($processes, proc_open(
                'php BotProcess.php ' . $account->getId() . ' ' . $proxyArray['ip'] . ' ' . $proxyArray['port'] ,
                [], $pipes, null, null
            )
        );


        $account->setInProcess(true);
        AccountsRepository::update($account);
    }
}

function filterProcesses(){
    global $processes;

    foreach ($processes as &$process)
        if(!proc_get_status($process)['running'])
            $process = null;

    $processes = array_filter($processes);
}

$processes = [];
$accounts = [];
$proxies = [
    ['ip' => '206.81.2.4', 'port' => '808', 'isUsed' => false],
    ['ip' => '183.88.212.141', 'port' => '8080', 'isUsed' => false]
];

while(true) {
    $accounts = AccountsRepository::getActualAccounts();
    filterProcesses();

    echo count($accounts)."\n";
    while (count($processes) < MAX_PROCESSES_COUNT)
        if (count($accounts) != 0)
            createNewProcess();
        else
            break;

    sleep(1);
}

