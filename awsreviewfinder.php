<?php
/*
Plugin Name: AWS Product Review And Rating Finder
Plugin URI: 
Description: This plugin will collect the review and rating from amazon
Version: 0.1
Author: Seema Kumari
Author Email: seemi1026@gmail.com
License:
*/

require_once("class.php");
require_once("config.php");
require_once("simple_html_dom.php");
require_once("uagent.php");


add_action( 'admin_menu', 'awsreviewfinder_menu_page' );

function awsreviewfinder_menu_page(){
	add_menu_page( 'AWS Review Finder', 'AWS Review Finder', 'manage_options', 'awsreviewfinder-settings','awsreviewfinder_settings');
}

function awsreviewfinder_settings(){
	require_once('views/settings.php');
}

function awsreviewfinder_enqueue_script() {   
    wp_enqueue_script( 'tester_javascript', plugin_dir_url( __FILE__ ) . 'js/tester.js' ,array(),'1.0',true);
}
add_action('admin_enqueue_scripts', 'awsreviewfinder_enqueue_script');

function awsreview_action_do_ajax_request(){
		
$response = array("error" => "Error parsing request. Ensure you've provided the necessary parameters.");

if (isset($_GET["profile_url"]) && isset($_GET["asin"]))
{
	$get_stars = filter_input(INPUT_GET, "get_stars", FILTER_VALIDATE_BOOLEAN);
	//$response = array("profileUrl" => $_GET["profile_url"], "asin" => $_GET["asin"], "stars" => $get_stars);
	$review = new AWSReview();
	$url = $review->strip_ref(trim($_GET["profile_url"]));
	$response = $review->get_users_product_review($profile_url, trim($_GET["asin"]), $get_stars);
	
	echo $url;
} elseif (isset($_GET["review_url"]) && isset($_GET["get_profile"]))
{
	$get_stars = filter_input(INPUT_GET, "get_stars", FILTER_VALIDATE_BOOLEAN);
	//$response = array("reviewUrl" => $_GET["review_url"], "stars" => $get_stars);
	$review = new AWSReview();
	$url = $review->strip_ref(trim($_GET["review_url"]));
	$response = $review->get_reviews_user_profile($review_url, $get_stars);
	
	echo $url;
} elseif(isset($_GET["review_url"]))
{
	$get_stars = filter_input(INPUT_GET, "get_stars", FILTER_VALIDATE_BOOLEAN);
	if ($get_stars) {
		$review = new AWSReview();
		$url = $review->strip_ref(trim($_GET["review_url"]));
		$rating = $review->get_stars($review_url);
		$response = array("rating" => $rating);
	}
}

//return JSON response
header('Content-Type: application/json');
exit(json_encode($response));
}

add_action( 'wp_ajax_awsreview_action', 'awsreview_action_do_ajax_request' );
add_action( 'wp_ajax_nopriv_awsreview_action', 'awsreview_action_do_ajax_request' );

function get_profile_links(){
	
//Retrieves a profile link for a valid review link.	
	
	global $wpdb;
	$review_table = "wp_review_test";
	$reader_table = "wp_reader_test";
			
	$results = $wpdb->get_results("SELECT review_id,book_review,reader_id FROM $review_table WHERE book_review IS NOT NULL");
			
		foreach ($results as $result){
						
			$review_id = $result->review_id;
			$book_review = $result->book_review;

			$review = new AWSReview();
			$review_url = $review->strip_ref(trim($book_review));
			$response = $review->get_reviews_user_profile($review_url);
			$profile = $response[profileUrl];
			
			if ($response == 0){
				continue;
			}
			
			else{
				
				
				$wpdb->update($review_table,
					array('profile_link' => $profile),			
					array('review_id' => $result->review_id ),
					array('%s'),
					array('%d')
				);				
						
			}
			sleep(1);
		}
		
}

function get_review_links(){

//Retrieves review link from Amazon for valid profile link and ASIN. Also returns star rating via get_stars function.
	
	global $wpdb;

	$source_table = "wp_reader_test";	
	$test_table = "wp_review_test";
	$book_table = "wp_arc_books";

	$results = $wpdb->get_results("SELECT * FROM $source_table");
				
	foreach ($results as $result){
		
		$reader_id = $result->ID;
		$profile_link = $result->profile_link;
		
		$books =  $wpdb->get_results("SELECT * FROM $test_table JOIN $book_table ON $test_table.book_id = $book_table.ID WHERE $test_table.reader_id = '$reader_id' and $test_table.status='' Limit 0,5");
		
		foreach($books as $book){
			
			$asin = $book->book_asin;
			$review_id = $book->review_id;
						
			$review = new AWSReview();

			$rating="";
			
			if($book->book_review){
				$url = $book->book_review;
				$response = $review->get_reviews_user_profile($url, true);	
			
				if(@$response['rating']){
					$rating=$response['rating'];

					$wpdb->update($test_table,
						array('star_rating'=>$rating,'status'=>'processed'),			
						array('review_id' => $review_id),
						array('%s'),
						array('%d')
					);
					continue;
				}else{
					$wpdb->update($test_table,
						array('star_rating'=>$rating,'status'=>'failed - review link invalid '),			
						array('review_id' => $review_id),
						array('%s'),
						array('%d')
					);
				}
			}
			

			$profile_url = $review->strip_ref(trim($profile_link));
			$response = $review->get_users_product_review($profile_url, trim($asin));
			$rating="";
			$review_url="";
			if($response[reviewLinks]){
				$review_url = $response[reviewLinks][0][url];
				$rating = $review->get_stars($review_url);
										
				if ( !empty($review_url) && $review_url !== 0 ){
					
					$wpdb->update($test_table,
						array('book_review' => $review_url,'status'=>'processed'),			
						array('review_id' => $review_id),
						array('%s'),
						array('%s'),
						array('%d')
					);
					
				}else{
					$wpdb->update($test_table,
						array('status'=>'failed | '.serialize($response)." | ".$profile_url." | ".trim($asin)),			
						array('review_id' => $review_id),
						array('%s'),
						array('%d')
					);
				}
			}else{
				$wpdb->update($test_table,
						array('status'=>'failed | '.serialize($response)." | ".$profile_url." | ".trim($asin)),			
						array('review_id' => $review_id),
						array('%s'),
						array('%d')
					);
			}
			
			if ( !empty($rating) && $rating !== 0 ){
				
				$wpdb->update($test_table,
					array('star_rating' => $rating,'status'=>'processed','book_review'=>$review_url),			
					array('review_id' => $review_id),
					array('%d'),
					array('%s'),
					array('%s'),
					array('%d')
				);
				
			}
					sleep(1);	
		}
	
	}
	
}


if(isset($_GET['get_review_links'])){

	get_review_links();
}else if(isset($_GET['run_get_review_links'])){
	$pid=file_get_contents("pid.txt");
	
	if(file_exists("/var/run/".$pid)){
		exec("kill -9 "."/var/run/".$pid);
	}

	exec("wget http://localhost/wordpress/2017/05/13/hello-world/?get_review_links=true >/dev/null 2>&1 & echo $!",$output);

	file_put_contents("pid.txt",$output[0]);
	die;
}else if(isset($_GET['pidcheck'])){
	$pid=file_get_contents("pid.txt");
	
	if(file_exists("/var/run/".$pid)){
		//exec("kill -9 "."/var/run/".$pid);
	}else{
		exec("wget http://localhost/wordpress/2017/05/13/hello-world/?get_review_links=true >/dev/null 2>&1 & echo $!",$output);
		file_put_contents("pid.txt",$output[0]);
	}
}else if(isset($_GET['updateinvalid'])){
	global $wpdb;
	
	$wpdb->query("update `wp_review_test` set status='' where status not like '%asin%' and status like '%invalid%' and profile_link like '%com/%'");

	die;
} 

add_action( 'muplugins_loaded', 'my_plugin_override' );

function my_plugin_override() {
    die;
}
?>