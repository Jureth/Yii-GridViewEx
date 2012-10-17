<?php

/*
 *  All rights reserved, Yuri 'Jureth' Minin, J.Jureth@gmail.com, 2012
 */

/**
 * Yii action for invocation user methods to store columns list
 *
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,11/13/2010
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
            //We can't call static method by class name ($class::model()),
            //So we need to try something else
            $r = new ReflectionClass($provider);
            if ( $r->implementsInterface('IColumnsProvider') ){
                /* @var $m ReflectionMethod */
                $m = $r->getMethod('model');
                $model = $m->invoke(null);
                //$model = UserData::model();
                $model->setVisibleColumns($grid, $data);
                //todo maybe it would be better remove this?
                $model->save();
            }else{
                //wrong class
                throw new Exception('interface not implemented');
            }
        }
        return;
    }

}

?>
