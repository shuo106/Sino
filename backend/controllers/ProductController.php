<?php

namespace backend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\mysql\Product;
use common\models\mysql\ProductQuery;
use common\models\mysql\ProductColor;
use backend\models\Upload;
use backend\models\BannerForm;
use backend\controllers\MyController;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends MyController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Product::find(),
        ]);

        return $this->render('index', [
            // 'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
       
        return $this->render('view', [
            'model' => $this->findModel($id)
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $file = new BannerForm();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'file' => $file
            ]);
        }
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionUpload($id)
    {
        $model = new Upload();
        $info = $model->upImage();
        // return json_encode($info);
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($info && is_array($info) && $this->save($info,$id))
            return $info;
        else
            return ['code' => 1,'msg' => 'error'];
    }

    private function save($info,$id)
    {
        $model = new ProductColor();
        $model->product_id = intval($id);
        $model->name = 'red';
        $model->image_url = $info['url'];
        $res = $this->imageSize($info['attachment']);
        $model->image_width = $res['width'];
        $model->image_height = $res['height'];
        $model->image_mime = $res['mime'];

        return $model->save();
    }

    //获取图片的尺寸
    private function imageSize($imagePath)
    {
        $size = getimagesize($imagePath);
        $a = explode(" ", $size[3]);
        $res = array();
        for($i = 0;$i<count($a);$i++)
        {
            $temp = explode("=", $a[$i]);
            $res[$temp[0]] = (int)trim($temp[1],'"');
        }
        $res['mime'] = $size['mime'];
        return $res;
    }


}
