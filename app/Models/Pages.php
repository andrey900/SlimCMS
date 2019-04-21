<?php

namespace App\Models;

class Pages extends BaseModel
{
	protected $table = 'pages';
	protected $errors = array();

	protected $fillable = array (
  0 => 'name',
  1 => 'code',
  2 => 'url_prefix',
  3 => 'preview_text',
  4 => 'detail_text',
  5 => 'preview_picture',
  6 => 'detail_picture',
  7 => 'show_in_menu',
  8 => 'name_for_menu',
  9 => 'active',
  10 => 'slogan',
  11 => 'fullname',
  12 => 'sort',
  13 => 'category_id',
);

public function validate() {
	$this->errors = array();
	if ($this->show_in_menu && empty($this->name_for_menu)) {
		$this->errors['name_for_menu'] = "can't be blank";
	}
	$page = Pages::where('code', $this->code)->first();
	if ($page && $page->id != $this->id) {
		$this->errors['code'] = "is already taken";
	}
	return empty($this->errors);
}

public function getErrors() {
	return $this->errors;
}
}
