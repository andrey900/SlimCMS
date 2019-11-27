<?php

namespace Modules\Sections;

use App\Helpers\FileWorker;
use App\Source\BaseModule;
use App\Source\RouteSystem\AdminResource;
use App\Source\RouteSystem\AdminRouteCollection;
use App\Helpers\SessionManager as Session;
use App\Source\Factory\ModelsFactory;
use App\Models\Sections;
use App\Source\RouteSystem\PageResource;
use App\Source\RouteSystem\PageRouteCollection;
use App\Source\Composite\Menu;

class Module extends BaseModule
{
    const MODULE_NAME = 'Sections';

    protected static $loaded = false;

    public function registerRoute()
    {
        $sections = Sections::getAllGlobalActive()->keyBy('id')->toArray();

        if( empty($sections) )
            return;

        foreach ($sections as $section) {
            $url = array_filter(explode(\App\Models\Sections::PATH_DELIMITER, $section['path']));

            foreach ($url as &$id) {
                $id = $sections[$id]['code'];
            }

            $url[-1] = '';
            $url[] = $section['code'];
            ksort($url);

            $url = implode(\App\Models\Sections::PATH_DELIMITER, $url);

            PageRouteCollection::add(new PageResource($url.'/', 'sectionAction', 's'.$section['id']));
            PageRouteCollection::add(new PageResource($url.'/{pageCode}', 'detailAction', 'sp'.$section['id']));
        }
    }

    public function afterInitialization(){
        parent::afterInitialization();

        $this->adminPanelRouteRegister();

        $this->menuCreator();

        $this->container->dispatcher->addListener('basecontroller.render.before', function ($event) {

            $arItems = $this->findFieldValues($event);
            if( !$arItems )
                return true;

            $model = ModelsFactory::getModel('sections');
            $arRes = $model->where('active', 1)->get();

            $data = [];

            foreach ($arRes as $item) {
                if (!array_key_exists('parent_id', $arItems) || $item->id != $event->getParams()['fieldsValues']->id) {
                    $data[$item->id] = $item->name;
                }
            }

            foreach($arItems as $name=>$values){
                $event->getParams()['ttt'][$name]->values = $values + $data;
            }
        });
    }

    protected function adminPanelRouteRegister(){
        if( Session::has('auth') && Session::get('auth') ){
            AdminRouteCollection::add(new AdminResource('sections'));
            $this->adminPanelMenuRegister();
        }
    }

    protected function adminPanelMenuRegister(){
        $item = new Menu('Categories',[
            'menu_name' => 'section.categories',
            'url' => '#',
            'link_attr' => [
                'icon' => 'fa fa-list-alt fa-fw'
            ],
            'meta_attr' => [
                'onlyDevelopersMode' => false,
                'sort' => 180
            ],
            'sub_menu' => [
                new Menu('Show all categories', [
                    'menu_name' => 'categories.list',
                    'url' => '/admin/sections',
                    'link_attr' => [
                        'icon' => 'fa fa-file-o fa-fw'
                    ],
                    'meta_attr' => [
                        'onlyDevelopersMode' => false,
                    ],
                ]),
                new Menu('Add new category', [
                    'menu_name' => 'categories.add',
                    'url' => '/admin/sections/add',
                    'link_attr' => [
                        'icon' => 'fa fa-pencil-square-o fa-fw'
                    ],
                    'meta_attr' => [
                        'onlyDevelopersMode' => false,
                    ],
                ])
            ]
        ]);
        
        $this->container->get('adminMenuLeft')->add($item);
    }

    protected function findFieldValues($event){
        $arFields = ['category_id', 'parent_id'];

        $arNames = [];
        foreach ($arFields as $name) {
            $v = $event->getParams()['ttt'][$name]->values;

            if($v)
                $arNames[$name] = $v;
        }

        return $arNames;
    }

    protected function menuCreator(){
        $this->container->dispatcher->addListener('publiccontroller.menu.logic', function ($event) {
            $items = Sections::getAllGlobalActiveRaw()->where('show_in_menu', 1)->orderBy('sort', 'asc')->get();
            
            $name = '';
            if($route = $event->getParams()->request->getAttribute('route'))
                $name = $route->getName();

            $args = $route->getArguments();

            $menu = $event->getParams()->menu;
            $sections = array_filter($menu, function($e){
                return (bool)$e['section'];
            });
            
            foreach($sections as $k=>$item){
                $menu[$k]['current'] = (bool)($name=='page.sp'.$item['section'] && $args['pageCode'] == $item['code']);
                $menu[$k]['url'] = 'page.sp'.$item['section'];
            }

            foreach ($items as $item) {
                $menu[] = [
                    'name' => $item->name_for_menu,
                    'current' => (bool)($name=='page.s'.$item->id),
                    'section' => $item->parent_id,
                    'code' => $item->code,
                    'id' => $item->id,
                    'url' => 'page.s'.$item->id,
                ];
            }

            $event->getParams()->menu = $menu;
        });
    }

    public function installModule()
    {
        parent::installModule();

        $this->container->get('db')->schema()->create('sections', function($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->integer('sort')->default(100)->nullable();
            $table->integer('parent_id')->default(0)->nullable();
            $table->text('detail_text')->nullable();
            $table->string('detail_picture')->nullable();
            $table->string('show_in_menu')->nullable();
            $table->string('name_for_menu')->nullable();
            $table->string('path')->nullable();
            $table->char('active', 1)->default(1);
            $table->timestamps();
            $table->index(['code', 'parent_id', 'path']);
            //$table->foreign('parent_id')->references('id')->on('sections');
        });

        $this->container->get('db')->schema()->table('pages', function($table)
        {
            $table->integer('category_id')->default(0)->nullable();
            //$table->foreign('category_id')->references('id')->on('sections');
        });

        $path = RESOURCE_PATH.'models_field_info/pages.json';
        $pagesField = FileWorker::getJsonDataFile($path);

        if( !$pagesField ){
            $pagesField = [];
        }

        $trigUrlPrefix = false;
        foreach($pagesField as $field){
            if( $field->name == 'url_prefix' ){
                $field->type = "hidden";
                $trigUrlPrefix = true;
                break;
            }
        }
        if( !$trigUrlPrefix ){
            $cId = new \stdClass();
            $cId->name = "url_prefix";
            $cId->type = "hidden";
            $pagesField[] = $cId;
        }

        $cId = new \stdClass();
        $cId->name = "category_id";
        $cId->type = "select";
        $pagesField[] = $cId;

        FileWorker::saveJsonFile($path, $pagesField);

        $sectionFields = [
        	[
        		"name" => "active",
				"type" => "checkbox",
				"default" => "1",
				"values" => ["0" => "0", "1" => "1"]
        	],
        	[
        		"name" => "show_in_menu",
				"type" => "checkbox"
        	],
        	[
        		"name" => "detail_text",
				"type" => "html",
				"rows" => 10
        	],
        	[
        		"name" => "parent_id",
				"type" => "select"
        	],
        	[
        		"name" => "path",
				"type" => "hidden"
        	],
        	[
        		"name" => "sort",
				"type" => "string",
				"default" => 100
        	]
        ];

        FileWorker::saveJsonFile(RESOURCE_PATH.'models_field_info'.DIRECTORY_SEPARATOR.'sections.json', $sectionFields);

        FileWorker::addItemInModelsFillable('Pages', 'category_id');

        $this->saveConfigForModule(self::class, ["params" => ["installed"=>true, "active"=>true]]);
    }

    public function uninstallModule()
    {
        parent::uninstallModule();

        $this->container->get('db')->schema()->table('pages', function($table)
        {
            $table->dropColumn('category_id');
        });

        $this->container->get('db')->schema()->dropIfExists('sections');

        $path = RESOURCE_PATH.'models_field_info/pages.json';
        $pagesField = FileWorker::getJsonDataFile($path);

        if( $pagesField ){
            $cId = false;
            foreach($pagesField as $k=>$field){
                if( $field->name == 'url_prefix' ){
                    $field->type = "string";
                    continue;
                }
                if( $field->name == 'category_id' ){
                    $cId = $k;
                    continue;
                }
            }
            if( $cId !== false ){
                unset($pagesField[$cId]);
                FileWorker::saveJsonFile($path, $pagesField);
            }
        }

        FileWorker::removeItemInModelsFillable('Pages', 'category_id');

        $this->saveConfigForModule(self::class, ["params" => ["installed"=>false, "active"=>false]]);
    }
}