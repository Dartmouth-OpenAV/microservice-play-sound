#!/usr/bin/php
<?php

if( !isset($argv[1]) ) {
    echo "error: need asset file as argument\n" ;
    exit( 1 ) ;
}

shell_exec( "/usr/bin/sudo /usr/bin/amixer set PCM 85%" ) ;
if( isset($argv[2]) &&
    file_exists($argv[2]) ) {
    shell_exec( "/usr/bin/sudo /usr/bin/play {$argv[2]}" ) ;
}
shell_exec( "/usr/bin/sudo /usr/bin/play {$argv[1]}" ) ;

?>