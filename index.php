<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Amazon API test</h3>
					</div>
					<div class="panel-body">
						<form id="form-tester">
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
							<div id="group-asin" class="form-group">
								<label for="input-asin">Pagination</label>
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