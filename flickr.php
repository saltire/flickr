<?php

// flickr api fetcher
// last modified sept 15, 2011

abstract class Flickr {

	protected $apikey;
	protected $sourceid;
	
	private $sizes = array('sq', 't', 's', 'm', 'z', 'l', 'o');
	
	abstract protected function fetch_photos($size, $num);

	// runs a curl request to the flickr api and returns the result as an array
	protected function fetch_flickr($method, $args) {
		$query = http_build_query(array(
			'method' => $method,
			'format' => 'php_serial',
			'api_key' => $this->apikey
				) + (array) $args);

		$ch = curl_init("http://api.flickr.com/services/rest/?$query");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		$result = curl_exec($ch);
		$fl = $result ? unserialize($result) : array();
		curl_close($ch);
		return $fl;
	}

	// returns all photos (or $num number of photos)
	public function get_photos($size = 's', $num = 0) {
		$size = in_array($size, $this->sizes) ? $size : 's';

		$photolist = array();
		$photos = $this->fetch_photos($size, $num);
		foreach ($photos as $photo) {
			$photolist[] = array(
				'num' => count($photolist) + 1,
				'total' => count($photos),
				'id' => $photo['id'],
				'uri' => $photo["url_$size"],
				'width' => $photo["width_$size"],
				'height' => $photo["height_$size"]
			);
		}
		return $photolist;
	}

	// returns a single photo and ids of neighbouring photos
	public function get_photo($id, $size = 's') {
		$args = array(
			'photo_id' => $id
		);
		$fl = $this->fetch_flickr("flickr.photos.getSizes", $args);
		if ($fl['stat'] == 'fail') {
			return 0;
		}

		$size_index = array_search($size, $this->sizes);
		$photo = $fl['sizes']['size'][($size_index === false) ? 2 : $size_index];

		$photos = $this->get_photo_ids();
		$key = array_search($id, $photos);

		return array(
			'uri' => $photo['source'],
			'width' => $photo['width'],
			'height' => $photo['height'],
			'num' => $key + 1,
			'prev' => isset($photos[$key - 1]) ? $photos[$key - 1] : null,
			'next' => isset($photos[$key + 1]) ? $photos[$key + 1] : null
		);
	}

	// returns a list of photos on a particular page, and total number of pages
	public function get_photos_page($page = 1, $pp = 24, $size = 's') {
		$skip = $pp * ($page - 1);
		$photos = $this->get_photos($size);
		return array(
			'photos' => array_slice($photos, $skip, $pp),
			'pages' => ceil(count($photos) / $pp),
		);
	}

	// returns an array of ids of all photos
	private function get_photo_ids() {
		$photos = array();
		foreach ($this->get_photos() as $photo) {
			$photos[] = $photo['id'];
		}
		return $photos;
	}

	// given a number of photos per page, return the page a certain photo is on
	public function get_photo_page_num($id, $pp) {
		$photos = $this->get_photo_ids();
		$key = array_search($id, $photos);
		if ($key === false) {
			return 0;
		}
		$page = ceil(($key + 1) / $pp);
		return $page;
	}

}
