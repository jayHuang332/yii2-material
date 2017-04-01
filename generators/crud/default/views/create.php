<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model <?=ltrim($generator->modelClass, '\\')?> */

?>
<div class="<?=Inflector::camel2id(StringHelper::basename($generator->modelClass))?>-create col-xs-12" style="padding-bottom: 100px">
    <?="<?= "?>$this->render('_form', [ 'model' => $model ]) ?>
</div>
