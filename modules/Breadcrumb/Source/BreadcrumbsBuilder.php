<?php

namespace Modules\Breadcrumb\Source;

use App\Source\Factory\ModelsFactory;

class BreadcrumbsBuilder
{
	protected $path;
	protected $breadcrumbs = '';
	protected $delimiter;
	protected $arItems = [];

	private $isBuild = false;
	private $addCurrentItem = true;
	private $activeLastItem = false;


	public function __construct($path = '')
	{
		$this->delimiter = \App\Models\Sections::PATH_DELIMITER;
		
		if( $path )
			$this->setPath($path);
	}

	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	public function addItem($item)
	{
		$this->arItems[] = $item;

		return $this;
	}

	public function addLastItem($url, $name)
	{
		$this->arLastItem = ['name'=>$name, 'url'=>$url];

		return $this;
	}

	public function getItems()
	{
		if( $this->path )
			$this->parsePath();

		return $this->arItems;
	}

	public function setItems(array $arItems)
	{
		$this->arItems = $arItems;
	}

	public function parsePath()
	{
		$this->setItems(array_filter(explode($this->delimiter, $this->path), function($e){
			return $e !== "";
		}));

		$this->removePath();

		return $this;
	}

	public function removePath()
	{
		$this->path = null;
	}

	public function makeUrls()
	{
		if( $this->path )
			$this->parsePath();

		if( $this->arItems ){
			$items = ModelsFactory::getModel('sections')->find($this->arItems)->keyBy('id');

			$this->breadcrumbs = '<ol class="breadcrumb">';
			
			$cnt = count($this->arItems);

			for ($i = 1; $i <= $cnt; $i++) {
				$item = $this->arItems[$i];

				if( $item === "" )
					continue;

				$url = '/';
				$name = 'Home';
				if( $item > 0 ){
					$url = $GLOBALS['app']->getContainer()->router->pathFor('page.s'.$item);
					$name = $items[$item]->name;
				}
				
				$this->breadcrumbs .= '<li><a href="'.$url.'">'.$name.'</a></li>';
			}

			if( !$this->activeLastItem && $this->addCurrentItem ){
				$this->breadcrumbs .= '<li class="active">'.$this->arLastItem['name'].'</li>';
			}
			if( $this->activeLastItem && $this->addCurrentItem ){
				$this->breadcrumbs .= '<li><a href="'.$this->arLastItem['url'].'">'.$this->arLastItem['name'].'</a></li>';
			}

			$this->breadcrumbs .= '</ol>';
		}
		
		$this->isBuild = true;
	}

	public function __toString()
	{
		if( !$this->isBuild )
			$this->makeUrls();

		return $this->breadcrumbs;
	}
}