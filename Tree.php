<?php
/**
 * Created by:  Pavel Kondratenko
 * Created at:  17:10 09.04.14
 * Contact:     gustarus@gmail.com
 */

namespace gustarus\tree;

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
class Tree extends Component {

  /**
   * Ключ используемый для идентификации элемента.
   * @var string
   */
  public $key = 'id';

  /**
   * Ключ, используемый для идентификации родителя.
   * @var string
   */
  public $parentKey = 'parent_id';


  /**
   * Родительский элемент дерева.
   * @var TreeNode
   */
  private $root;

  /**
   * Коллекция элементов дерева.
   * @var TreeNode[]
   */
  private $nodes;


  /**
   * Устанавливает коллекцию дерева из переданных моделей.
   * Удобно передавать модели при создании дерева.
   * @param $models
   */
  public function setModels($models) {
    $this->set($models);
  }

  /**
   * Устанавливает коллекцию дерева.
   * @param $models
   * @return Tree
   */
  public function set($models) {
    return $this->reset()->add($models);
  }

  /**
   * Сброс дерева.
   * @return Tree
   */
  public function reset() {
    // сбрасываем дерево
    $this->root = $this->nodes = null;

    // инициализируем дерево
    $this->root = new TreeNode();
    $this->nodes = [];

    return $this;
  }

  /**
   * Добавляет модели в коллекцию дерева.
   * @param array $models
   * @return Tree
   */
  public function add($models) {
    is_object($models) && $models = [$models];

    // получаем названия ключей
    $key = $this->key;
    $parentKey = $this->parentKey;

    // собираем коллекцию
    foreach ($models as $model) {
      // создем элемент дерева
      $node = new TreeNode();
      $node->pk = $model->$key;
      $node->data = $model;

      // сохраняем элемент в колекцию
      $this->nodes[$model->$key] = $node;
    }

    // устанавливаем связи с деревом
    foreach ($this->nodes as $node) {
      // привязываем к родителю
      $node->bindParent($this->findByPk($node->data->$parentKey) ?: $this->root);
    }

    return $this;
  }

  /**
   * Возвращаем корневой элемент.
   * @return TreeNode
   */
  public function getRoot() {
    return $this->root;
  }

  /**
   * @param $level
   * @return TreeNode[]
   */
  public function getLevelRoots($level) {
    return $this->getLevelRootsRecursion($this->root, $level);
  }

  /**
   * @param TreeNode $node
   * @param int $level
   * @return TreeNode[]
   */
  private function getLevelRootsRecursion($node, $level) {
    // получаем текущий уровень
    if ($node->getLevel() + 1 == $level) {
      return $node->children;
    }

    $children = [];
    foreach ($node->children as $child) {
      $children = array_merge($children, $this->getLevelRootsRecursion($child, $level));
    }

    return $children;
  }

  /**
   * Возвращаем элементы дерева.
   * @return TreeNode[]
   */
  public function getNodes() {
    return $this->nodes;
  }


  /**
   * Находит элемент дерева по модели.
   * @param \yii\db\ActiveRecord $model
   * @return TreeNode
   */
  public function findByModel($model) {
    return $this->findByPk($model->getAttribute($this->key));
  }

  /**
   * Находит элемент дерева по ключу.
   * @param mixed $pk
   * @return TreeNode
   */
  public function findByPk($pk) {
    return isset($this->nodes[$pk]) ? $this->nodes[$pk] : false;
  }


  /**
   * Возвращает список ключей.
   * @param string $value
   * @param array $options
   * @return array
   */
  public function getValues($value = 'id', $options = []) {
    return $this->getValuesByNode($this->root, $value, $options);
  }

  /**
   * Возвращает список в виде $value => $label.
   * @param string $value
   * @param string $label
   * @param array $options
   * @return array
   */
  public function getList($value = 'id', $label = 'title', $options = []) {
    return $this->getListByNode($this->root, $value, $label, $options);
  }

  /**
   * Возвращает список значений ключей с элемента дерева.
   * @param TreeNode $node
   * @param string $value
   * @param array $options
   * @return array
   */
  public function getValuesByNode($node, $value = 'id', $options = []) {
    $options = array_merge([
      'prefix' => false,
      'since' => false,
      'till' => false,
    ], $options);

    // рекурсивно получаем данные
    return $this->getListByNodeRecursion($node, $value, $value, $options);
  }

  /**
   * Возвращает список начиная с элемента дерева в виде $value => $label.
   * @param TreeNode $node
   * @param string $value
   * @param string $label
   * @param array $options
   * @return array
   */
  public function getListByNode($node, $value = 'id', $label = 'title', $options = []) {
    $options = array_merge([
      'prefix' => '- ',
      'since' => false,
      'till' => false,
    ], $options);

    // рекурсивно получаем данные
    return $this->getListByNodeRecursion($node, $value, $label, $options);
  }

  /**
   * Рекурсия для метода getListByNode.
   * @param TreeNode $node
   * @param $value
   * @param $label
   * @param $options
   * @return array
   */
  private function getListByNodeRecursion($node, &$value, &$label, &$options) {
    $list = [];
    foreach ($node->getChildren() as $child) {
      // добавляем элемент в список
      $list[$child->data->$value] = $options['prefix']
        ? str_repeat($options['prefix'], $child->level - 1) . $child->data->$label
        : $child->data->$label;

      // рекурсивно добавляем дочерние элементы
      if ($child->getChildren()) {
        $list += $this->getListByNodeRecursion($child, $value, $label, $options);
      }
    }

    return $list;
  }
}
