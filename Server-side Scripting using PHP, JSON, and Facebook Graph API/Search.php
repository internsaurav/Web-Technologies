<?php
require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
$fb = new Facebook\Facebook([
  'app_id' => '1626086000739357',
  'app_secret' => 'd8473db420b1f7d0da27d2b4009aef0a',
  'default_graph_version' => 'v2.8',
  'default_access_token' => 'EAAXG6qZAyxB0BAMgQZCeBXggBLZBdZAXFeL12NBApcTbbtqn7DgheMthxxiLEXo9G9GCXGw5JHq6oKnDZBjlaUQacGLjn4MlgDAZBSP9j7RN03y6ys4pQinuR4QqVoZBlp0bXGijQZBO9ggmSf89EN47xX7uoKp7wTcZD',]);
$helper = $fb->getRedirectLoginHelper();
$permissions = ['email', 'user_likes']; // optional
$loginUrl = $helper->getLoginUrl('http://{your-website}/login-callback.php', $permissions);
echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;

?>

<html>
<head>
  <title>Facebook Search</title>
  <style>
    h1{
      font-style: italic;
      text-align: center;
      margin-top: 0;
    }
    form {
      position: absolute;
      background-color: gainsboro;
      width: 35em;
      left: 25em;
      height: 11em;
    }
    form select {
      margin-left: 2em;
    }
    hr{
      position: relative;
      top: -1em;
    }
    
  </style>
</head>
<body>
  <form name="fbData" method="post" action="">
    <h1>Facebook Search</h1><hr>
    <label for="keyword">Keyword:</label><input type="text" name="keyword" required><br>
    <label for="type">Type:</label>
    <select name="type" onchange="addOptions();">
      <option selected>users</option>
      <option>pages</option>
      <option>events</option>
      <option oninput="addOptions();">places</option>
      <option>groups</option>
    </select><br>
    <label for="location" hidden="true" class="location">Location</label><input type="text" name="location" hidden="true" class="location">
    <label for="Disctance" hidden="true" class="distance">Disctance(meters)</label><input type="text" name="Distance" hidden="true" class="distance"><br>
    <input type="submit" value="Search">
    <input type="reset" value="Clear" onclick="clearEverything();">
  </form>
  <script>
    function addOptions(){
      var formName = document.getElementsByName("fbData")[0];
      var value = formName.type.value;
      var target1=document.getElementsByClassName("location");
      var target2=document.getElementsByClassName("distance");
      if(value == "places"){
        target1[0].hidden=false;
        target1[1].hidden=false;
        target1[1].required=true;
        target2[0].hidden=false;
        target2[1].hidden=false;
        target2[1].required=true;
      }
      else{
        target1[0].hidden=true;
        target1[1].hidden=true;
        target1[1].required=false;
        target2[0].hidden=true;
        target2[1].hidden=true;
        target2[1].required=false;
      }
    }
    
    function clearEverything(){
      
    }
  </script>
  
</body>
</html>