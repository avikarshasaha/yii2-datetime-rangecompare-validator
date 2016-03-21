<?php
namespace avikarsha\validators;

use DateTime;
use Yii;
use yii\base\Exception;
use yii\helpers\Html;
use yii\validators\Validator;

class DateTimeRangeCompareValidator extends Validator
{

    /**
     * @var string the date format that the value being validated should follow.
     * Please refer to <http://www.php.net/manual/en/datetime.createfromformat.php> on
     * supported formats.
     */
    public $format = 'Y-m-d';

    /**
     * @var string the date format that the value being validated should follow.
     * Used to correctly work with momentjs
     * Please refer to <http://momentjs.com/docs/> on supported formats.
     */
    public $jsFormat = 'YYYY-MM-DD';

    /**
     * @var string the name of the attribute to be compared with
     */
    public $compareAttribute;

    /**
     * @var string|\DateTime the constant value to be compared with
     */
    public $compareValue;

    /**
     * @var string the operator for comparison. Defaults to '='.
     * The followings are valid operators:
     * <ul>
     * <li>'=' or '==': validates to see if the two values are equal;</li>
     * <li>'!=': validates to see if the two values are NOT equal;</li>
     * <li>'>': validates to see if the value being validated is greater than the value being compared with;</li>
     * <li>'>=': validates to see if the value being validated is greater than or equal to the value being compared with;</li>
     * <li>'<': validates to see if the value being validated is less than the value being compared with;</li>
     * <li>'<=': validates to see if the value being validated is less than or equal to the value being compared with.</li>
     * </ul>
     */
    public $operator = '==';

    /**
     * @var array error messages list
     */
    private $_messages;
    
    /**
     * @var array permitted operations
     */
    private $_permittedOperations = ['!=', '<', '<=', '==', '>', '>='];

    /**
     * @var boolean whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnCompareAttributeEmpty = true;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isEmpty($this->compareAttribute) && $this->isEmpty($this->compareValue)) {
            throw new Exception(Yii::t('app', 'You must specify compareAttribute or compareValue'));
        }
        
        $this->_messages = [
            '!=' => Yii::t('yii', '{attribute} must not be equal to "{compareValue}".'),
            '<' => Yii::t('yii', '{attribute} must be less than "{compareValue}".'),
            '<=' => Yii::t('yii', '{attribute} must be less than or equal to "{compareValue}".'),
            '==' => Yii::t('yii', '{attribute} must be repeated exactly.'),
            '>' => Yii::t('yii', '{attribute} must be greater than "{compareValue}".'),
            '>=' => Yii::t('yii', '{attribute} must be greater than or equal to "{compareValue}".'),
        ];
        
        if (!in_array($this->operator, $this->_permittedOperations)) {
            throw new InvalidConfigException("Unknown operator: {$this->operator}");
        }
        
        if ($this->message === null) {
            $this->message = $this->_messages[$this->operator];
        }        
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->skipOnEmpty && $this->isEmpty($value)) {
            return null;
        }

        if ($this->compareValue === null) {
            $compareAttribute = $this->compareAttribute;
            $compareValue = $model->$compareAttribute;
            $compareTo = $model->getAttributeLabel($compareAttribute);
            $compareValueDT = DateTime::createFromFormat($this->format, $compareValue);
        } elseif ($this->compareValue instanceof DateTime) {
            $compareTo = $this->compareValue->format($this->format);
            $compareValue = $compareTo;
            $compareValueDT = $this->compareValue;
        } else {
            $compareTo = $this->compareValue;
            $compareValue = $this->compareValue;
            $compareValueDT = DateTime::createFromFormat($this->format, $this->compareValue);
        }

        if (!$compareValueDT instanceof DateTime) {
            $this->addError($model, null !== $this->compareAttribute ? $compareAttribute : $attribute, Yii::t('yii', 'Invalid compare value date format: {value}'), ['{value}' => $compareValue]);
            return null;
        }

        $valueDT = DateTime::createFromFormat($this->format, $value);

        if (!$valueDT instanceof DateTime) {
            $this->addError($model, $attribute, Yii::t('yii', 'Invalid value date format: {value}'), [
                '{value}' => $value
            ]);
            return null;
        }

        switch ($this->operator) {
            case '==':
                if ($valueDT != $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
            case '!=':
                if ($valueDT == $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
            case '>':
                if ($valueDT <= $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
            case '>=':
                if ($valueDT < $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
            case '<':
                if ($valueDT >= $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
            case '<=':
                if ($valueDT > $compareValueDT) {
                    $message = $this->_messages[$this->operator];
                }
                break;
        }

        if (!empty($message)) {
            $this->addError($model, $attribute, $message, [
                'compareAttribute' => $compareTo,
                'compareValue' => $compareValue
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        DateTimeRangesCompareValidatorAsset::register($view);

        $jsOptions['operator'] = $this->operator;
        $jsOptions['format'] = $this->jsFormat;

        if ($this->compareValue === null) {
            $compareAttribute = $this->compareAttribute;
            $compareValue = $model->getAttributeLabel($compareAttribute);
            $jsOptions['compareAttribute'] = Html::getInputId($model, $compareAttribute);
        } else {
             $compareValue = $this->compareValue;
            $jsOptions['compareValue'] = $compareValue;
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if ($this->skipOnEmpty) {
            $jsOptions['skipOnEmpty'] = 1;
        }

        if ($this->skipOnCompareAttributeEmpty) {
            $jsOptions['skipOnCompareAttributeEmpty'] = 1;
        }

        
        $jsOptions['message'] = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
            'compareAttribute' => $compareValue,
            'compareValue' => $compareValue,
            'compareValueOrAttribute' => $compareValueOrAttribute,
        ], Yii::$app->language);
        
        return 'yii.validation.datetimerangecompare(value, messages, ' . json_encode($jsOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');'; 
    }
}
