<?php

/**
 *
 *  All rights reserved, Yuri 'Jureth' Minin, J.Jureth@gmail.com, 2012
 */
Yii::import('zii.widgets.grid.CGridView');

/**
 * !!! Last changes wasn't tested yet.
 *
 * Extended CGridView.
 * 1. Columns visibility added. Use {columnsBtn} to show management button
 *
 * @todo Replace russian (and english) hardcoded texts with i18n
 *
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,11/13/2010
 */
class GridViewEx extends CGridView {

    /**
     * All possible columns
     * @var array
     */
    private $dlgColumns;

    /**
     * Flag to use columns selection dialog.
     * If it set to FALSE there will be common CGridView
     * @var boolean
     */
    private $useDialog;

    /**
     * Column classes, which will never be shown in selection dialog.
     * @var array
     */
    private $excludedColumns = array(
        'CButtonColumn' //we'll always skip columns like 'actions' by default
    );

    /**
     * Dialog column names
     * @var array
     */
    private $columnNames = array(
        'CCheckBoxColumn' => 'Select columns',
    );

    /**
     * Object to store visible columns settings
     * @var IColumnsProvider
     */
    public $columnsProvider;

    /**
     * URL associated with VisibleColumnsAction
     * Will be called via AJAX with current columns list after
     * closing the dialog
     *
     * @var string
     */
    public $action = '/site/setVisibleColumns';

    /**
     * Create column objects and initializes them.
     */
    protected function initColumns(){
        if ( $this->columns === array( )
            && $this->dataProvider instanceof CActiveDataProvider
        ){
            $this->columns = CActiveRecord::model($this->dataProvider->modelClass)->attributeNames();
        }

        $visibleColumns = $this->getVisibilityData();

        $id = $this->getId();
        foreach( $this->columns as $i => $column ){
            if ( is_string($column) ){
                $column = $this->createDataColumn($column);
            }else{
                if ( !isset($column['class']) ){
                    $column['class'] = 'CDataColumn';
                }
                $column = Yii::createComponent($column, $this);
            }

            //Generate own column id if it's needed
            if ( $column->id === null ){
                if ( $column instanceof CDataColumn ){
                    $column->id = 'col_' . str_replace(array( '.', '=>' ), '_', $column->name);
                }else{
                    $column->id = $id . '_c' . $i;
                }
            }


            if ( !in_array(get_class($column), $this->excludedColumns) ){
                $listItem = array(
                    'id' => $column->id,
                    'header' => $column->header,
                );

                if ( isset($this->columnNames[get_class($column)]) ){
                    $listItem['header'] = $this->columnNames[get_class($column)];
                }

                if ( (!$column->header) && $this->dataProvider instanceof CActiveDataProvider ){
                    $listItem['header'] = $this->dataProvider->model->getAttributeLabel($column->name);
                }
                $this->dlgColumns[] = $listItem;
            }

            //Set up visibility
            if ( !$column->visible || (isset($visibleColumns[$column->id]) && !$visibleColumns[$column->id]) ){
                unset($this->columns[$i]);
                continue;
            }

            $this->columns[$i] = $column;
        }

        foreach( $this->columns as $column ){
            $column->init();
        }
    }

    /**
     * Get stored columns visibility information.
     */
    protected function getVisibilityData(){
        if ( $this->columnsProvider instanceOf IColumnsProvider ){
            return $this->columnsProvider->getVisibleColumns($this->getId());
        }else{
            return null;
        }
    }

    /**
     * Columns selection button
     */
    public function renderColumnsBtn(){
        echo CHtml::link(
            'Columns...',
            '',
            array(
                'onclick' => '$("#show_columns_dlg").dialog("open")',
                'class' => 'button middle'
            )
        );
        $this->useDialog = true;
    }

    /**
     * Rendering
     */
    public function run(){
        parent::run();
        if ( $this->useDialog ){
            $this->renderSelectDialog();
        }
    }

    /**
     * Renders column selection dialog
     */
    private function renderSelectDialog(){
        $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
            'id' => 'show_columns_dlg',
            'options' => array(
                'title' => 'Select columns',
                'buttons' => array(
                    'Ok' => 'js:function(){
							var column_ids = new Array();
							$("#show_columns_grid .items").find("input:checkbox").each(function(){
								column_ids.push(new Array($(this).val(), $(this).attr("checked") ));
							});
							$.post(
                                "' . $this->action . '",
                                {
                                    "ajax":"1",
                                    "grid":"' . $this->getId() . '",
                                    "columnsProvider":"' . get_class($this->columnsProvider) . '",
                                    "columns":column_ids
                                },
                                function(data){
                                    $.fn.yiiGridView.update("' . $this->getId() . '");
                                }
                            );
							$(this).dialog("close");
						}',
                    'Cancel' => 'js:function(){
							$(this).dialog("close");
						}',
                ),
                'open' => 'js:function(event, ui){
						$("#show_columns_grid .items").find("input:checkbox").each(function(){
							if ( $("#' . $this->getId() . '").find(".items th#"+$(this).val()).size() > 0 ){
								$(this).attr("checked", true);
							}else{
								$(this).attr("checked", false);
							}
						});
					}',
                'close' => 'js:function(event, ui){
					}',
            )
        ));
        echo CHtml::tag('div', array( ), false, false);

        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'show_columns_grid',
            'selectableRows' => 2,
            'hideHeader' => true,
            'summaryText' => '',
            //Todo Is that is internal ArrayDataProvider or my own?
            //If it's my own, it'll be better to replace it by internal
            'dataProvider' => CustomArrayDataProvider::create()->setRawData($this->dlgColumns),
            'columns' => array(
                array(
                    'class' => 'dataCheckBoxColumn',
                    'id' => 'id',
                    'name' => 'id',
                ),
                'header',
            ),
        ));
        echo CHtml::closeTag('div');
        $this->endWidget();
    }
}
