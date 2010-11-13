<?php

/**
 * 
 *  All rights reserved, Yuri 'Jureth' Minin, J.Jureth@gmail.com, 2010
 */
Yii::import('zii.widgets.grid.CGridView');

/**
 * Расширение стандартного списка CGridView.
 * Добавлена кнопка {updateBtn}
 * Добавлен механизм выбора видимых столбцов и кнопка {columnsBtn}
 *
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,2010/11/13
 */
class GridViewEx extends CGridView {

    /**
     * Все возможные столбцы
     * @var array
     */
    private $dlgColumns;
    /**
     * Флаг подключения диалога выбора столбцов
     * @var boolean
     */
    private $useDialog;
    /**
     * Перечень классов, которые никогда не должны иметь возможность настраиваться
     * @var array
     */
    private $excludedColumns = array(
        'CButtonColumn'
    );
    private $columnNames = array(
        'CCheckBoxColumn' => 'Выбор',
    );
    /**
     * Класс для хранения текущих состояний столбцов.
     * @var IColumnsProvider
     */
    public $columnsProvider;
    /**
     * Действие для записи списка столбцов
     * @var string
     */
    public $action = '/site/setVisibleColumns';

    /**
     * Кнопка обновления
     */
    public function renderUpdateBtn(){
        echo CHtml::link('Обновить', '', array( 'class' => 'button middle', 'onclick' => '$.fn.yiiGridView.update("' . $this->getId() . '")' ));
    }

    /**
     * Create column objects and initializes them.
     */
    protected function initColumns(){
        if ( $this->columns === array( ) && $this->dataProvider instanceof CActiveDataProvider ) $this->columns = CActiveRecord::model($this->dataProvider->modelClass)->attributeNames();

        $visibleColumns = $this->getVisibilityData();

        $id = $this->getId();
        foreach( $this->columns as $i => $column ){
            if ( is_string($column) ) $column = $this->createDataColumn($column);
            else{
                if ( !isset($column['class']) ) $column['class'] = 'CDataColumn';
                $column = Yii::createComponent($column, $this);
            }


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

            //Видимость столбцов
            if ( !$column->visible || (isset($visibleColumns[$column->id]) && !$visibleColumns[$column->id]) ){
                unset($this->columns[$i]);
                continue;
            }

            $this->columns[$i] = $column;
        }

        foreach( $this->columns as $column ) $column->init();
    }

    protected function getVisibilityData(){
        if ( $this->columnsProvider instanceOf IColumnsProvider ){
            return $this->columnsProvider->getVisibleColumns($this->getId());
        }else{
            return null;
        }
    }

    /**
     * Кнопка выбора столбцов
     */
    public function renderColumnsBtn(){
        echo CHtml::link('Столбцы...', '', array( 'onclick' => '$("#show_columns_dlg").dialog("open")', 'class' => 'button middle' ));
        $this->useDialog = true;
    }

    /**
     * Вывод виджета
     */
    public function run(){
        parent::run();
        if ( $this->useDialog ){
            $this->renderSelectDialog();
        }
    }

    /**
     * Диалог выбора столбцов
     */
    private function renderSelectDialog(){
        $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
            'id' => 'show_columns_dlg',
            'options' => array(
                'title' => 'Выберите столбцы',
                'buttons' => array(
                    'Ок' => 'js:function(){
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
                    'Отмена' => 'js:function(){
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
