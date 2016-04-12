<?
chdir(dirname(__DIR__));
error_reporting(1);
session_start();

require_once 'vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
function sqlConnect(){
	$servername = "localhost";
	$username = "root";
	$password = "root";
	$dbname = "quizzit";
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	return $conn;
}
function lastMinutes($minutes){
	

	$conn = sqlConnect();

	$sql = "SELECT *
FROM comments
WHERE timestamp >= NOW() - INTERVAL $minutes minute
ORDER BY timestamp DESC";
	
	if (!$conn->query($sql)) {
    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}


	$res = $conn->query($sql);
	return $res->num_rows;	

}
function update(){

		$conn = sqlConnect();


   /** @var $client Client */
    $client = new Client("https://www.reddit.com");
    $client->setUserAgent('Getting all new comments', true);

	/** @var $request Request */
    $request = $client->get('/r/all/comments.json?limit=1000');

	/** @var $response Response */
    $response = $request->send();

	/** @var $body EntityBody */
    $body = $response->getBody(true);
    $body = json_decode($body);
	//var_dump(json_decode($body));
    //var_dump($body->data->children);

	foreach ($body->data->children as $key => $value) {
		$commentbody = mysqli_real_escape_string($conn,$value->data->body);
		$linktitle = mysqli_real_escape_string($conn,$value->data->link_title);
		$subreddit = $value->data->subreddit;
		$comment_id = $value->data->id;

		$sql = "INSERT INTO comments (comment,title,sub,comment_id)
				VALUES ('$commentbody','$linktitle','$subreddit','$comment_id')"
        			;

		if ($conn->query($sql) === TRUE) {
		    //echo "New record created successfully";
		} else {
		    echo "Error: " . $sql . "<br>" . $conn->error;
		}

		
	}
	//echo "Updated Database.<br><br>";
	$conn->close();

}
function updateIfStale(){
	if (lastMinutes(15)<50) {
		update();
		//echo("updated db with new data");
	}
}
function returnRandomRowFromMinutes($minutes){

		$conn = sqlConnect();



	$sql = "SELECT *
FROM comments
ORDER BY rand() LIMIT 1";
	
	if (!$conn->query($sql)) {
    echo "Random Row failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}


	$res = $conn->query($sql);
	$res->data_seek(0);
	while ($row = $res->fetch_assoc()) {
	    echo json_encode($row);
	}
		$conn->close();

}
function getRandomSubreddits($amount){
		$conn = sqlConnect();

	// Check connection
	$sql = "SELECT DISTINCT sub FROM comments ORDER BY rand() LIMIT $amount";


	$res = $conn->query($sql);
	$res->data_seek(0);
	$subreddits = [];
	$i=0;
	while ($row = $res->fetch_assoc()) {
	    $subreddits[$i] = $row['sub'];
	    $i+=1;
	}
	echo json_encode($subreddits);
	$conn->close();

}
function submitAnswer($comment_id,$answer,$solution){
		$conn = sqlConnect();

	// Check connection
	$sql = "INSERT INTO history (comment_id,answer,solution,user)
				VALUES ('$comment_id','$answer','$solution','".$_SESSION['user_name']."')"
        			;
	if ($conn->query($sql) === TRUE) {
		    echo "New history created successfully";
		} else {
		    echo "Error: " . $sql . "<br>" . $conn->error;
		}	
	
	//echo "Updated Database.<br><br>";
	$conn->close();
}
function getGlobalScore(){

	$conn = sqlConnect();

	// Check connection
$sql="SELECT `id`, COUNT(*) AS TOTAL, COUNT(IF(`answer`=`solution`,1,null) ) AS T, COUNT(IF(`answer`!=`solution`,1,null) ) AS F FROM history";

	$res = $conn->query($sql);
	$res->data_seek(0);
	while ($row = $res->fetch_assoc()) {
	    echo json_encode($row);
	}
	$conn->close();
}

function getPlayerScore(){

	$conn = sqlConnect();

	// Check connection
$sql="SELECT `id`, COUNT(*) AS TOTAL, COUNT(IF(`answer`=`solution`,1,null) ) AS T, COUNT(IF(`answer`!=`solution`,1,null) ) AS F FROM history WHERE user = '".$_SESSION['user_name']."'";

	$res = $conn->query($sql);
	$res->data_seek(0);
	while ($row = $res->fetch_assoc()) {
	    echo json_encode($row);
	}
	$conn->close();

}




updateIfStale();
if (isset($_GET["x"])) {
	if ($_GET["x"] === "1") {
		returnRandomRowFromMinutes(60);
	}
	if ($_GET["x"] === "2") {
		getRandomSubreddits(3);
	}
	if ($_GET["x"] === "3") {
		submitAnswer($_GET["i"],$_GET["a"],$_GET["s"]);
	}
	if ($_GET["x"] === "4") {
		getGlobalScore();
	}
	if ($_GET["x"] === "5") {
		getPlayerScore();
	}

}


?>
