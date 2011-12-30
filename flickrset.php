<?php

// uses a photoset or list of photosets
// last modified august 29, 2011

class FlickrSet extends Flickr {

	public function __construct($apikey, $set) {
		$this->apikey = $apikey;
		$this->sourceid = (array) $set;
	}

	protected function fetch_photos($size, $num) {
		$photos = array();
		foreach ($this->sourceid as $set) {
			$page = 1;
			do {
				$args = array(
					'photoset_id' => $set,
					'page' => $page,
					'per_page' => ($num > 0 ? $num : null),
					'extras' => "url_$size"
				);
				$fl = $this->fetch_flickr("flickr.photosets.getPhotos", $args);
				foreach ($fl['photoset']['photo'] as $photo) {
					$photos[] = $photo;
					if ($num > 0 && count($photos) >= $num) {
						break 3;
					}
				}
			} while ($fl['photoset']['pages'] > $page++);
		}
		return $photos;
	}

}
