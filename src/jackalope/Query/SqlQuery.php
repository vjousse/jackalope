<?php

/**
 * SQL2 Query Object
 */
class jackalope_Query_SqlQuery implements PHPCR_Query_QueryInterface {
    protected $statement;
    protected $objectmanager;
    protected $path;

    /**
     * @param statement The SQL statement for this query
     * @param objectmanager Object manager to execute query against
     * @param path If this query is loaded from workspace with QueryManager->getQuery, path has to be stored here
     */
    public function __construct($statement, jackalope_ObjectManager $objectmanager, $path = null) {
        $this->statement = $statement;
        $this->objectmanager = $objectmanager;
        $this->path = $path;
    }
    /**
     * Binds the given value to the variable named $varName.
     *
     * @param string $varName name of variable in query
     * @param PHPCR_ValueInterface $value value to bind
     * @return void
     * @throws InvalidArgumentException if $varName is not a valid variable in this query.
     * @throws RepositoryException if an error occurs.
     * @api
     */
    public function bindValue($varName, PHPCR_ValueInterface $value);

    /**
     * Executes this query and returns a QueryResult object.
     *
     * @return PHPCR_Query_QueryInterface a QueryResult object
     * @throws PHPCR_Query_InvalidQueryException if the query contains an unbound variable.
     * @throws PHPCR_RepositoryException if an error occurs
     * @api
     */
    public function execute();

    /**
     * Returns the names of the bind variables in this query. If this query
     * does not contains any bind variables then an empty array is returned.
     *
     * @return array the names of the bind variables in this query.
     * @throws PHPCR_RepositoryException if an error occurs.
     * @api
     */
    public function getBindVariableNames();

    /**
     * Sets the maximum size of the result set to limit.
     *
     * @param integer $limit
     * @return void
     * @api
     */
    public function setLimit($limit);

    /**
     * Sets the start offset of the result set to offset.
     *
     * @param integer $offset
     * @return void
     * @api
     */
    public function setOffset($offset);

    /**
     * Returns the statement defined for this query.
     * If the language of this query is string-based (like JCR-SQL2), this method
     * will return the statement that was used to create this query.
     *
     * If the language of this query is JCR-JQOM, this method will return the
     * JCR-SQL2 equivalent of the JCR-JQOM object tree.
     *
     * This is the standard serialization of JCR-JQOM and is also the string stored
     * in the jcr:statement property if the query is persisted. See storeAsNode($absPath).
     *
     * @return string the query statement.
     * @api
     */
    public function getStatement() {
        return $this->statement;
    }

    /**
     * JCR-SQL2
     *
     * @return string the query language.
     */
    public function getLanguage() {
       return self::JCR_SQL2;
    }

    /**
     * If this is a Query object that has been stored using storeAsNode(java.lang.String)
     * (regardless of whether it has been saved yet) or retrieved using
     * QueryManager.getQuery(javax.jcr.Node)), then this method returns the path
     * of the nt:query node that stores the query.
     *
     * @return string path of the node representing this query.
     * @throws PHPCR_ItemNotFoundException if this query is not a stored query.
     * @throws PHPCR_RepositoryException if another error occurs.
     * @api
     */
    public function getStoredQueryPath() {
        if ($this->path == null) throw new PHPCR_ItemNotFoundException('Not a stored query');
        return $this->path;
    }

    /**
     * Creates a node of type nt:query holding this query at $absPath and
     * returns that node.
     *
     * This is  a session-write method and therefore requires a
     * Session.save() to dispatch the change.
     *
     * The $absPath provided must not have an index on its final element. If
     * ordering is supported by the node type of the parent node then the new
     * node is appended to the end of the child node list.
     *
     * @param string $absPath absolute path the query should be stored at
     * @return PHPCR_NodeInterface the newly created node.
     * @throws PHPCR_ItemExistsException if an item at the specified path already exists, same-name siblings are not allowed and this implementation performs this validation immediately.
     * @throws PHPCR_PathNotFoundException if the specified path implies intermediary Nodes that do not exist or the last element of relPath has an index, and this implementation performs this validation immediately.
     * @throws PHPCR_NodeType_ConstraintViolationException if a node type or implementation-specific constraint is violated or if an attempt is made to add a node as the child of a property and this implementation performs this validation immediately.
     * @throws PHPCR_Version_VersionException if the node to which the new child is being added is read-only due to a checked-in node and this implementation performs this validation immediately.
     * @throws PHPCR_Lock_LockException if a lock prevents the addition of the node and this implementation performs this validation immediately instead of waiting until save.
     * @throws PHPCR_UnsupportedRepositoryOperationException in a level 1 implementation.
     * @throws PHPCR_RepositoryException if another error occurs or if the absPath provided has an index on its final element.
     * @api
     */
    public function storeAsNode($absPath) {
        throw new PHPCR_UnsupportedRepositoryOperationException('Level 2');
    }

}
