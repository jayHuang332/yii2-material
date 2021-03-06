<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?=StringHelper::dirname(ltrim($generator->controllerClass, '\\'))?>;

use Yii;
use <?=ltrim($generator->modelClass, '\\')?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?=ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "")?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif;?>
use <?=ltrim($generator->baseControllerClass, '\\')?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
<?php if ($generator->enableUpload): ?>
use yii\web\UploadedFile;
<?php endif;?>

/**
 * <?=$controllerClass?> implements the CRUD actions for <?=$modelClass?> model.
 */
class <?=$controllerClass?> extends <?=StringHelper::basename($generator->baseControllerClass) . "\n"?>
{

    public $layout = '@jackh/dashboard/views/layouts/partial.php';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all <?=$modelClass?> models.
     * @return mixed
     */
    public function actionIndex()
    {
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?=isset($searchModelAlias) ? $searchModelAlias : $searchModelClass?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->setPagination(["pageSize" => 15]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?=$modelClass?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif;?>
    }

    /**
     * Displays a single <?=$modelClass?> model.
     * <?=implode("\n     * ", $actionParamComments) . "\n"?>
     * @return mixed
     */
    public function actionView(<?=$actionParams?>)
    {
        return $this->render('view', [
            'model' => $this->findModel(<?=$actionParams?>),
        ]);
    }

    /**
     * Creates a new <?=$modelClass?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new <?=$modelClass?>();

		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash("notify", ["type" => "success", "message" => Yii::t('app', 'Create Success!')]);
				return $this->redirect(Url::to(['/<?=Inflector::camel2id($modelClass)?>/update', "id" => $model->id]));
			} else {
				Yii::$app->session->setFlash("notify", ["type" => "warning", "message" => Yii::t('app', 'Create Failed!')]);
			}
		}

		return $this->render('create', [ 'model' => $model ]);
    }

    /**
     * Updates an existing <?=$modelClass?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?=implode("\n     * ", $actionParamComments) . "\n"?>
     * @return mixed
     */
    public function actionUpdate(<?=$actionParams?>)
    {
        $model = $this->findModel(<?=$actionParams?>);

		if ($model->load(Yii::$app->request->post())) {
			if ($model->save()) {
				Yii::$app->session->setFlash("notify", ["type" => "success", "message" => Yii::t('app', 'Update Success!')]);
			} else {
				Yii::$app->session->setFlash("notify", ["type" => "warning", "message" => Yii::t('app', 'Update Failed!')]);
			}
		}

		return $this->render('update', [ 'model' => $model ]);
    }

<?php if ($generator->enableUpload): ?>
	/**
	 * Upload file to an existing <?=$modelClass?> model.
	 * If upload is successful, will return a success status.
	 * <?=implode("\n     * ", $actionParamComments) . "\n"?>
	 * @return mixed
	 */
	public function actionUpload(<?=$actionParams?>) {
        $model = $this->findModel(<?=$actionParams?>);
        $model->file = UploadedFile::getInstance($model, 'file');
        Yii::$app->response->format = 'json';
        return [ 'success' => $model->upload()];
    }
<?php endif; ?>

    /**
     * Deletes an existing <?=$modelClass?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?=implode("\n     * ", $actionParamComments) . "\n"?>
     * @return mixed
     */
    public function actionDelete(<?=$actionParams?>)
    {
		Yii::$app->response->format = "json";
		$model = $this->findModel($id);
		$model->is_delete = 1;
		$model->save();
		if ($result) {
			return ["success" => $result];
		} else {
			foreach ($model->firstErrors as $key => $value) {
				$firstError = $value;
				break;
			}
			return ["success" => $result, "message" => $firstError];
		}
    }

    /**
     * Finds the <?=$modelClass?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?=implode("\n     * ", $actionParamComments) . "\n"?>
     * @return <?=$modelClass?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(<?=$actionParams?>)
    {
<?php
if (count($pks) === 1) {
	$condition = '$id';
} else {
	$condition = [];
	foreach ($pks as $pk) {
		$condition[] = "'$pk' => \$$pk";
	}
	$condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?=$modelClass?>::findOne(<?=$condition?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
