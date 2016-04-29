#!/usr/bin/php
<?php
require 'vendor/autoload.php';

$curl = new Curl\Curl;
$climate = new League\CLImate\CLImate;

$useShortVersion = in_array('--short', $argv) || in_array('-S', $argv);

if (!isset($argv[1]) || strpos($argv[1], '-S') === 0 || strpos($argv[1], '--short') === 0) {
    $climate->error('You must specify an organazation as the first parameter');
    die();
}

$organazation = $argv[1];

$curl->setUserAgent('User-Agent: Awesome-Octocat-App');
$curl->get('https://api.github.com/orgs/'.$organazation.'/repos');

$repositories = json_decode($curl->response);
if ($repositories->message) {
    $climate->out('rate limit reached :-(');
    die();
}

usort($repositories, function($a, $b){
    return strtotime($b->pushed_at) - strtotime($a->pushed_at);
});
$climate->out(count($repositories));

if (!$useShortVersion) {
    foreach ($repositories as $repository) {
        $prettyData[] = [
            'name'        => $repository->name,
            'updated_at'  => $repository->updated_at,
            'pushed_at'   => $repository->pushed_at,
        ];
    }
    $climate->table($prettyData);
}
