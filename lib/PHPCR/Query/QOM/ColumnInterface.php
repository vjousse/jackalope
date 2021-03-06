<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script belongs to the FLOW3 package "PHPCR".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Defines a column to include in the tabular view of query results.
 *
 * If property is not specified, a column is included for each single-valued
 * non-residual property of the node type specified by the nodeType attribute of
 * selector.
 *
 * If property is specified, columnName is required and used to name the column
 * in the tabular results. If property is not specified, columnName must not be
 * specified, and the included columns will be named "selector.propertyName".
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @license http://opensource.org/licenses/bsd-license.php Simplified BSD License
 * @api
 */
interface PHPCR_Query_QOM_ColumnInterface {

	/**
	 * Gets the name of the selector.
	 *
	 * @return string the selector name; non-null
	 * @api
	 */
	public function getSelectorName();

	/**
	 * Gets the name of the property.
	 *
	 * @return string the property name, or null to include a column for each single-value non-residual property of the selector's node type
	 * @api
	 */
	public function getPropertyName();

	/**
	 * Gets the column name.
	 *
	 * @return string the column name; must be null if getPropertyName is null and non-null otherwise
	 * @api
	 */
	public function getColumnName();

}

?>