<?php

/*
 *  All rights reserved, Yuri 'Jureth' Minin, J.Jureth@gmail.com, 2010
 */

/**
 * Yii action for invocation user methods to store columns list
 *
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,2010/11/13
 */
class VisibleColumnsAction extends CAction {

    public function run(){
        if ( !isset($_REQUEST['ajax']) ){
            return;
        }
        $grid = $_POST['grid'];
        if ( $_POST['columns'] ){
            foreach( $_POST['columns'] as $row ){
                $data[$row[0]] = ($row[1] == 'true');
            }
        }
        require_once dirname(__FILE__) . '/IColumnsProvider.php';

        $provider = $_POST['columnsProvider'];
        if ( $grid && $data ){
            //Выяснение соответствия переданного класса интерфейсу и запуск его метода.
            //Метода жуткая, может есть способы и проще.
            $r = new ReflectionClass($provider);
            if ( $r->implementsInterface('IColumnsProvider') ){
                /* @var $m ReflectionMethod */
                $m = $r->getMethod('model');
                $model = $m->invoke(null);
                //$model = UserData::model();
                $model->setVisibleColumns($grid, $data);
                //todo remove this maybe?
                $model->save();
            }else{
                throw new Exception('interface not implemented');
            }
        }
        return;
    }

}

?>
