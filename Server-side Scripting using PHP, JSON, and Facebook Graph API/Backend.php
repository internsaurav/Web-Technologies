<?php
  require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
//  session_start();
  //create a fb handler
  $fb = new Facebook\Facebook([
  'app_id' => '1626086000739357',
  'app_secret' => 'd8473db420b1f7d0da27d2b4009aef0a',
  'default_graph_version' => 'v2.8',
  'default_access_token' => 'EAAXG6qZAyxB0BAOnNVvlvdM2uNuV4ZAIUEMev6OQCFq5THOaFgGHJQeFjTJQZAoSrbvWUAvAsa9fnf9HEgazI2nVCkO9ct0jKLVi8VZC6Bl6E6ZA1J70ZCjMC4rtY7Ym90BYE1Whmwhmx3AZBQInCYgc7pyMhZCUbU8ZD',
  ]);

  if(isset($_GET['clear'])){
    echo "<p>reset</p>";
  }
  
$type="";
  //First form
  if(isset($_GET["submit"])){
    $type = $_GET["type"];
    $location="";
    $distance="";
    
    if($type == 'place'){
      if (!empty($_GET['location'])){
        $place = $_GET["location"];
        $encodedPlace = urlencode($place);
//      echo $encodedPlace."<br>";
        $googleURL = "https://maps.googleapis.com/maps/api/geocode/json?address=".$encodedPlace."&key=AIzaSyBsNrL6L4AlIybnA7WTrXAnAm-6ZVMH7sw" ;
//      echo $googleURL."<br>";
//      $encGoogleURL = urlencode($googleURL);
//      echo $encGoogleURL."<br>";
//      $results = file_get_contents($encGoogleURL);
        $results = file_get_contents($googleURL);
        $decoded_results = json_decode($results,true);
        //only if google api returns good values
        if(isset($decoded_results['results'][0]['geometry']['location'])){
          $latitude = $decoded_results['results'][0]['geometry']['location']['lat'];
          $longitude = $decoded_results['results'][0]['geometry']['location']['lng'];
          $location = $latitude.",".$longitude;
          $center="&center=".$location;
          if(isset($_GET["distance"])){
            $distance=$_GET["distance"];
          }
          $URL="search?q=".$_GET["keyword"]."&type=".$_GET["type"]."&center=".$location."&distance=".$distance."&fields=id,name,picture.width(700).height(700)";
          }
        else{
          $URL="search?q=".$_GET["keyword"]."&type=".$_GET["type"]."&fields=id,name,picture.width(700).height(700)";
        }
      }
      else{
        $URL="search?q=".$_GET["keyword"]."&type=".$_GET["type"]."&fields=id,name,picture.width(700).height(700)";
      }
    }
    else if($type == "event"){
      $URL="search?q=".$_GET["keyword"]."&type=".$_GET["type"]."&fields=id,name,picture.width(700).height(700),place";
    }
    else{
      $URL="search?q=".$_GET["keyword"]."&type=".$_GET["type"]."&fields=id,name,picture.width(700).height(700)";
    }
    
    try{
      $res = $fb->get($URL);
    }
    catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    }  
    catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
  
    $user = $res->getGraphEdge();
    echo "<div class='output'>";
    if(count($user)==0){
      echo "<table border='1'><tr><td class='error'>No records have been found</td></tr></table>";
    }
    else{
      echo "<table border='1'>";
      if($type != 'event'){
        echo "<th>Profile Photo</th><th>Name</th><th>Details</th>";
      }
      else{
        echo "<th>Profile Photo</th><th>Name</th><th>Place</th>";
      }
      foreach ($user as $value){
        $imageLink=$value["picture"]["url"];
//        echo $imageLink."<br><br>";
        echo "<tr><td class='pp'><a href='".$imageLink."' target='_blank'><img src=".$imageLink." height='30' width='40'></a></td>";
  //      echo "<td>".$value["id"]."</td>";
        echo "<td class='name'>".$value["name"]."</td>";
        if($type != 'event'){
          $prevKeyWord = $_GET['keyword'];
          //if type is not equal to place then just pass keywod and type
          if($type !='place'){
            echo "<td class='details'><a href='takeflight.php?id=".$value['id']."&prevKeyWord=".$prevKeyWord."&prevType=".$type."' >Details</a></td></tr>";
          }
          //if type is place then pass keyword, type, location,distance
          else{
            $prevLocation = $place;
            $prevDistance = $distance;
            echo "<td class='details'><a href='takeflight.php?id=".$value['id']."&prevKeyWord=".$prevKeyWord."&prevType=".$type."&prevLocaion=".$prevLocation."&prevDistance=".$prevDistance."' >Details</a></td></tr>";
          } 
        }
        else{
          if(isset($value['place'])){
            echo "<td class='details'>".$value['place']['name']."</td></tr>";
          }
          else{
            echo "<td class='details'></td></tr>";
          }
        }
      }
    echo "</table>";
    }
    echo "</div>";
  }

//second form

if(isset($_GET['id'])){
    $id = $_GET["id"];
    $URL2=$id."?fields=id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2){name,picture}},posts.limit(5)";
    try{
      $res2 = $fb->get($URL2);
    }
    catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    }  
    catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $user2 = $res2->getGraphObject();
    echo "<div class='output'>";
    if(isset($user2["albums"]) == false ){
      echo "<table border='1'><tr><td class='error'>No albums have been found</td></tr></table><br>";
    }
  else{
//    echo $user2;
    echo "<table border=1><th class='singleTabs'><a href=javascript:flipAlbum('albums')>Albums</a></th></table><br>";
    echo "<table border=1 id='albums' hidden='true'>";
    $counter =0;
    foreach ($user2["albums"] as $photoInAlbums){
      $counter = $counter + 1;
      $name = $photoInAlbums['name'];
      $albumID = "photos".$counter;
      $link = "<a href=javascript:flipAlbum('".$albumID."')>";
//      echo $photoInAlbums."<br><br>";
      if(isset($photoInAlbums['photos'])){
        echo "<tr><td class='singleTabs'>".$link.$name."<a></td></tr>";
        $photos = $photoInAlbums['photos'];
//        echo $photos."<br><br>";
        echo "<tr><td class='singleTabs' id='photos".$counter."' hidden=true > ";
        foreach ($photos as $photo){
//          echo $photo."<br><br>";
          $image = $photo['picture'];
          $picID = $photo['id'];
          $url = $picID."?fields=images";
//          echo $url."<br>";
          try{
              $resIMG = $fb->get($url);
            }
            catch(Facebook\Exceptions\FacebookResponseException $e) {
              echo 'Graph returned an error: ' . $e->getMessage();
              exit;
            }  
            catch(Facebook\Exceptions\FacebookSDKException $e) {
              echo 'Facebook SDK returned an error: ' . $e->getMessage();
              exit;
            }
          $hdImage = $resIMG->getGraphObject();
          $hdImageLink = $hdImage['images'][0]['source'];
          
//        $hdURL=getHDimage($picID);//gets the image
//        echo $hdURL."<br><br>";
        echo "<a href='".$hdImageLink."' target='_blank'><img src=".$image." height='80' width='80'></a>&nbsp&nbsp";
        }
        echo "</td></tr>";
      }
      else{
        echo "<tr><td class='singleTabs'>".$name."</td></tr>";
      }
    }
    echo "</table><br>";
  }
    if(isset($user2["posts"]) == false ){
      echo "<table border='1'><tr><td class='error'>No posts have been found</td></tr></table>";
    }
  else{
    echo "<table border=1><th class='singleTabs'><a href=javascript:flipAlbum('posts')>Posts</a></th></table><br>";
    echo "<table border=1 id='posts' hidden='true'><th id='messages' >Messages</th>";
    foreach ($user2["posts"] as $post){
      if(isset($post['message'])){
        $message = $post['message'];
        echo "<tr><td class='singleTabs'>".$message."</td></tr>";
      }
      else{
        $message = "(This post has no content)";
        echo "<tr><td class='singleTabs'>".$message."</td></tr>";
      }
    }
    echo "</table>";
  }
  echo "</div>";
}


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
    .output{
      position: absolute;
      left: 16em;
      top: 15em;
    }
       
    .singleTabs{
      width: 55em;
      left: 16em;
      border-color: gainsboro;
    }
    .albumsOutput{
      position: absolute;
      background-color: gainsboro;
      top: 15em;
      left: 16em;
    }
   
    .postsOutput{
      position: absolute;
      background-color: gainsboro;
      top: 20em;
      left: 16em;
    }
    td{
      background-color:aliceblue;
    }
    td.pp {
      width: 15em;
    }
    td.name {
      width: 30em;
    }
    td.details{
      width: 10em;
    }
    td.error {
      width: 55em;
      text-align: center;
    }
    th{
      background-color: gainsboro;
      text-align: left;
    }
    
    th.singleTabs{
      text-align: center;
    }
    .messages{
      background-color: aliceblue;
      text-align: left;
    }
  </style>
</head>
<body>
  <p id=sample></p>
  <form name="fbData" method="get" action="">
    <h1>Facebook Search</h1><hr>
    <label for="keyword">Keyword:</label><input type="text" name="keyword" required value="<?php echo isset($_GET["keyword"]) ? $_GET["keyword"] : '' ?>"><br>
      
    <label for="type">Type:</label>
    <select name="type" onchange="addOptions()">
      <option <?php if($type=='user' || ''){ echo 'selected=selected';} ?> value="user">users</option>
      <option <?php if($type=='page'){ echo 'selected=selected';} ?> value="page">pages</option>
      <option <?php if($type=='event'){ echo 'selected=selected';} ?> value="event">events</option>
      <option <?php if($type=='place'){ echo 'selected=selected';} ?> value="place">places</option>
      <option <?php if($type=='group'){ echo 'selected=selected';} ?> value="group">groups</option>
    </select><br>
    <label for="location" <?php if($type != 'place'){echo "hidden=true";} ?> class="location" >Location</label><input type="text" name="location" <?php if($type != 'place'){echo "hidden=true";} ?> class="location" value="<?php echo isset($_GET["location"]) ? $_GET["location"] : '' ?>">
    <label for="Disctance" <?php if($type != 'place'){echo "hidden=true";} ?> class="distance">Disctance(meters)</label><input type="text" name="distance" <?php if($type != 'place'){echo "hidden=true";} ?> class="distance" value="<?php echo isset($_GET["distance"]) ? $_GET["distance"] : '' ?>"><br>
    <input type="submit" name ="submit" value="Search">
    <input type="button" name = "clear" value="clear" onclick="clearEverything()">
  </form>
  <script>
    function clearEverything(){
      //delete the attribute
      var options = document.getElementsByTagName("option");
      for(var i=0; i<options.length; i++){
        if(options[i].selected=="selected"){
          options[i].removeAttribute("selected");
        }
      }
      //set the default to user
      options[0].selected="selected";
      
      //clear the keyword,location and distance
      document.getElementsByName("keyword")[0].value="";
      document.getElementsByName("location")[0].value="";
      document.getElementsByName("distance")[0].value="";
      
      //hide location and distance
      document.getElementsByName("location")[0].hidden=true;
      document.getElementsByName("distance")[0].hidden=true;
      document.getElementsByTagName("label")[2].hidden=true;
      document.getElementsByTagName("label")[3].hidden=true;
      
      
      //clear the output
      var everything = document.getElementsByClassName("output");
      for (var i=0; i<everything.length; i++){
        everything[i].innerHTML="";
      }
      
      
    }
    function flipAlbum(idofalbum){
      var photos = document.getElementById(idofalbum);
      var status = photos.hidden;
      photos.hidden=!status;
    }
    function addOptions(){
      var formName = document.getElementsByName("fbData")[0];
      var value = formName.type.value;
      var target1=document.getElementsByClassName("location");
      var target2=document.getElementsByClassName("distance");
      if(value == "place"){
        target1[0].hidden=false;
        target1[1].hidden=false;
        target2[0].hidden=false;
        target2[1].hidden=false;
      }
      else{
        target1[0].hidden=true;
        target1[1].hidden=true;
        target2[0].hidden=true;
        target2[1].hidden=true;
      }
    }
    
    function retainValue(option){
      document.getElementById("sample").innerHTML="Yo";
      var options = document.getElementsByTagName("option");
      for (var i=0; i<options.length; i++){
        var value = options[i].value;
        if(value == option){
          document.getElementsByTagName("option")[i].selected="true";
          break;
        }
      }
    }
    
    
    
  </script>
</body>
</html>
