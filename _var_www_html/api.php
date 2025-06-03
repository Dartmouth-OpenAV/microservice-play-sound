<?php

//  ____            __ _ _       _     _
// |  _ \ _ __ ___ / _| (_) __ _| |__ | |_
// | |_) | '__/ _ \ |_| | |/ _` | '_ \| __|
// |  __/| | |  __/  _| | | (_| | | | | |_
// |_|   |_|  \___|_| |_|_|\__, |_| |_|\__|
//


header( "Access-Control-Allow-Origin: *" ) ;
header( "Access-Control-Allow-Credentials: true" ) ;
header( "Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT,PATCH,DELETE,EXPORT" ) ;
header( "Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization" ) ;

if( $_SERVER['REQUEST_METHOD']=="OPTIONS" ) {
	http_response_code( 200 ) ;
	exit( 0 ) ;
}




//  ____             _   _
// |  _ \ ___  _   _| |_(_)_ __   __ _
// | |_) / _ \| | | | __| | '_ \ / _` |
// |  _ < (_) | |_| | |_| | | | | (_| |
// |_| \_\___/ \__,_|\__|_|_| |_|\__, |
//


$method = $_SERVER['REQUEST_METHOD'] ;
$request_uri = $_SERVER['REQUEST_URI'] ;
$request_uri = explode( "/", $request_uri ) ;
$device = $request_uri[1] ;
$path = implode( "/", array_slice($request_uri, 2) ) ;
$path = explode( "?", $path ) ;
$path = $path[0] ;

if( $path=="errors" &&
	$method=="GET" ) {
	get_errors() ;
}

if( $path=="play_text" &&
	$method=="POST" ) {
	play_text() ;
}

close_with_400( "unknown path" ) ;
exit( 1 ) ;




// _____                 _   _
// |  ___|   _ _ __   ___| |_(_) ___  _ __  ___
// | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
// |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
// |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/





function get_errors() {
	if( file_exists("/web/errors.json") ) {
		$errors = json_decode( file_get_contents("/web/errors.json"), true ) ;
		unlink( "/web/errors.json" ) ;
		close_with_500( $errors ) ;
	} else {
		close_with_200( "no errors" ) ;
	}
}


function play_text() {
	global $device ;

	if( trim(strtolower(getenv()['host']))==trim(strtolower($device)) ) {
        $data = get_request_body() ;
        $parsed_data = json_decode( $data, true ) ;

        if( !is_array($parsed_data) ||
            !array_key_exists('text', $parsed_data) ) {
        	close_with_400( "JSON data with 'text' key required" ) ;
	    }

	    $text = str_replace( '"', '\\"', $parsed_data['text'] ) ;

	    $extra_args = "" ;
	    if( isset($_GET['announcement_ding']) &&
	    	$_GET['announcement_ding']=="true" ) {
	    	$extra_args = "/assets/announcement_ding.wav" ;
		}

	    shell_exec( "/usr/bin/nohup /play_sound.php \"$text\" {$extra_args} &" ) ;

		close_with_200( "ok!" ) ;
	} else {
	    // accounting for failover, you want to make sure you're only playing on the home orchestrator
		// closing with 200 because it's not really an error if you're not home, that'll happen
		close_with_200( "not the right orchestrator" ) ;
	}
}




//  ____                               _     _____                 _   _
// / ___| _   _ _ __  _ __   ___  _ __| |_  |  ___|   _ _ __   ___| |_(_) ___  _ __  ___
// \___ \| | | | '_ \| '_ \ / _ \| '__| __| | |_ | | | | '_ \ / __| __| |/ _ \| '_ \/ __|
//  ___) | |_| | |_) | |_) | (_) | |  | |_  |  _|| |_| | | | | (__| |_| | (_) | | | \__ \
// |____/ \__,_| .__/| .__/ \___/|_|   \__| |_|   \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
// 			   |_|   |_|


function close_with_500( $message ) {
	global $db, $path ;

	http_response_code( 500 ) ;

	header( "Content-Type: application/json" ) ;

	$to_return = array( "success"=>false, "message"=>$message ) ;
	echo json_encode( $to_return ) ;

	exit( 1 ) ;
}


function close_with_501( $message ) {
	global $db, $path ;

	http_response_code( 501 ) ;

	header( "Content-Type: application/json" ) ;

	$to_return = array( "success"=>false, "message"=>$message ) ;
	echo json_encode( $to_return ) ;

	exit( 1 ) ;
}


function close_with_404( $message ) {
	global $db, $path ;

	http_response_code( 404 ) ;

	header( "Content-Type: application/json" ) ;

	$to_return = array( "success"=>false, "message"=>$message ) ;
	echo json_encode( $to_return ) ;

	exit( 1 ) ;
}


function close_with_400( $message ) {
	global $db, $path ;

	http_response_code( 400 ) ;

	header( "Content-Type: application/json" ) ;

	$to_return = array( "success"=>false, "message"=>$message ) ;
	echo json_encode( $to_return ) ;

	exit( 1 ) ;
}


function close_with_401( $message ) {
	global $db, $path ;

	http_response_code( 401 ) ;

	echo "Unauthorized: {$message}" ;

	exit( 1 ) ;
}


function close_with_204() {
	global $db, $path ;

	http_response_code( 204 ) ;

	exit( 1 ) ;
}


function close_with_200( $data ) {
	global $db, $path ;

	header( "Content-Type: application/json; charset=utf-8" ) ;

	echo json_encode( $data ) ;

	exit( 0 ) ;
}


function get_request_body() {
	$input = file_get_contents( "php://input" ) ;
	// $input = json_decode( $input, true ) ;
	return $input ;
}


function is_cli() {
	return php_sapi_name()==="cli" ;
}


?>
