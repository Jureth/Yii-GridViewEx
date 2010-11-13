<?php

/**
 * Интерфейс для класса сохранения данных о видимых столбцах
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,2010/11/13
 */
interface IColumnsProvider {

    public function getVisibleColumns($gridId);

    public function setVisibleColumns($gridId, $data);
}
