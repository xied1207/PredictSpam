<h1>Predict Spam</h1>
<?php
	$servername = "pearl.ils.unc.edu";
	$username = "db2_xied";
	$password = "s8VY3eQGj";
	$dbname = "db2_xied";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
		die("Connection failed: " .
					$conn->connect_error);
	}

if($_POST["email"]){
  $email = $_POST["email"];
  $insert = "INSERT INTO userInputSpam(text) VALUES('$email');";
  $insertResult = $conn->query($insert);
  $search = "SELECT id FROM userInputSpam WHERE text='$email';";
  $searchResult = $conn->query($search);
  if(($searchResult->num_rows > 0)) {
        while ($row = $searchResult->fetch_assoc())
        {
          $userInputId =$row['id'];
  		}
  }
  //here passed test
  $callGetFeatures = "CALL WekaCreateFeatures($userInputId,@textLength,@hasLinks,@isHtml,@hasSpammyWords);";
  //$getFeatures = "SELECT @textLength AS textLength, @hasLinks AS hasLinks, @isHtml AS isHtml, @hasSpammyWords AS hasSpammyWords ;";
  $featureResult= $conn->query($callGetFeatures);
  //$featureResult= $conn->query($getFeatures);
  if(($featureResult->num_rows > 0)) {
        while ($row = $featureResult->fetch_assoc())
        {
          $textLength =$row['textLength'];
          $hasLinks =$row['hasLinks'];
          $isHtml =$row['isHtml'];
          $hasSpammyWords =$row['hasSpammyWords'];
      }
  }

  mysqli_free_result($featureResult);
	mysqli_next_result($conn);

  $callDecisionTree = "CALL SpamDecisionTree($textLength,$isHtml,$hasLinks,$hasSpammyWords,@isSpammy);";
  $getResult = "SELECT @isSpammy AS finalPrediction;";
  //$treeDecision= $conn->query($callDecisionTree);
  $decision=$conn->query($callDecisionTree);
	$decision=$conn->query($getResult);

  if(($decision->num_rows > 0)) {
        while ($row = $decision->fetch_assoc())
        {
          $isSpammy =$row['finalPrediction'];
      }
  }
$insertPrediction="UPDATE userInputSpam SET prediction=$isSpammy WHERE id=$userInputId;";
$conn->query($insertPrediction);
  if($isSpammy=="1"){
    echo "It's a spam.";
  }else{
    echo "It's not a spam";
  }
  }

?>
