<?php

/*
*	Accepts CafeMom CSV report. Genenerates reports. Outputs in csv or json.
*	- Wesley D. Radcliffe
*
*/

class PostSorter{

	const MIN_COMMENTS = 10;
	const MIN_VIEWS = 9000;
	const MAX_LENGTH = 40; 

	private $_output_format;
	private $_output_type;
	private $_read_file_pointer;
	private $_post_data;
	private $_csv_header;
	private $_top_posts;
	private $_other_posts;
	private $_daily_best;

	public $status;
	public $post_count;


	//attempt to load file, throw exception if file not found
	function __construct($file_location, $output_format="csv", $output_type="quick"){
		global $CFG;

		$this->_output_type = $output_type;
		$this->_output_format = $output_format;

		try{
			$this->_read_file_pointer = fopen($file_location,"r");
		}catch(Exception $e){
			echo "Could not open specified file " . $e->getMessage();
		}
		
		$this->post_count = 0;
		$header_row = false;
		
		while (($post = fgetcsv($this->_read_file_pointer, 5000, ",")) !== FALSE) {
			
			//yank out the header row
			if( !$header_row )
			{
			    foreach( $post as $key => $value )
			    {
				    $key_array[strtolower($value)] = trim($key);
				    //print_ar( $value );
				    
			    }
			    $this->_csv_header = $post;
			    $header_row = true;
			    //echo "<pre>" . print_r($key_array) . "</pre>";
			    continue;
			}
			//end header row

			$this->post_count ++;
			$data['id'] = $post[$key_array['id']];
			$data['title'] = $post[$key_array['title']];
			$data['privacy'] = $post[$key_array['privacy']];
			$data['likes'] = $post[$key_array['likes']];
			$data['views'] = $post[$key_array['views']];
			$data['comments'] = $post[$key_array['comments']];
			$data['timestamp'] = $post[$key_array['timestamp']];
			$this->_post_data[] = $data;
 
	    }

	    // echo $this->post_count . " posts loaded";
	    fclose($this->_read_file_pointer);
	    //echo "<pre>" . print_r($this->_post_data) . "</pre>";

	}


	/*
	* Top Posts Rules:
	* The post must be public 
	* The post must have over 10 comments and over 9000 views
	* The post title must be under 40 characters
	*
	*	starting by looping through our dataset and pushing proper fields to a return array
	*	should we usort?
	*	other posts is not top (so run it at the same time);
	*	best of is top top (so run it at the same time);
	*/
	public function generateReports(){

		if(is_array($this->_post_data)){
			foreach($this->_post_data as $key=>$post){

				//first test - public posts 
				if($post['privacy'] == "private"){
					$this->_other_posts[] = $post;
					continue;
				}

				if($post['comments'] > self::MIN_COMMENTS && $post['views'] > self::MIN_VIEWS && strlen($post['title']) < self::MAX_LENGTH){
					$this->_top_posts[] = $post;
				}else{
					$this->_other_posts[] = $post;
				}

			}
		}

		//daily best is one of the top comments
		usort($this->_top_posts, function($a, $b) {
		    return $a['likes'] - $b['likes'];
		});
		$this->_daily_best[] = end($this->_top_posts);
		//print_r($this->_daily_best);

	}

	public function getTopPostsOutput(){
		global $CFG;

		if($this->_output_format == "csv"){//open file. dump using csv
			$output_file_location = $CFG->datadir . "/top_posts.csv";
			$this->_outputCSV($output_file_location,$this->_top_posts);

		}else{ //spit out JSON
			$output_file_location = $CFG->datadir . "/top_posts.json";
			$this->_outputJSON($output_file_location,$this->_top_posts);
		}

	}

	public function getOtherPostsOutput(){
		global $CFG;

		if($this->_output_format == "csv"){//open file. dump using csv
			$output_file_location = $CFG->datadir . "/other_posts.csv";
			$this->_outputCSV($output_file_location,$this->_other_posts);

		}else{ //spit out JSON
			$output_file_location = $CFG->datadir . "/other_posts.json";
			$this->_outputJSON($output_file_location,$this->_other_posts);
		}

	}

	public function getDailyBestOutput(){
		global $CFG;

		if($this->_output_format == "csv"){//open file. dump using csv
			$output_file_location = $CFG->datadir . "/daily_top_posts.csv";
			$this->_outputCSV($output_file_location,$this->_daily_best);

		}else{ //spit out JSON
			$output_file_location = $CFG->datadir . "/daily_top_posts.json";
			$this->_outputJSON($output_file_location,$this->_daily_best);
		}

	}

	//output to file
	function _outputCSV($file_location, $data_set){
		$output_pointer = fopen($file_location,"w");

		
		if($this->_output_type == "full"){
			//csv header
			fputcsv($output_pointer, $this->_csv_header);

			if(is_array($data_set)){
				foreach($data_set as $data){
					fputcsv($output_pointer, $data);
				}
			}
		}else{
			//spit out a comma separated list of post ids
			$temp_output = array();
			if(is_array($data_set)){
				foreach($data_set as $data){
					$temp_output[] = $data['id'];
				}
			}
			$temp_output = implode($temp_output, ',');
			fwrite($output_pointer,$temp_output);
			
		}
		

		fclose($output_pointer);


	}

	//output to json
	function _outputJSON($file_location, $data_set){
		$output_pointer = fopen($file_location,"w");

		
		if($this->_output_type == "full"){
			$temp_output =  json_encode($data_set);
			fwrite($output_pointer,$temp_output);
		}else{
			//spit out a comma separated list of post ids
			$temp_output = array();
			if(is_array($data_set)){
				foreach($data_set as $data){
					$temp_output[] = $data['id'];
				}
			}
			$temp_output = json_encode($temp_output);
			fwrite($output_pointer,$temp_output);
			
		}
		

		fclose($output_pointer);
	}




}