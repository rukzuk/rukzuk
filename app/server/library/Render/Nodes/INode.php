<?php


namespace Render\Nodes;

use Render\Visitors\AbstractVisitor;

interface INode
{

  /**
   * Part of the visitor pattern to realize a double dispatching. It is used
   * to tell the visitor what kind of Node/Module the current Node is.
   *
   * Note: This is the only function that the visitor pattern really need
   * for all nodes!
   *
   * @param AbstractVisitor $visitor
   * @return Nothing
   */
  public function accept(AbstractVisitor $visitor);

  /**
   * Only used by the NodeFactory to create the tree structure.
   * @see \Render\NodeFactory
   *
   * @param INode $node
   *
   * @return void
   */
  public function addChild(INode $node);
}
