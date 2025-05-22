#!/usr/bin/php
<?php

if( !isset($argv[1]) ) {
	echo "error: need text as argument\n" ;
	exit( 1 ) ;
}

$filename = "/dev/shm/" . md5($argv[1]) . "-" . date( microtime(true) ) . ".wav" ;

shell_exec( "/usr/bin/pico2wave -w {$filename} \"{$argv[1]}\"" ) ;
shell_exec( "/usr/bin/sudo /usr/bin/amixer set PCM 85%" ) ;
shell_exec( "/usr/bin/sudo /usr/bin/play {$filename}" ) ;
unlink( $filename ) ;

?>