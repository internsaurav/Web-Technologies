<?php
  require_once __DIR__ . '/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
  session_start();
//  session_register('globaluser');
  //create a fb handler
  $fb = new Facebook\Facebook([
  'app_id' => '413132942383481',
  'app_secret' => '02943244238e5c295da0656504ad7163',
  'default_graph_version' => 'v2.8',
  'default_access_token' => 'EAAF3vgJGmXkBAIUg4iQkDOxBACWAW5Y3BY0liF21AZBYr4IQ5JuGmnJL3ZCwErGq1pDSSnZAIZB6FGl3rZBanmgqYgCeiLDPrZA7zxMc48gZCMZCWoUNHeU1AMh9Wd4gqr2pEdKuNK7WyMDgIwEtCwJZBuwHhWCNgL1JZAmccXUEIZBFgZDZD',
  ]);
    

//if its not a detail query call then do normal call
	if(isset($_GET)){
      
      //this is a general query call
      if($_GET['callType']=='general'){
        // get the search keyword, search type and location
		$keyword = $_GET['keyword'];
		$searchtype = $_GET['searchtype'];
		$longitude=$_GET['longitude'];
		$latitude=$_GET['latitude'];
		// echo $keyword."<br>".$type."<br>".$latitude."<br>".$longitude;
		
		//construct the query string
		
		/*
		 * code given in the document for users
		 * https://graph.facebook.com/v2.8/search?q=The_Keyword_to_be_searched&type=user
		 * &fields=id,name,picture.width(700).height(700)&access_token=Your_Access_Token
		 */
		$center = "&center=".$latitude.",".$longitude;
		if($searchtype != 'place'){
			$URL="search?q=".$_GET["keyword"]."&type=".$_GET["searchtype"]."&fields=id,name,picture.width(700).height(700)&limit=25";	
		}
		else{
			/*
			 * code given for places
			 * https://graph.facebook.com/v2.8/search?q=The_Keyword_to_be_searched&type=place
	&fields=id,name,picture.width(700).height(700)&center=latitude,longitude&access_token=Your_Access_Token
			 */
			$URL="search?q=".$_GET["keyword"]."&type=".$_GET["searchtype"]."&fields=id,name,picture.width(700).height(700)".$center."&limit=25";
		}
		
		// echo $URL;
		
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
        $_SESSION["lastResp"] = $user;
        //check for next and previous tabs
        $nextPage = ($fb->next($user)==null)?false:true;
        $prevPage = ($fb->previous($user)==null)?false:true;
        //assign this result to environment variable
        
		// echo $user;
		
		/*
		 * Extract the ID, name and picture and send to client
		 */ 
		$result = array();
		// $counter=0;
		if(count($user)!=0){
			foreach ($user as $value){
				$id=$value["id"];
				$name=$value["name"];
	        	$imageLink=$value["picture"]["url"];
				$internalResult = array();
				$internalResult['id']=$id;
				$internalResult['name']=$name;
				$internalResult['imageLink']=$imageLink;
				$internalResult = (object)$internalResult;
				// $counter++;
				array_push($result,$internalResult);
			}
          
            
          //insert the pagination details
            $pagination=array();
            $pagination['nextPage'] = $nextPage;
            $pagination['prevPage'] = $prevPage;
            array_push($result,$pagination);
          
		}
		$encoded_result = json_encode($result);
		echo $encoded_result;
      }
      
      //this is a details query call
      elseif($_GET['callType']=='details'){
        $id = $_GET["memberID"];
        $URL2=$id."?fields=id,name,picture.width(700).height(700),albums.limit(5){name,photos.limit(2){name,picture}},posts.limit(5){message,created_time}";
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
        if(isset($user2["albums"])){
          foreach ($user2["albums"] as $photoInAlbums){
          $name = $photoInAlbums['name'];
          if(isset($photoInAlbums['photos'])){
            $photos = $photoInAlbums['photos'];
            foreach ($photos as $photo){
              $image = $photo['picture'];
              $picID = $photo['id'];
              $url = $picID."?fields=images";
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
              $photo['picture']=$hdImageLink;
            }
	       }
	     }
        }
	     echo $user2;
      }//detaisls query scope ends here
      
      //pagination call
      elseif($_GET['callType']=='pagination'){
        $direction=$_GET['direction'];
        $lastResp = $_SESSION["lastResp"];
        $pageUser=null;
        if($direction=='nextPage'){
          $pageUser = $fb->next($lastResp);
        }
        else{
          $pageUser = $fb->previous($lastResp);
        }
        $nextPage = ($fb->next($pageUser)==null)?false:true;
        $prevPage = ($fb->previous($pageUser)==null)?false:true;
        $_SESSION["lastResp"]=$pageUser;
        $result = array();
		// $counter=0;
		if(count($pageUser)!=0){
			foreach ($pageUser as $value){
				$id=$value["id"];
				$name=$value["name"];
	        	$imageLink=$value["picture"]["url"];
				$internalResult = array();
				$internalResult['id']=$id;
				$internalResult['name']=$name;
				$internalResult['imageLink']=$imageLink;
				$internalResult = (object)$internalResult;
				// $counter++;
				array_push($result,$internalResult);
			}
          
          //insert the pagination details
            $pagination=array();
            $pagination['nextPage'] = $nextPage;
            $pagination['prevPage'] = $prevPage;
            array_push($result,$pagination);
          
		}
		$encoded_result = json_encode($result);
		echo $encoded_result;
        
        
      }
      
	}//isset($_GET) ends here


  
  

  /*
   $type="";
  //retain the fields
  if(isset($_GET['prevKeyWord'])){
    $_GET['keyword'] = $_GET['prevKeyWord'];
  }
  if(isset($_GET['prevType'])){
    $type = $_GET['prevType'];
  }
  if(isset($_GET['prevLocation'])){
    $_GET['location'] = $_GET['prevLocation'];
  }
  if(isset($_GET['prevDistance'])){
    $_GET['distance'] = $_GET['prevDistance'];
  }


  //First form
  if(isset($_GET["submit"])){
    $type = $_GET["type"];
    $location="";
    $distance="";
    $place="";
    
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
            echo "<td class='details'><a href='takeflight.php?id=".$value['id']."&prevKeyWord=".$prevKeyWord."&prevType=".$type."&prevLocation=".$prevLocation."&prevDistance=".$prevDistance."' >Details</a></td></tr>";
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
//      echo $name;
      $albumID = "photos".$counter;
      $link = "<a href=javascript:flipAlbum('".$albumID."')>";
//      echo $photoInAlbums."<br><br>";
      if(isset($photoInAlbums['photos'])){
        echo "<tr><td class='singleTabs'>".$link.$name."</a></td></tr>";
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
//        echo $name;
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
*/
?>
