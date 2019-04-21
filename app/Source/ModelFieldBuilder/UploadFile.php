<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 6/16/16
 * Time: 12:02 AM
 */

namespace App\Source\ModelFieldBuilder;


class UploadFile extends AField
{
    protected $allowTypes = ['file'];
    protected $defaultType = 'file';

    public function __construct(\stdClass $obj){
        parent::__construct($obj);
    }

    public function __toString(){
        $str = "";
        if( $this->value ){
            $str = '<br/><small class="text-danger"><a href="/'.$this->value.'" target="_blank">'.$this->value.'</a></small>';
            $str .= sprintf('<br/><input type="checkbox" name="%s" value=""> Remove', $this->name);
        }
        $str .= sprintf('<input class="file_upload" type="%s" name="%s" #>', $this->type, $this->name);

        return $this->toString($str);
    }
}
