<?php

namespace App;


class Media extends \Spatie\MediaLibrary\Media {

	public function default(){
		return $this->hasOne(File::class, 'id', 'id');
	}
}