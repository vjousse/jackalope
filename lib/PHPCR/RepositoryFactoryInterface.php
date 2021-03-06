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
 * RepositoryFactory is an interface for factory class implementations for
 * Repositories.
 *
 * Examples how to obtain repository instances
 *
 * Use repository factory based on parameters (the parameters below are examples):
 *    $parameters = array('com.vendor.address' => 'vendor://localhost:9999/myrepo');
 *    $repo = PHPCR_RepositoryFactory::getRepository($parameters);
 *
 * Get a default repository available in this environment:
 *    $repo = PHPCR_RepositoryFactory::getRepository();
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @license http://opensource.org/licenses/bsd-license.php Simplified BSD License
 * @api
 */
interface PHPCR_RepositoryFactoryInterface {

	/**
	 * Attempts to establish a connection to a repository using the given
	 * parameters.
	 *
	 * Parameters are passed in an array of key/value pairs. The keys are not
	 * specified by JCR and are implementation specific.
	 * However, vendors should use keys that are namespace qualified in the
	 * Java package style to distinguish their key names. For example
	 * an address parameter might be com.vendor.address.
	 *
	 * The implementation must return NULL if it does not understand
	 * the given parameters. The implementation may also return null if a default
	 * repository instance is requested (indicated by null parameters) and this
	 * factory is not able to identify a default repository. An implementation
	 * should throw an RepositoryException if it is the right factory but has
	 * trouble connecting to the repository.
	 *
	 * @param array|NULL $parameters string key/value pairs as repository arguments or NULL if a client wishes to connect to a default repository.
	 * @return PHPCR_RepositoryInterface a repository instance or NULL if this implementation does not understand the passed parameters
	 * @throws PHPCR_RepositoryException if no suitable repository is found or another error occurs.
	 * @api
	 */
	public function getRepository(array $parameters = NULL);

}

?>