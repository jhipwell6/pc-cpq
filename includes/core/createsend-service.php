<!--// Newsletter Automatic Signup Integration
		// create the object to add to the list
		$auth       = array('api_key' => "d5cd7650c2140b0b10ca55d29ee20d1005ed754e2ca97a1a");
		$cm         = new CS_REST_Subscribers("d38e04a14f4401af46346f4828a0c7e0", $auth); // access token
		$custom = array();
		if(isset($_POST['checkbox1']) && $_POST['checkbox1'] === "Yes" ){
		    $custom[] = array(
		            'Key' => 'Industry/CompanyNews',
		            'Value' => "on"
		    );
		    if(isset($_POST['topics'])) {
		        foreach($_POST['topics'] as $topic) {
		            $custom[] = array(
		                'Key' => str_replace(' ', '', $topic),
		                'Value' => 'on'
		            );
		        }
		    }
		}
		// // add our contact to the list.
		// // CustomFields are optional. Must set up names in the admin of the list in campaignmonitor before inserting
		$result = $cm->add(array(
		    'EmailAddress' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
		    'Name' => filter_var($_POST['name'], FILTER_SANITIZE_STRING),
		    'CustomFields' => $custom,
		    'Resubscribe' => false
		));-->