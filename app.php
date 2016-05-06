#!/usr/bin/php
<?php
require 'vendor/autoload.php';

$curl = new Curl\Curl();
$climate = new League\CLImate\CLImate();
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$useShortVersion = in_array('--short', $argv) || in_array('-S', $argv);

if (!isset($argv[1]) || strpos($argv[1], '-S') === 0 || strpos($argv[1], '--short') === 0) {
    $climate->error('You must specify an organazation as the first parameter');
    die();
}

$organazation = $argv[1];

$curl->setUserAgent('User-Agent: Awesome-Octocat-App');
$basicAuth = '';
if (getenv('githubUsername') && getenv('githubPassword')) {
    $basicAuth = getenv('githubUsername').':'.getenv('githubPassword').'@';
}
$url = 'https://'.$basicAuth.'api.github.com/orgs/'.$organazation.'/repos';
$curl->get($url);

$repositories = json_decode($curl->response);
if (isset($repositories->message)) {
    $climate->out('rate limit reached :-(');
    die();
}

usort($repositories, function ($a, $b) {
    return strtotime($b->pushed_at) - strtotime($a->pushed_at);
});
$climate->out(count($repositories));

if (!$useShortVersion) {
    foreach ($repositories as $repository) {
        $prettyData[] = [
            'name'       => $repository->name,
            'updated_at' => $repository->updated_at,
            'pushed_at'  => $repository->pushed_at,
        ];
    }
    $climate->table($prettyData);
}
