<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Hernando Donado" />
<meta name="Description" content="This is my test for inBed.me!" />
<meta name="Keywords" content="twitter,inbed.me,inbed,nandosman,guj,mil,gujmil,hdickson88,hdickson" />
<meta name="Robots" content="index,follow" />

<link href="css/screen.css" rel="stylesheet" type="text/css" media="screen" />
<link href="includes/jquery/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="includes/jquery/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="includes/jquery/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">

//This is the number of accounts to fetch from the view
var accountNumber = 3;

//Hide errors on the html
function hideErrors(){
	$('#error').fadeOut('slow');
	$('#message').html('Some of the accounts could not be fetched.');
}

//Transfrom dates in the appropriate format
function dateFormat(date1, date2){	 
	//get difference in milliseconds
	var diffMilliseconds = date1.getTime() - date2.getTime();
	 
	//make the difference positive
	if( diffMilliseconds < 0 ) diffMilliseconds *= -1;
	
	//convert milliseconds to minutes
	var diffmins = parseInt( ( diffMilliseconds / 1000 ) / 60 );
	var diff = diffmins;
	var units = 'minutes';
	
	//convert minutes to hours
	if ( diffmins > 60 ){
		diff = parseInt( diffmins / 60 );
		units = 'hours';
	}
	
	//convert hours to days
	if ( diffmins > 24*60 ){
		diff = parseInt( diffmins / 60 / 24 );
		units = 'days';
	}
	
	//convert days to months
	if ( diffmins > 30*24*60 ){
		diff = parseInt( diffmins / 60 / 24 / 30 );
		units = 'months';
	}
	
	//convert months to years
	if ( diffmins > 12*30*24*60 ){
		diff = parseInt( diffmins / 60 / 24 / 30 / 12 );
		units = 'years';
	}
	
	//return value
	return diff+' '+units;
}

//This function will take the accounts in the view and make an ajax request for each.
function fetchAccounts(){
	
	var n = accountNumber;
	
	// On submit disable its submit button
	$( '#refresh' ).attr( 'value', 'Fetching...' );
	$( '#refresh' ).attr('disabled', 'disabled');
	
	$('#loader').fadeIn('fast');
	
	//Number of tweets to be fetched per account.
	var tweetNumber = $('#amount option:selected').val();
	
	//Initialization of tweet object
	var request = new tweetRequest(accountNumber);
	
	for (var i=0;i<n;i++){
		var account = $('#account'+(i+1)).val();
		
		if ($.trim(account)){
			//Ajax request per account
			$.ajax({
				url: 'http://api.twitter.com/1/statuses/user_timeline.json',
				dataType: 'jsonp',
				data: 'screen_name='+account+'&include_rts=1&count='+tweetNumber,
				success: function(data){		
					request.fetchComplete(data); //Fetch tweets
				},
				timeout: 2500, //2.5 secs of timeout to produce an error
				error: function(){
					request.fetchFailed(); //Notify of an error
				}
			});
		}else{
			request.accountNumber--;
			if (request.accountNumber == 0){
				$('#message').html('You must write at least one valid username.');
				$('#error').fadeIn('fast');
				setTimeout( 'hideErrors()',5000);
				
				$( '#refresh' ).attr( 'value', 'Refresh' );
				$( '#refresh' ).removeAttr('disabled');
				$('#loader').fadeOut('fast');
			}
		}
	}
	
}

//Object to control account tweet fetching
function tweetRequest(accountNumber){
	/*Atributtes*************/
	this.accountNumber = accountNumber;
	this.data = [];
	this.fetchCompleted = 0;
	this.errors = 0;
	
	/*Functions*************/
	//Sort function for tweets, chronologically
	var sortfunction = function(tweet1, tweet2){
		var time1 = new Date( tweet1['created_at'] ), 
		time2 = new Date( tweet2['created_at'] );
		
		return time2.getTime() - time1.getTime();
	};
	//Prints the sorted tweets on the html
	this.printTweets = function(){
		var items = [];
		this.data.sort(sortfunction);
		
		//Fetching tweets array, and creating html
		$.each(this.data, function(key, val) {
			var user = [];
			var today = new Date();
			var date = new Date(val['created_at']);
			user = val['user'];
		
			items.push('<li class="tweet">'+
						'<img class="profile_pic" src="'+user['profile_image_url']+'" alt="Profile Pic" height="48" width="48">'+
						'<p class="tweet_text">'+val['text']+'</p>'+
						'<p class="author">By '+user['name']+' '+dateFormat(today, date)+' ago.</p></li>');
		});
		
		//Adding li elements to a new ul tag
		$('#list').html( 
			$('<ul/>', {
			'class': 'tweet_list',
			html: items.join('')
			}) 
		);
		
		//Error handling
		if (this.errors == 1){
			$('#error').fadeIn('fast');
			setTimeout( 'hideErrors()',5000);
		}
		
		$( '#refresh' ).attr( 'value', 'Refresh' );
		$( '#refresh' ).removeAttr('disabled');
		$('#loader').fadeOut('fast');
	}
	//Tweet fetch success event
	this.fetchComplete = function(tweets){
		this.data = this.data.concat( tweets );
		this.fetchCompleted++;
		if (this.fetchCompleted == this.accountNumber)
			this.printTweets();
	}
	//Tweet fetch fail event
	this.fetchFailed = function(){
		this.errors = 1;
		this.fetchCompleted++;
		if (this.fetchCompleted == this.accountNumber)
			this.printTweets();
	}
	
}


$(document).ready(function (){
	
	$('#refresh').button().bind( 'click', function(){ fetchAccounts(); });
	
});

</script>

<title>Twitter inBed.me - Hernando Donado</title>
</head>
<body>
<div id="header">
	<h1>Tweet Explorer - inBed.me Test</h1>
    <h2>by Hernando Donado</h2>
</div>
<div id="wrapper">
    <div id="error" class="ui-state-error ui-corner-all" style="display:none;">
        <p><span class="ui-icon ui-icon-alert"></span>
        <strong>Alert:</strong>
        <span id="message">Some of the accounts could not be fetched.</span>
        </p>
    </div>
        
    <form id="account_form" method="post" action="" onSubmit="return false;">
    <p>Write up to 3 twitter account names to start fetching:</p>
	
    <p>
    	<label for="amount">Number of tweets per account:</label>
        <select id="amount" name="amount">
        	<option value="1">1</option>
            <option value="3">3</option>
            <option value="5" selected="selected">5</option>
            <option value="10">10</option>
        </select>
    </p>
    
    <label class="orange" for="account1">@</label>
    <input type="text" id="account1" name="account1" value="" />

    <label class="orange" for="account2">@</label>
    <input type="text" id="account2" name="account2" value="" />

    <label class="orange" for="account3">@</label>
    <input type="text" id="account3" name="account3" value="" />

    <input type="submit" id="refresh" name="refresh" value="Refresh" />
    <span id="loader" style="display:none;"></span>
    </form>
    
    <div id="list">
    <!-- Information will be loaded here -->
    </div>
</div>

</body>
</html>