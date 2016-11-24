<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\authclient\widgets\AuthChoice;

$this->title = ucfirst($client->client_id);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">

    <div class="row">
        <div class="col-sm-offset-2 col-sm-8">
          <div class="authorize">
          <h1 class="text-center"><?= Html::encode($this->title) ?></h1>
          <div class="body">
            <ul>
              <li>Email</li>
            </ul>
          </div>
          <small style="padding:5px;color:gray;">
            <strong><?= Html::encode($this->title) ?></strong>
          will receive the following info: your public profile and email address.<i class="glyphicon glyphicon-info"></i>
          </small>
            <?php $form = ActiveForm::begin(['id' => 'authorized-form']); ?>
                <div class="form-group" style="">

                    <?= Html::submitButton('Cancel', ['class' => 'btn btn-default', 'name' => 'authorized-button', 'value'=>'no']) ?>
                    <?= Html::submitButton('Accept', ['class' => 'btn btn-primary', 'name' => 'authorized-button', 'value'=>'yes']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
        </div>
    </div>
</div>
