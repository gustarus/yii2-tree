<?php
/**
 * Created by:  Pavel Kondratenko
 * Created at:  17:10 09.04.14
 * Contact:     gustarus@gmail.com
 */

namespace gustarus\tree;

use yii\helpers\ArrayHelper;
use gustarus\tree\TreeNode;
use yii\base\Component;

/**
 * Class Tree
 * @package tree
 *
 * @property TreeNode $root
 * @property TreeNode[] $nodes
 * @property array $list
 */
class TreeHelper extends Component {

  public static function getTreeDrodownDisabledOptions($tree, $primaryValueToDisableFrom) {
    $node = $tree->findByPk($primaryValueToDisableFrom);
    if (!$node) {
      return [];
    }

    $values = array_merge(
      [$primaryValueToDisableFrom],
      $tree->getValuesByNode($node)
    );

    return ArrayHelper::map($values, function ($value) {
      return $value;
    }, function () {
      return ['disabled' => true];
    });
  }

  private static function getNodesOptions($nodes, $nodeDataKey, $sortDirection) {
    $options = [];

    if ($sortDirection) {
      ArrayHelper::multisort($nodes, 'data.' . $nodeDataKey, $sortDirection);
    }

    foreach ($nodes as $node) {
      if ($node->children) {
        $options[$node->data->$nodeDataKey]
          = self::getNodesOptions($node->children, $nodeDataKey, $sortDirection);
      } else {
        $options[$node->pk] = $node->data->$nodeDataKey;
      }
    }

    return $options;
  }
}
