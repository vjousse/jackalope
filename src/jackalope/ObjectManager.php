<?php
/**
 * Implementation specific class that talks to the Transport layer to get nodes
 * and caches every node retrieved to improve performance.
 */
class jackalope_ObjectManager {
    protected $session;
    protected $transport;

    /** mapping of absolutePath => node object */
    protected $objectsByPath = array();
    /**
     * mapping of uuid => absolutePath
     * take care never to put a path in here unless there is a node for that path in objectsByPath
     */
    protected $objectsByUuid = array();

    public function __construct(jackalope_TransportInterface $transport,
                                PHPCR_SessionInterface $session) {
        $this->transport = $transport;
        $this->session = $session;
    }

    /**
     * Get the node identified by an absolute path.
     * Uses the factory to instantiate Node
     *
     * @param string $path The absolute path of the node to create
     * @return PHPCR_Node
     * @throws PHPCR_RepositoryException    If the path is not absolute or not well-formed
     */
    public function getNodeByPath($absPath) {
        $absPath = $this->normalizePath($absPath);

        $this->verifyAbsolutePath($absPath);

        if (empty($this->objectsByPath[$absPath])) {
            $node = jackalope_Factory::get(
                'Node',
                array(
                    $this->transport->getItem($absPath),
                    $absPath,
                    $this->session,
                    $this
                )
            );
            $this->objectsByUuid[$node->getIdentifier()] = $absPath;
            $this->objectsByPath[$absPath] = $node;
        }

        return $this->objectsByPath[$absPath];
    }

    /**
     * Get the property identified by an absolute path.
     * Uses the factory to instantiate Property
     *
     * @param string $path The absolute path of the property to create
     * @return PHPCR_Property
     * @throws PHPCR_RepositoryException    If the path is not absolute or not well-formed
     */
    public function getPropertyByPath($absPath) {
        $absPath = $this->normalizePath($absPath);

        $this->verifyAbsolutePath($absPath);

        $name = substr($absPath,strrpos($absPath,'/')+1); //the property name
        $nodep = substr($absPath,0,strrpos($absPath,'/')+1); //the node this property should be in
        /* OPTIMIZE? instead of fetching the node, we could make Transport provide it with a
         * GET /server/tests/jcr%3aroot/tests_level1_access_base/multiValueProperty/jcr%3auuid
         * (davex getItem uses json, which is not applicable to properties)
         */
        $n = $this->getNodeByPath($nodep);
        return $n->getProperty($name); //throws PathNotFoundException if there is no such property
    }

    /**
     * Normalizes a path according to JCR's spec (3.4.5)
     * 
     * <ul>
     *   <li>All self segments(.) are removed.</li>
     *   <li>All redundant parent segments(..) are collapsed.</li>
     *   <li>If the path is an identifier-based absolute path, it is replaced by a root-based 
     *       absolute path that picks out the same node in the workspace as the identifier it replaces.</li>
     * </ul>
     *
     * Note: A well-formed input path implies a well-formed and normalized path returned.
     *
     * @param   string  $path   The path to normalize
     * @return  string  The normalized path
     */
    public function normalizePath($path) {
        // UUDID is HEX_CHAR{8}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{12}
        if (preg_match('/^\[([[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12})\]$/', $path, $matches)) {
            $uuid = $matches[1];
            if (empty($this->objectsByUuid[$uuid])) {
                $finalPath = $this->transport->getNodePathForIdentifier($uuid);
                $this->objectsByUuid[$uuid] = $finalPath;
            } else {
                $finalPath = $this->objectsByUuid[$uuid];
            }
        } else {
            $finalParts= array();
            $abs = ($path && $path[0] == '/');
            $parts = explode('/', $path);
            foreach ($parts as $pathPart) {
                switch ($pathPart) {
                    case '.':
                    case '':
                        break;
                    case '..':
                        array_pop($finalParts);
                        break;
                    default:
                        array_push($finalParts, $pathPart);
                        break;
                }
            }
            $finalPath = implode('/', $finalParts);
            if ($abs) {
              $finalPath = '/'.$finalPath;
            }
        }
        return $finalPath;
    }

    /**
     * Creates an absolute path from a root and a relative path
     * and then normalizes it
     *
     * If root is missing or does not start with a slash, a slash will be prepended
     *
     * @param string Root path to append the relative
     * @param string Relative path
     * @return string Absolute and normalized path
     */
    public function absolutePath($root, $relPath) {

        $root = trim($root, '/');
        $concat = $root;
        if (strlen($root)) {
            $concat = "/$root/";
        } else {
            $concat = '/';
        }
        $concat .= ltrim($relPath, '/');

        // TODO: maybe this should be required explicitly and not called from within this method...
        return $this->normalizePath($concat);
    }

    /**
     * Get the node idenfied by an uuid or path or root path and relative
     * path. If you have an absolute path use getNodeByPath.
     * @param string uuid or relative path
     * @param string optional root if you are in a node context
     * @return return jackalope_Node
     * @throws PHPCR_ItemNotFoundException If the path was not found
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getNode($identifier, $root = '/'){
        if ($this->isUUID($identifier)) {
            if (empty($this->objectsByUuid[$identifier])) {
                $path = $this->transport->getNodePathForIdentifier($identifier);
                $node = $this->getNodeByPath($path);
                $this->objectsByUuid[$identifier] = $path; //only do this once the getNodeByPath has worked
                return $node;
            } else {
                return $this->getNodeByPath($this->objectsByUuid[$identifier]);
            }
        } else {
            $path = $this->absolutePath($root, $identifier);
            return $this->getNodeByPath($path);
        }
    }

    /**
     * This is only a proxy to the transport it returns all node types if none
     * is given or only the ones given as array.
     * @param array empty for all or selected node types by name
     * @return DOMDoocument containing the nodetype information
     */
    public function getNodeTypes($nodeTypes = array()) {
        return $this->transport->getNodeTypes($nodeTypes);
    }

    /**
     * Get a single nodetype @see getNodeTypes
     * @param string the nodetype you want
     * @return DOMDocument containing the nodetype information
     */
    public function getNodeType($nodeType) {
        return $this->getNodeTypes(array($nodeType));
    }

    /**
     * Verifies the path to be absolute and well-formed
     *
     * @param string $path the path to verify 
     * @return  bool    Always true :)
     * @throws PHPCR_RepositoryException    If the path is not absolute or well-formed
     */
    public function verifyAbsolutePath($path) {
        if (!jackalope_Helper::isAbsolutePath($path)) {
            throw new PHPCR_RepositoryException('Path is not absolute: ' . $path);
        }
        if (!jackalope_Helper::isValidPath($path)) {
            throw new PHPCR_RepositoryException('Path is not well-formed (TODO: match against spec): ' . $path);
        }
        return true; 
    }

    /**
     * Checks if the string could be a uuid
     * @param string possible uuid
     * @return bool if it looks like a uuid it will return true
     */
    protected function isUUID($id) {
        // UUDID is HEX_CHAR{8}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{12}
        if (1 === preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', $id)) {
            return true;
        } else {
            return false;
        }
    }

    /** Implementation specific: Transport is used elsewhere, provide it here for Session */
    public function getTransport() {
        return $this->transport;
    }
}
