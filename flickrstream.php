<?php

// uses the photostream of a user or list of users
// last modified december 30, 2011

class FlickrStream extends Flickr {

	public function __construct($apikey, $user) {
		$this->apikey = $apikey;
		$this->sourceid = (array) $user;
	}

	protected function fetch_photos($size, $num) {
		$photos = array();
		foreach ($this->sourceid as $user) {
			$page = 1;
			do {
				$args = array(
					'user_id' => $this->sourceid,
					'page' => $page,
					'per_page' => ($num > 0 ? $num : null),
					'extras' => "url_$size"
				);
				$fl = $this->fetch_flickr("flickr.photos.getRecent", $args);
				foreach ($fl['photos']['photo'] as $photo) {
					$photos[] = $photo;
					if ($num > 0 && count($photos) >= $num) {
						break 3;
					}
				}
			} while ($num > 0 && $fl['pages'] > $page++);
		}
		return $photos;
	}

}

