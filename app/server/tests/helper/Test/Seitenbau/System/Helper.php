<?php
namespace Test\Seitenbau\System;

class Helper
{
  public static function user_proc_exec($command)
	{
    $process = proc_open(
      $command,
      array(
        0 => array("pipe", "r"), //STDIN
        1 => array("pipe", "w"), //STDOUT
        2 => array("pipe", "w"), //STDERR
      ),
      $pipes
    );
    $result = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    $result = explode("\n",trim($result));
    return(array($error, $result, $status));
	}
}