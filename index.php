<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
 
require 'vendor/autoload.php';
 
$app = new Slim\App();


$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->write("Hello, " . $args['name']);
    return $response;
});

//fetch all data
$app->get('/customers', function ($request, $response, $args) {
	$sql = "select * FROM users";
	try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $emp = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
       return json_encode($emp);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->function() .'}}';
    }
    //$response->write("Hello, " . $args['name']);
    //return $response;
});

// fetch single data
$app->get('/onecust/{id}', function ($request, $response, $args) {
	$userid = 0;
    $userid =  $request->getAttribute('id');
    if(empty($userid)) {
                echo '{"error":{"text":"Id is empty"}}';
    }
    try {
        $db = getConnection();
        $sth = $db->prepare("SELECT * FROM users WHERE uid=$userid");
        //$sth->bindParam("id", $args['id']);
        $sth->execute();
        $todos = $sth->fetchObject();
        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->function() .'}}';
    }
});

// add user
$app->get('/insertuser/{name}/{email}/{password}', function ($request, $response, $args) {

$name =  $request->getAttribute('name');
$email =  $request->getAttribute('email');
$password =  $request->getAttribute('password');
$sql = "INSERT INTO users (name, email, password) VALUES ('".$name."', '".$email."', '".$password."')";
  try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $ss = $db->lastInsertId();
        $db = null;
        return json_encode($ss);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->function() .'}}';
    }

});

//delete user
$app->get('/deleteuser/{id}', function ($request, $response, $args) {
	$userid = 0;
    $userid =  $request->getAttribute('id');
    //return ($userid);
    if(empty($userid)) {
                echo '{"error":{"text":"Id is empty"}}';
    }
    $sql = "DELETE FROM users WHERE uid=$userid";
    //return ($sql);
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $todos = "successfully! deleted Records";
        return json_encode($todos);
    } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->function() .'}}';
    }
});

// update user
$app->get('/updateuser/{name}/{email}/{password}/{id}', function ($request, $response, $args) {

$name =  $request->getAttribute('name');
$email =  $request->getAttribute('email');
$password =  $request->getAttribute('password');
$id =  $request->getAttribute('id');

//$sql = "INSERT INTO users (name, email, password) VALUES ('".$name."', '".$email."', '".$password."')";
$sql = "UPDATE users SET name='".$name."', email='".$email."', password='".$password."' WHERE uid='".$id."'";
  try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $dd="updated userid is: ";
        $ss = $db->lastInsertId();
        $db = null;
        return json_encode($dd." ".$id);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->function() .'}}';
    }

});

function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="";
    $dbname="slimdb";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
 
$app->run();
