<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<div class="container-fluid" style="margin-top:40px;">
		<div class="row">
			<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Amazon Product Review And Rating</h3>
					</div>
					<div class="panel-body">
						<form id="form-tester">
							<input type="hidden" value="awsreview_action" class="send" name="action">
							<div class="btn-group" data-toggle="buttons" style="margin-bottom: 1em;">
								<label class="btn btn-default active">
									<input type="radio" name="options" id="option-review" autocomplete="off" value="review" checked> Get review URL
								</label>
								<label class="btn btn-default">
									<input type="radio" name="options" id="option-profile" autocomplete="off" value="profile"> Get stars / profile
								</label>
							</div>
							<div id="group-profile-url" class="form-group">
								<label for="input-profile-url">Profile URL</label>
								<input type="text" class="form-control send" id="input-profile-url" name="profile_url" placeholder="Amazon profile URL">
							</div>
							<div id="group-review-url" class="form-group" style="display:none">
								<label for="input-review-url">Review URL</label>
								<input type="text" class="form-control" id="input-review-url" name="review_url" placeholder="Amazon review URL">
							</div>
							<div id="group-asin" class="form-group">
								<label for="input-asin">ASIN</label>
								<input type="text" class="form-control send" id="input-asin" name="asin" placeholder="ASIN" style="width: 300px">
							</div>
							<div id="group-paging" class="form-group">
								<label for="input-paging">Pagination</label>
								<input type="number" class="form-control send" id="input-paging" name="paging" placeholder="0" style="width: 300px">
							</div>							
							<div class="checkbox" id="group-get-profile" style="display:none">
								<label>
									<input name="get_profile" type="checkbox" class="send"> Get profile URL
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input name="get_stars" type="checkbox" class="send"> Get star rating
								</label>
							</div>
							<button type="submit" class="btn btn-default" id="button-submit">Submit</button>
						</form>
						<h4 style="margin-top: 2em;">Response</h4>
						<pre id="response" style="min-height: 4em;"></pre>
					</div>
				</div>
			</div>
		</div>
		
	</div>
	
	<div>
		<h2>Get Profile Links</h2>

				<form method="post" action="">
					<input type="submit" value="Poulate" name="get_profile_links"/>
				</form>
				<?php if ( isset($_POST['get_profile_links']) ) get_profile_links();?>	
	</div>

	<div>
		<h2>Get Review Links</h2>

				<form method="post" action="">
					<input type="submit" value="Test" name="get_review_links"/>
				</form>
				<?php if ( isset($_POST['get_review_links']) ) get_review_links();?>	
	</div>		

	<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>