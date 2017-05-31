<?php
class AWSReview
{
	public function get_stars($review_url)
	{
		if (!empty($review_url)) {

			$client = new simple_html_dom();

			$review_id = @substr($review_url, strrpos($review_url, '/') + 1);

			 $review_selector = 'a[name="' . $review_id . '"]';

			try {
				$agent = $this->get_data($review_url);

				$client->load($agent);

				$rating_string=0;

				foreach($client->find($review_selector) as $alt){
					$next=$alt->next_sibling()->next_sibling();
					
					$img=$next->find("img")[0];

					$rating_string=$img->alt;
				}

				return floatval(substr($rating_string, 0, 3));

			} catch (Exception $e) {
				return array("error" => "Error retrieving star rating.");
			}
		}
	}

	function get_data($Url,$savesession=false,$ref="",$changeip=false){

	    // is cURL installed yet?
	    if (!function_exists('curl_init')){
	        die('Sorry cURL is not installed!');
	    }

	    if($changeip):
	    $fp = fsockopen('127.0.0.1', 9051, $errno, $errstr, 30);
		$auth_code = 'pundir1234';
		if ($fp) {
		    //echo "Connected to TOR port<br />";
		}
		else {
		    //echo "Cant connect to TOR port<br />";
		}

		fputs($fp, "AUTHENTICATE \"".$auth_code."\"\r\n");
		$response = fread($fp, 1024);
		list($code, $text) = explode(' ', $response, 2);
		if ($code = '250') {
		    //echo "Authenticated 250 OK<br />";
		}
		else {
		    //echo "Authentication failed<br />";
		}

		fputs($fp, "SIGNAL NEWNYM\r\n");
		$response = fread($fp, 1024);
		list($code, $text) = explode(' ', $response, 2);
		if ($code = '250') {
		    //echo "New Identity OK<br />";
		}
		else {
		    //echo "SIGNAL NEWNYM failed<br />";
		    die();       
		}
		fclose($fp);
		endif;
	 
	    // OK cool - then let's create a new cURL resource handle
	    $ch = curl_init();
	 
	    // Now set some options (most are optional)
	 
	    // Set URL to download
	    curl_setopt($ch, CURLOPT_URL, $Url);
	    curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:9050");
	    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	    //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	    if($savesession){
	    	curl_setopt($ch, CURLOPT_COOKIEJAR, 'tmp.txt');
		 	
	    }else{
	    	
	    }

	    curl_setopt ($ch, CURLOPT_COOKIEFILE, 'tmp.txt');
	 
	 	if($ref){

	 		// Set a referer
	    	curl_setopt($ch, CURLOPT_REFERER, $ref);
	    	
	 	}else{
	 		// Set a referer
	    	curl_setopt($ch, CURLOPT_REFERER, $Url);
	 	}
	    
	 
	    // User agent
	    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36");
	 
	 	if($ref){
	 		curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest", "Content-Type: application/json; charset=utf-8"));
	 	}else{
	 		// Include header in result? (0 = yes, 1 = no)
	    curl_setopt($ch, CURLOPT_HEADER, 0);	
	 	}
	    
	 
	    // Should cURL return or print out the data? (true = return, false = print)
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    



	    
	 
	    // Timeout in seconds
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	 
	    // Download the given URL, and return output
	    $output = curl_exec($ch);
	 
	    // Close the cURL resource, and free system resources
	    curl_close($ch);
	 
	    return $output;
	}

	public function getOldReview($asin,$data){
		sleep(1);
		// Client will make requests
			$client = new simple_html_dom();

			$client->load($data);

			$nexturl="";

			$hasnexturl=false;
			$findresult=false;

			$urls=array();

			foreach($client->find("div.glimpse-main-pagination-trigger") as $cl){
				$hasnexturl=true;
				$u="data-url";
				$p="data-pagination-token";
				$f="data-feed-context";
				$fdid="data-feed-id";
				$nexturl=$cl->$u."?token=".$cl->$p."&context=".$cl->$f."&id=".$cl->$fdid."&preview=false&dogfood=false";
			}
			$page=1;
			$targetpagin=$_GET['paging'];
			if($nexturl){
				
				$assinlist=array();
				while($hasnexturl){
				//	echo $nexturl."<br />";
					$agent = $this->get_data($nexturl);
					$client->load($agent);

					$recordfound=0;
					foreach($client->find("div.glimpse-card") as $div){
						
						foreach($div->find('div[data-story-type=REVIEW]') as $dd){
							$tmp="data-asin";

							

							if(in_array(trim($dd->$tmp), $assinlist)){
								$hasnexturl=false;

								$error = "asin not found!";
    							throw new Exception($error);
							}else{
								$assinlist[]=trim($dd->$tmp);	
							}
							
						}

						foreach($div->find('div[data-asin="'.$asin.'"]') as $div1){
							$recordfound=1;
							$next= $div1->next_sibling();

							$tag=$next->find("a.a-link-normal");

							$a=$tag[count($tag)-1];

							global $get_stars;

							$url = substr($a->href, 0, strpos($a->href, "?"));

							

							if ($get_stars) {
								$stars = $this->get_stars($url);

								$urls[]= array("url" => $url, "rating" => $stars);
							}else{
								$urls[]= array("url" => $url);
							}

						}
					}

					if($recordfound==0){
						$hasnexturl=false;
						foreach($client->find("div.glimpse-main-pagination-trigger") as $cl){
							$hasnexturl=true;
							$u="data-url";
							$p="data-pagination-token";
							$f="data-feed-context";
							$fdid="data-feed-id";
							$nexturl=$cl->$u."?token=".$cl->$p."&context=".$cl->$f."&id=".$cl->$fdid."&preview=false&dogfood=false";
						}
					}else{
						$hasnexturl=false;
					}

					// echo $page;

					if($targetpagin){
						if($page==$targetpagin){
							$hasnexturl=false;
						}	
					}
					

					$page++;
				}

				//echo "Total count:".count($assinlist);
			}

			if(count($urls)==0){
				$error = "asin not found!";
    			throw new Exception($error);
			}

			return $urls;
	}

	public function get_users_product_review($profile_url, $asin, $get_stars = false)
	{
		$review_info = array();

		if (!empty($profile_url)) {
			// Client will make requests
			$client = new simple_html_dom();
			// Path to review link
			$review_selector = 'div.glimpse-card div[data-asin="' . $asin . '"] + div.a-row > a.a-link-normal';

			try {
				// Make request for user profile
				 $agent = $this->get_data($profile_url,false,"",true);

				$client->load($agent);
				// Acquire then decode an object containing user profile data
				$user_data ="";

				foreach($client->find("#profile-v5-desktop-vis-layout") as $obj){
					$user_data=$obj->data;
				}

				$user_data=json_decode($user_data);

				

				if (!empty($user_data)) {
					if(!empty($asin)):
					// Grab URL for user's reviews
					 $reviews_url = $user_data->activityData->feed->reviewsURL;

					// if(strlen($reviews_url)==0){
					// 	$reviews_url=$user_data->reviewsData->reviewsURL;
					// }
					
					try {
						
						$url_info = parse_url($profile_url);

						$finalurl=$url_info["scheme"]."://".$url_info["host"].$reviews_url;

						//$vdata=$this->get_data($finalurl,true);

						//$nexttokenurl="";

						// //condition for .co.uk domain
						// $pos = strpos($finalurl, ".co.uk");

						// if($pos===false){}else{
						// 	$this->get_data(str_replace("/activity_feed","",$finalurl),true);

						// 	$json= $this->get_data($finalurl."?review_offset=0",false,str_replace("/activity_feed","",$finalurl));

						// 	$json=json_decode($json);

						// 	print_r($json);die;
						// }


						// Make request for reviews
						 $agent = $this->get_data($finalurl,true);



						$client->load($agent);
												
						$urls=array();

						$recordfound=0;

						$foundstructure=false;
						foreach($client->find("div.glimpse-card") as $div){
							$foundstructure=true;
							foreach($div->find('div[data-asin="'.$asin.'"]') as $div1){
								$recordfound=1;
								$next= $div1->next_sibling();

								$tag=$next->find("a.a-link-normal");

								$a=$tag[count($tag)-1];

								global $get_stars;

								$url = substr($a->href, 0, strpos($a->href, "?"));

								

								if ($get_stars) {
									$stars = $this->get_stars($url);

									$urls[]= array("url" => $url, "rating" => $stars);
								}else{
									$urls[]= array("url" => $url);
								}
							}
						}

						if($foundstructure){
							if($recordfound==0){
								$urls=$this->getOldReview($asin,$agent);
							}

							$review_info["reviewLinks"] = $urls;
						}else{
							$review_info = array("error" => "There is a problem in HTML structure.");
						}
						

						

					} catch (Exception $e) {
						$msg="Error obtaining reviews.";

						if($e->getMessage()){
							$msg=$e->getMessage();
						}
						$review_info = array("error" => $msg);
					}
					else:
						$review_info = array("msg" => "Profile URL is valid.");
					endif;
				} else {
					
					$review_info = array("error" => "Invalid user profile.");
				}

			} catch (Exception $e) {
				$review_info = array("error" => "Error requesting the user profile.");
			}
		} else {
			$review_info = array("error" => "'profile_url' is required.");
		}

		return $review_info;
	}

	public function get_reviews_user_profile($review_url, $get_stars = false)
	{
		$review_info = array();

		if (!empty($review_url)) {

			$client = new simple_html_dom();

			$review_id = substr($review_url, strrpos($review_url, '/') + 1);
			
			$link_selector = 'a[name=' . $review_id . '] + br + div a[href*=profile]';

			try {
				$agent = $this->get_data($review_url);
				$client->load($agent);

				try {
					$profile_url_full ="";
					foreach($client->find('a[name=' . $review_id . ']') as $a){
						$next=$a->next_sibling()->next_sibling();

						foreach($next->find('a[href*=profile]') as $alink){
							$profile_url_full =$alink->href;
						}
					}

					if(strlen($profile_url_full)>0){
						$profile_url = substr($profile_url_full, 0, strpos($profile_url_full, "/ref"));
		
						$review_info["profileUrl"] = $profile_url;

						if ($get_stars) {
							$stars = $this->get_stars($review_url);
							$review_info["rating"] = $stars;
						}
					}else{
						$review_info = array("error" => "Error parsing html layout.");
					}
					
				} catch (Exception $e) {
					$review_info = array("error" => "Error obtaining the profile URL.");
				}

			} catch (Exception $e) {
				$review_info = array("error" => "Error requesting the review.");
			}
		} else {
			$review_info = array("error" => "'review_url' is required.");
		}

		return $review_info;
	}

	public function strip_ref($url) {
		//check if .html exist
		$pos=strpos($url,".html");

		if($pos===false){}else{
			$url=parse_url($url);
			parse_str($url['query'],$query);
			$url=$query['U'];
		}


		
		//remove /gp/aw with review
		$pos=strpos($url,"/gp/aw");

		if($pos===false){}else{
			$url=parse_url($url);
			
			$assin=explode("/",trim($url['path'],"/"));
			$url=$url['scheme']."://".$url['host']."/review/".$assin[4]."/?".$url['query'];
		}
		

		if (strpos($url, '/ref') !== false) {
			$url = substr($url, 0, strpos($url, "/ref"));
		}

		$url=parse_url($url);
		$url=$url['scheme']."://".$url['host'].$url['path'];


		return rtrim($url, '/');
	}
}
?>