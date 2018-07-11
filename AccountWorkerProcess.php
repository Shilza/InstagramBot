<?php

require 'vendor/autoload.php';

use InstagramAPI\Instagram;

use Repository\AccountsRepository;
use Repository\UsersRepository;
use Util\AccountWorker;

use Util\Logger;

$maxPointsCount = 0;
const BREAK_TIME = 3600; //1 HOUR
const DAY = 86400;
$maxDailyPointsCount = 0;

$id = $argv[1];

switch ($argv[2]){
    case 2:
        $target = "deleteCommentsByBot";
        break;
    case 3:
        $target = "unfollowFromAll";
        break;
    case 4:
        $target = "unfollowFromUnfollowers";
        break;
    default:
        exit();
}

Logger::logToConsole("AccountWorkerProcess $target started with ID " . $argv[1]);
Logger::setFilePath("awFollowsProcess$id");

$account = AccountsRepository::getBy(['id' => $id])[0];
$maxPointsCount = 0;

try {
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = new Instagram(false, false);
    $instagram->login($user->getLogin(), $user->getPassword());

    if ($account->getDailyPointsCount() == 0)
        $account->setLimitTime(time() + DAY);
    else if ($account->getLimitTime() < time()) {
        $account->setDailyPointsCount(0);
        $account->setLimitTime(time() + DAY);
    }

    $accountWorker = new AccountWorker($instagram, $argv[2]);
    $maxPointsCount = $accountWorker->getMaxPointsCount();
    Logger::setFilePath("$target" .$instagram->account_id);
    $accountWorker->$target();
    Logger::logToConsole("AccountWorkerProcess target $target with ID $id finished");
}
catch (\Exception $e) {
    Logger::log("AccountWorker process crush: " . $e->getMessage() . "\n" . $e->getTraceAsString());
} finally {
    $account->setDailyPointsCount(
        $account->getDailyPointsCount() + $maxPointsCount - $accountWorker->getMaxPointsCount()
    );

    $account->setTime($account->getLimitTime());

    $account->setInProcess(false);
    AccountsRepository::update($account);
    Logger::logToConsole("Finally-block accountWorker $target has reached with ID $id");
}