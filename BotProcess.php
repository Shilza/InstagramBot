<?php

require_once 'src/Repositories/AccountsRepository.php';
require_once 'src/Entities/Account.php';

sleep(10);

AccountsRepository::update(new Account($argv[1], time()+60, false));

/*
$instagram = InstagramScraper\Instagram::withCredentials($user->getLogin(), $user->getPassword());
$instagram->login();

$geotags = [
    'California', "India", "Kiev"
];

$bots = [];

//$user = UsersRepository::getBy(['id' => $accounts[0]->getId()])[0];
if($user->getSettings()['genesis_account_bot_selected'])
    array_push($bots, new AccountsBot($instagram, $user->getSettings()));
if($user->getSettings()['hashtag_bot_selected'])
    array_push($bots, new HashtagBot($instagram, $user->getSettings()));
if($user->getSettings()['geotag_bot_selected'])
    array_push($bots, new GeotagBot($instagram, $user->getSettings(), $geotags));
*/