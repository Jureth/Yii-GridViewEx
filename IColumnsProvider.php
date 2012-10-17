<?php

/**
 * Interface to manage current visible columns info
 * @package baseclass_extensions
 * @subpackage CGridView
 * @author Yuri 'Jureth' Minin, J.Jureth@gmail.com
 * @version v1.0,11/13/2010
 */
interface IColumnsProvider {

	/**
	 * Must return array of visible columns names for gridId
	 */
    public function getVisibleColumns($gridId);

    /**
     * Stores column names wherether you want.
     */
    public function setVisibleColumns($gridId, $data);
}
