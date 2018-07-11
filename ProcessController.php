<?php

require 'vendor/autoload.php';

use Repository\AccountsRepository;

const MAX_PROCESSES_COUNT = 2;
const ACTUAL_ACCOUNTS_GET_DELAY = 120;


function createNewProcess(){
    global $processes;
    global $accounts;

    if(count($accounts) > 0) {
        $account = array_shift($accounts);

        if($account->getTarget() == 1)
            array_push($processes, proc_open(
                    "php BotProcess.php " . $account->getId(),
                    [], $pipes, null, null
                )
            );
        else if($account->getTarget() > 1)
            array_push($processes, proc_open(
                    "php AccountWorkerProcess.php " . $account->getId()
                    . " " . $account->getTarget(),
                    [], $pipes, null, null
                )
            );
        else
            return;
        
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

while(true) {
    AccountsRepository::deleteInvalidAccounts();
    $accounts = AccountsRepository::getActualAccounts();
    filterProcesses();

    if(count($accounts) != 0)
        \Util\Logger::logToConsole("Accounts in work: " . count($accounts));

    while (count($processes) < MAX_PROCESSES_COUNT)
        if (count($accounts) != 0)
            createNewProcess();
        else
            break;

    sleep(ACTUAL_ACCOUNTS_GET_DELAY);
}

