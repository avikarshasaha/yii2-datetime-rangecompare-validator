yii.validation.datetimerangecompare = function (value, messages, options) {
    if (options.skipOnEmpty && yii.validation.isEmpty(value)) {
        return null;
    }
    
    var dateCompareValue, valid = true;
    var dateValue = moment(value, options.format).unix();

    dateCompareValue = options.compareAttribute === undefined ? options.compareValue : $('#' + options.compareAttribute).val();
    dateCompareValue = moment(dateCompareValue, options.format).unix();

    if(options.skipOnCompareAttributeEmpty && yii.validation.isEmpty(dateCompareValue)){
        return;
    }

    switch (options.operator) {
        case '==':
            valid = dateValue == dateCompareValue;
            break;
        case '!=':
            valid = dateValue != dateCompareValue;
            break;
        case '>':
            valid = dateValue > dateCompareValue;
            break;
        case '>=':
            valid = dateValue >= dateCompareValue;
            break;
        case '<':
            valid = dateValue < dateCompareValue;
            break;
        case '<=':
            valid = dateValue <= dateCompareValue;
            break;
        default:
            valid = false;
            break;
    }

    if (!valid) {
        yii.validation.addMessage(messages, options.message, value);
    }
};