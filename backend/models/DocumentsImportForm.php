<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use common\models\foListDocuments;
use common\models\foListDocumentsTp;
use common\models\Organizations;

/**
 * Форма импорта счетов покупателям с их табличными частями из CRM Fresh Office в качестве документов в веб-приложение.
 *
 * @property string $periodStart
 * @property string $periodEnd
 */
class DocumentsImportForm extends Model
{
    /**
     * @var string дата начала периода для выборки документов
     */
    public $periodStart;

    /**
     * @var string конец периода
     */
    public $periodEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['periodStart', 'periodEnd'], 'required'],
            [['periodStart', 'periodEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'periodStart' => 'Начало периода',
            'periodEnd' => 'Конец периода',
        ];
    }

    /**
     * Делает выборку табличных частей документов, входящих в границы периода, заданного пользователем.
     * @return array|\yii\db\ActiveRecord[]
     */
    public function fetchDocuments()
    {
        $subQuery = foListDocuments::find()->select(foListDocuments::tableName() . '.ID_DOC')->where([
            'ID_TIP_DOC' => Organizations::find()->select('fo_dt_id')->column(),
        ]);

        if (!empty($this->periodStart) && !empty($this->periodEnd)) {
            // если указаны обе даты
            $subQuery->andFilterWhere(['between', foListDocuments::tableName() . '.DATA_DOC', new Expression('CONVERT(datetime, \''. $this->periodStart .'T00:00:00.000\', 126)'), new Expression('CONVERT(datetime, \''. $this->periodEnd .'T23:59:59.999\', 126)')]);
        } else if (!empty($this->periodStart) && empty($this->periodEnd)) {
            // если указано только начало периода
            $subQuery->andFilterWhere(['>=', foListDocuments::tableName() . '.DATA_DOC', new Expression('CONVERT(datetime, \''. $this->periodStart .'T00:00:00.000\', 126)')]);
        } else if (empty($this->periodStart) && !empty($this->periodEnd)) {
            // если указан только конец периода
            $subQuery->andFilterWhere(['<=', foListDocuments::tableName() . '.DATA_DOC', new Expression('CONVERT(datetime, \''. $this->periodEnd .'T23:59:59.999\', 126)')]);
        }
        else {
            return [];
        };

        return foListDocumentsTp::find()->select([
            foListDocuments::tableName() . '.*',
            foListDocumentsTp::tableName() . '.*',
            'src_dc' => 'LIST_TOVAR.ADD_klass_opasnosti',
            'src_hk' => 'LIST_TOVAR.ADD_sposob',
        ])->leftJoin(
            '[CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR]', '[LIST_TOVAR].[ID_TOVAR] = [LIST_TOVAR_DOC].[ID_TOVAR]'
        )->where([
            foListDocumentsTp::tableName() . '.ID_DOC' => $subQuery,
        ])->andWhere('[TOVAR_DOC] NOT LIKE \'%спортные услуги%\'')->andWhere([
            'or',
            [foListDocuments::tableName() . '.TRASH' => null],
            [foListDocuments::tableName() . '.TRASH' => 0],
        ])->joinWith('document')->orderBy(foListDocuments::tableName() . '.DATA_DOC DESC, ' . foListDocuments::tableName() . '.ID_DOC')->asArray()->all();
    }
}
