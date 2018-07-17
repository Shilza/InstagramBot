<?php

require 'vendor/autoload.php';

use Exception\WorkStoppedException;
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
        $target = "unfollowFromAllByDB";
        break;
    case 4:
        $target = "unfollowFromUnfollowers";
        break;
    case 5:
        $target = "unfollowFromAll";
        break;
    default:
        exit();
}

Logger::info("AccountWorkerProcess $target started");
Logger::setFilePath($id);

$account = AccountsRepository::getBy(['id' => $id])[0];
$maxPointsCount = 0;

try {
    $user = UsersRepository::getBy(['id' => $id])[0];
    $instagram = new Instagram(false, false);
    $accountWorker = new AccountWorker($instagram, $argv[2]);
    $instagram->login($user->getLogin(), $user->getPassword());

    if ($account->getDailyPointsCount() == 0)
        $account->setLimitTime(time() + DAY);
    else if ($account->getLimitTime() < time()) {
        $account->setDailyPointsCount(0);
        $account->setLimitTime(time() + DAY);
    }

    $maxPointsCount = $accountWorker->getMaxPointsCount();
    Logger::setFilePath($instagram->account_id);
    $accountWorker->$target();
    Logger::info("AccountWorkerProcess finished target $target");
}
catch (WorkStoppedException $e){
    $account->setOldTarget(-$account->getOldTarget());
    $account->setTarget(-$account->getTarget());
    Logger::info("AccountWorker target $target stopped");
}
catch (\Exception $e) {
    Logger::error("AccountWorker process crush: " . $e->getMessage() . "\n" . $e->getTraceAsString());
} finally {
    $account->setDailyPointsCount(
        $account->getDailyPointsCount() + $maxPointsCount - $accountWorker->getMaxPointsCount()
    );

    $account->setTime($account->getLimitTime());

    $account->setInProcess(false);
    AccountsRepository::update($account);
    Logger::info("Finally-block accountWorker $target");
}