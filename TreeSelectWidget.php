<?php
/**
 * Created by:  Pavel Kondratenko
 * Created at:  17:18 09.04.14
 * Contact:     gustarus@gmail.com
 */

namespace gustarus\tree;

use yii\helpers\Html;
use yii\widgets\InputWidget;

class TreeSelectWidget extends InputWidget {

  public $model;

  public $attribute;

  public $models;

  public $key = 'id';

  public $parentKey = 'parent_id';

  public $dataKey = 'title';

  public $options = [
    'class' => 'form-control',
    'prompt' => '',
  ];


  public function run() {
    $tree = $tree = new Tree([
      'key' => $this->key,
      'parentKey' => $this->parentKey,
      'models' => $this->models,
    ]);

    $dataOptions = $tree->getList($tree->key, $this->dataKey);
    $disabledOption = null;
    if ($this->model->id) {
      $disabledOption = TreeHelper::getTreeDrodownDisabledOptions($tree, $this->model->id);
    }

    $inputOptions = array_merge($this->options, ['options' => $disabledOption]);
    return Html::activeDropDownList($this->model, $this->attribute, $dataOptions, $inputOptions);
  }
} 
